import { appendFile, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { JSDOM } from 'jsdom';
import XLSX from 'xlsx';

import {
	BASE_URL,
	DATA_DIR,
	IMAGES_DIR,
	LOGS_DIR,
	PAGES_DIR,
	PARSING_ROOT,
	readJson,
	slugFromPath,
	sleep,
} from './utils.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '../..');

const OLD_PAGES_JSON = path.join(DATA_DIR, 'old-pages.json');
const BRANDS_IMPORT_JSON = path.join(DATA_DIR, 'brands-import.json');
const SERVICES_IMPORT_JSON = path.join(DATA_DIR, 'services-import.json');
const OUTPUT_XLSX = path.join(DATA_DIR, 'portfolio-import.xlsx');
const OUTPUT_JSON = path.join(DATA_DIR, 'portfolio-import.json');
const OUTPUT_CSV = path.join(DATA_DIR, 'portfolio-import.csv');
const EXCLUDED_JSON = path.join(DATA_DIR, 'portfolio-excluded.json');
const EXCLUDED_CSV = path.join(DATA_DIR, 'portfolio-excluded.csv');
const LLM_CACHE_JSON = path.join(DATA_DIR, 'portfolio-llm-cache.json');
const LLM_LOG = path.join(LOGS_DIR, 'portfolio-llm.log');

const BRAND = 'КБ АВТО';
const CITY = 'СПб';
const CITY_LONG = 'Санкт-Петербург';
const TITLE_SUFFIX_RE = /\s*[—–-]\s*Автостудия КБ АВТО\s*$/i;
const WORK_DONE_FLAT_MAX = 8;
const LLM_CONCURRENCY = 8;
const LLM_TEXT_MAX = 12000;
const EXCEL_CELL_MAX = 32700;

const PORTFOLIO_HEADERS = [
	'title',
	'slug',
	'excerpt',
	'hero_title',
	'card_quote',
	'hero_image',
	'before_image',
	'after_image',
	'featured_image',
	'photos',
	'video',
	'price',
	'duration',
	'task_description',
	'case_description',
	'what_we_did',
	'work_done_json',
	...Array.from({ length: WORK_DONE_FLAT_MAX }, (_, i) => [
		`work_done_${i + 1}_title`,
		`work_done_${i + 1}_text`,
		`work_done_${i + 1}_photo`,
	]).flat(),
	'related_brands',
	'related_services',
	'old_url',
];

const SEO_HEADERS = [
	'Focus Keyword',
	'SEO Title',
	'Meta Description',
	'Meta Keywords',
	'Facebook Title',
	'Twitter Title',
];

const HEADERS = [...PORTFOLIO_HEADERS, ...SEO_HEADERS];

const LEGACY_TAG_MAP = {
	'ремонт фар': 'Ремонт фар',
	'замена стёкол фар': 'Замена стёкол фар',
	'замена стекол фар': 'Замена стёкол фар',
	'полировка фар': 'Полировка и шлифовка фар',
	'чистка фар': 'Химическая чистка фары изнутри',
	'покраска фар': 'Покраска масок фар',
	ретрофит: 'Замена линз в фарах',
	'замена линз': 'Замена линз в фарах',
	'установка светодиодных линз': 'Установка Bi-LED (светодиодных) линз',
	'bi-led': 'Установка Bi-LED (светодиодных) линз',
	'установка би-ксенона': 'Установка ксенона',
	'установка ксенона': 'Установка ксенона',
	дхо: 'Изготовление дневных ходовых огней (ДХО)',
	drl: 'Изготовление дневных ходовых огней (ДХО)',
	'drl nolden': 'Изготовление дневных ходовых огней (ДХО)',
	'запотевание фар': 'Устранение запотевания фар',
	'ремонт фонарей': 'Ремонт штатных светодиодных фонарей',
	'ремонт светодиодных фонарей': 'Ремонт штатных светодиодных фонарей',
	'установка светодиодов': 'Замена ламп на светодиодные — салон, габариты, подсветка номера',
	'тюнинг оптики': 'Комплексное улучшение света фар',
	'ремонт корпуса': 'Ремонт корпуса фар',
	'ремонт отражателей': 'Ремонт отражателей фар — чистка или замена рефлекторов',
};

const IGNORED_CATEGORY_SLUGS = new Set([
	'portfolio',
	'uncategorized',
	'без-рубрики',
	'bez-rubriki',
]);

const EXCLUDE_TITLE_RE = [
	/^отзывы$/i,
	/^бронирование фар$/i,
	/^восстановление отражателей$/i,
	/^полировка/i,
	/^покраска фар$/i,
	/^появились/i,
	/^регулировка фар$/i,
	/^инсталляция/i,
	/^комлпексный ремонт автоэлектрики/i,
	/^комплексный ремонт автоэлектрики/i,
	/^ремонт усилителей/i,
	/^ремонт электронных блоков/i,
	/^установка автомагнитол/i,
	/^установка видеоинтерфейсов/i,
	/^установка видеорегистраторов/i,
	/^блоки розжига/i,
	/^замена стекол и корпусов фар$/i,
	/^замена стёкол и корпусов фар$/i,
	/^ремонт задних фонарей$/i,
	/^ремонт запотевающих фар$/i,
];

function parseArgs(argv) {
	const args = { limit: 0, skipLlm: false, forceLlm: false };
	for (const arg of argv) {
		if (arg.startsWith('--limit=')) {
			args.limit = Number.parseInt(arg.slice('--limit='.length), 10) || 0;
		} else if (arg === '--skip-llm') {
			args.skipLlm = true;
		} else if (arg === '--force-llm') {
			args.forceLlm = true;
		}
	}
	return args;
}

async function loadEnvFile() {
	try {
		const raw = await readFile(path.join(ROOT, '.env'), 'utf8');
		for (const line of raw.split(/\r?\n/)) {
			const trimmed = line.trim();
			if (!trimmed || trimmed.startsWith('#')) continue;
			const eq = trimmed.indexOf('=');
			if (eq <= 0) continue;
			const key = trimmed.slice(0, eq).trim();
			let value = trimmed.slice(eq + 1).trim();
			if (
				(value.startsWith('"') && value.endsWith('"'))
				|| (value.startsWith("'") && value.endsWith("'"))
			) {
				value = value.slice(1, -1);
			}
			if (!(key in process.env)) {
				process.env[key] = value;
			}
		}
	} catch {
		/* optional */
	}
}

function escapeCsvCell(value) {
	const text = String(value ?? '');
	if (/[;"\n\r]/.test(text)) {
		return `"${text.replace(/"/g, '""')}"`;
	}
	return text;
}

function truncate(text, max) {
	const normalized = String(text ?? '').replace(/\s+/g, ' ').trim();
	if (normalized.length <= max) return normalized;
	return `${normalized.slice(0, max - 1).trim()}…`;
}

function truncateForExcel(value) {
	const text = String(value ?? '');
	if (text.length <= EXCEL_CELL_MAX) return text;
	return `${text.slice(0, EXCEL_CELL_MAX - 1)}…`;
}

function normalizeText(value) {
	return String(value ?? '')
		.toLowerCase()
		.replace(/ё/g, 'е')
		.replace(/\s+/g, ' ')
		.trim();
}

function cleanTitle(value) {
	return String(value ?? '')
		.replace(TITLE_SUFFIX_RE, '')
		.replace(/\s+/g, ' ')
		.trim();
}

function normalizeUploadUrl(url) {
	const text = String(url ?? '').trim();
	if (!text) return '';
	return text.replace(/-\d+x\d+(\.(png|jpe?g|webp|svg|gif))$/i, '$1');
}

function localImagePathToAbsolute(src) {
	const text = String(src ?? '').trim();
	if (!text) return '';

	if (/^https?:\/\//i.test(text)) {
		return normalizeUploadUrl(text);
	}

	const uploadsMatch =
		text.match(/(?:^|\/)images\/uploads\/(.+)$/i)
		|| text.match(/(?:^|\/)uploads\/(.+)$/i);
	if (uploadsMatch) {
		return normalizeUploadUrl(`${BASE_URL}/wp-content/uploads/${uploadsMatch[1]}`);
	}

	const externalMatch = text.match(/(?:^|\/)images\/external\/(.+)$/i);
	if (externalMatch) {
		const localPath = path.join(IMAGES_DIR, 'external', externalMatch[1]);
		return pathToFileURL(localPath).href;
	}

	return '';
}

function extractVideoUrl(document) {
	const iframes = [...document.querySelectorAll('iframe[src]')];
	for (const iframe of iframes) {
		const src = iframe.getAttribute('src') || '';
		const embed = src.match(/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/i);
		if (embed) {
			return `https://www.youtube.com/watch?v=${embed[1]}`;
		}
		const watch = src.match(/[?&]v=([a-zA-Z0-9_-]+)/i);
		if (watch) {
			return `https://www.youtube.com/watch?v=${watch[1]}`;
		}
	}

	for (const link of document.querySelectorAll('a[href*="youtu"]')) {
		const href = link.getAttribute('href') || '';
		const short = href.match(/youtu\.be\/([a-zA-Z0-9_-]+)/i);
		if (short) return `https://www.youtube.com/watch?v=${short[1]}`;
		const watch = href.match(/[?&]v=([a-zA-Z0-9_-]+)/i);
		if (watch) return `https://www.youtube.com/watch?v=${watch[1]}`;
	}

	return '';
}

function extractPrice(text) {
	const patterns = [
		/(?:стоимость|цена|работ[аы]?)\s*(?:составляет|от|:)?\s*(\d[\d\s]{1,})\s*₽/i,
		/от\s+(\d[\d\s]{1,})\s*₽\s*(?:\/|за)?\s*(?:фару|работу)?/i,
	];
	for (const re of patterns) {
		const match = String(text ?? '').match(re);
		if (match) {
			const amount = match[1].replace(/\s/g, ' ').trim();
			if (/^(19|20)\d{2}$/.test(amount.replace(/\s/g, ''))) continue;
			return `${amount} ₽`;
		}
	}
	return '';
}

function firstParagraphText(document) {
	const paragraph = document.querySelector('.entry-content p');
	if (!paragraph) return '';
	return paragraph.textContent.replace(/\s+/g, ' ').trim();
}

function cleanEntryContent(document) {
	const content = document.querySelector('.entry-content');
	if (!content) return null;

	const clone = content.cloneNode(true);
	clone
		.querySelectorAll(
			[
				'.blog-shortcode',
				'.woocommerce',
				'.products',
				'.entry-footer',
				'.post-navigation',
				'.related-posts-box',
				'.sharedaddy',
				'.jp-relatedposts',
			].join(', '),
		)
		.forEach((node) => node.remove());

	for (const img of clone.querySelectorAll('img[src]')) {
		const absolute = localImagePathToAbsolute(img.getAttribute('src'));
		if (absolute) {
			img.setAttribute('src', absolute);
		}
	}

	return clone;
}

function extractPhotos(document, metaImages = [], cleanedContent) {
	const urls = new Set();

	for (const relPath of metaImages) {
		const absolute = localImagePathToAbsolute(relPath);
		if (absolute) urls.add(absolute);
	}

	const cover = document.querySelector('.entry-cover img[src]');
	const coverUrl = localImagePathToAbsolute(cover?.getAttribute('src'));
	if (coverUrl) urls.add(coverUrl);

	const scope = cleanedContent || document.querySelector('.entry-content');
	if (scope) {
		for (const img of scope.querySelectorAll('img[src]')) {
			const absolute = localImagePathToAbsolute(img.getAttribute('src'));
			if (absolute) urls.add(absolute);
		}
	}

	return [...urls];
}

function extractBrandSlugsFromArticle(article) {
	if (!article) return [];
	const className = article.getAttribute('class') ?? '';
	const slugs = [];
	for (const token of className.split(/\s+/)) {
		const match = token.match(/^category-([a-z0-9-]+)$/i);
		if (!match) continue;
		const slug = match[1].toLowerCase();
		if (/^\d+$/.test(slug) || IGNORED_CATEGORY_SLUGS.has(slug)) continue;
		slugs.push(slug);
	}
	return [...new Set(slugs)];
}

function extractTags(document) {
	return [...document.querySelectorAll('.entry-tags a[rel="tag"]')]
		.map((link) => link.textContent.replace(/\s+/g, ' ').trim())
		.filter(Boolean);
}

function buildBrandIndex(brands) {
	const bySlug = new Map();
	const byTitle = new Map();
	for (const brand of brands) {
		bySlug.set(brand.slug.toLowerCase(), brand);
		byTitle.set(normalizeText(brand.title), brand);
	}
	return { bySlug, byTitle, list: brands };
}

function buildServiceIndex(services) {
	const byTitle = new Map();
	for (const service of services) {
		byTitle.set(service.title, service);
	}
	return { byTitle, list: services };
}

function matchBrandFromTitle(title, brandIndex) {
	const normalizedTitle = normalizeText(title);
	let best = null;
	for (const brand of brandIndex.list) {
		const normalizedBrand = normalizeText(brand.title);
		if (!normalizedBrand) continue;
		if (normalizedTitle.includes(normalizedBrand)) {
			if (!best || normalizedBrand.length > normalizeText(best.title).length) {
				best = brand;
			}
		}
	}
	return best;
}

function resolveBrands({ article, title, brandIndex }) {
	const brands = [];
	for (const slug of extractBrandSlugsFromArticle(article)) {
		const brand = brandIndex.bySlug.get(slug);
		if (brand) brands.push(brand);
	}
	if (!brands.length) {
		const fallback = matchBrandFromTitle(title, brandIndex);
		if (fallback) brands.push(fallback);
	}
	const unique = new Map();
	for (const brand of brands) unique.set(brand.title, brand);
	return [...unique.values()];
}

function mapTagToService(tag, serviceIndex) {
	const normalizedTag = normalizeText(tag);
	const mappedTitle = LEGACY_TAG_MAP[normalizedTag];
	if (mappedTitle && serviceIndex.byTitle.has(mappedTitle)) {
		return serviceIndex.byTitle.get(mappedTitle);
	}
	for (const service of serviceIndex.list) {
		const normalizedService = normalizeText(service.title);
		if (
			normalizedTag.includes(normalizedService)
			|| normalizedService.includes(normalizedTag)
		) {
			return service;
		}
	}
	return null;
}

function matchServicesFromTitle(title, serviceIndex) {
	const normalizedTitle = normalizeText(title);
	const matches = [];
	for (const service of serviceIndex.list) {
		const normalizedService = normalizeText(service.title);
		const keyword = normalizedService.split(/[—(]/)[0].trim();
		if (keyword.length >= 8 && normalizedTitle.includes(keyword)) {
			matches.push(service);
		}
	}
	return matches;
}

function resolveServices({ tags, title, serviceIndex }) {
	const services = new Map();
	for (const tag of tags) {
		const service = mapTagToService(tag, serviceIndex);
		if (service) services.set(service.title, service);
	}
	if (!services.size) {
		for (const service of matchServicesFromTitle(title, serviceIndex)) {
			services.set(service.title, service);
		}
	}
	return [...services.values()];
}

const CAR_BRAND_RE =
	/\b(bmw|mercedes(?:-benz)?|merсedes|audi|toyota|lexus|nissan|infiniti|honda|mazda|ford|volkswagen|vw|porsche|jaguar|subaru|kia|hyundai|chevrolet|opel|peugeot|volvo|skoda|mini|jeep|dodge|cadillac|bentley|ferrari|maserati|maybach|hummer|chrysler|acura|mitsubishi|renault|citroen|seat|fiat|tesla|genesis|haval|geely|chery|uaz|lada|газ|range\s*rover|land\s*rover|ktm|porsche)\b/i;

function looksLikeCaseTitle(title) {
	const t = cleanTitle(title);
	if (CAR_BRAND_RE.test(t)) return true;
	// «Марка Модель — работа» only when left side looks like a car designation
	if (/[—–-]/.test(t) && /\b([A-ZА-Я]{1,3}\d{1,3}|[ewfg]\d{2}|w\d{3}|c\d)\b/i.test(t)) {
		return true;
	}
	return false;
}

function classifyCase({ title, brands, cleanedHtml, plainText }) {
	const clean = cleanTitle(title);

	for (const re of EXCLUDE_TITLE_RE) {
		if (re.test(clean)) {
			return { include: false, reason: `excluded_title:${clean}` };
		}
	}

	const textLen = (plainText || '').replace(/\s+/g, ' ').trim().length;
	if (textLen < 80 && !(cleanedHtml || '').includes('<img')) {
		return { include: false, reason: 'empty_or_stub' };
	}

	const hasOwnStory =
		textLen >= 120
		|| /<(p|li|h[1-6])\b/i.test(cleanedHtml || '');
	const onlyRelated =
		!hasOwnStory
		&& /blog-shortcode|РАБОТЫ ПО/i.test(cleanedHtml || '');
	if (onlyRelated) {
		return { include: false, reason: 'related_posts_only' };
	}

	if (brands.length || looksLikeCaseTitle(clean)) {
		return { include: true, reason: '' };
	}

	if (/появились|новинк|каталог стекол|в наличии/i.test(plainText || '')) {
		return { include: false, reason: 'news_or_catalog' };
	}

	return { include: false, reason: 'ambiguous_not_a_case' };
}

function buildMetaKeywords(focusKeyword) {
	const keyword = focusKeyword.trim().toLowerCase();
	return [...new Set([
		keyword,
		`${keyword} ${CITY.toLowerCase()}`,
		`ремонт фар ${CITY}`,
		`тюнинг оптики ${CITY}`,
		BRAND,
	].filter(Boolean))].join(', ');
}

function buildSeoFields(item) {
	const title = String(item.title ?? '').trim();
	const excerpt = String(item.excerpt ?? '').trim();
	const brandPart = String(item.related_brands ?? '').split('|')[0].trim();
	const focusKeyword = truncate([title, brandPart].filter(Boolean).join(' ').toLowerCase(), 80);
	const seoTitle = truncate(`${title} в ${CITY} | ${BRAND}`, 60);
	const metaDescription = truncate(`${excerpt || title}. ${BRAND}, ${CITY_LONG}.`, 160);
	return {
		'Focus Keyword': focusKeyword,
		'SEO Title': seoTitle,
		'Meta Description': metaDescription,
		'Meta Keywords': buildMetaKeywords(focusKeyword),
		'Facebook Title': seoTitle,
		'Twitter Title': seoTitle,
	};
}

function workDoneToWhatWeDid(workDone) {
	return (workDone || [])
		.map((step) => String(step.title || step.text || '').trim())
		.filter(Boolean)
		.join('|');
}

function portfolioToRow(item, { forExcel = false } = {}) {
	const seo = buildSeoFields(item);
	const caseDescription = forExcel
		? truncateForExcel(item.case_description)
		: item.case_description ?? '';
	const taskDescription = forExcel
		? truncateForExcel(item.task_description)
		: item.task_description ?? '';
	const workDone = Array.isArray(item.work_done) ? item.work_done : [];
	const workDoneJson = forExcel
		? truncateForExcel(JSON.stringify(workDone))
		: JSON.stringify(workDone);

	const flat = [];
	for (let i = 0; i < WORK_DONE_FLAT_MAX; i += 1) {
		const step = workDone[i] || {};
		flat.push(step.title ?? '', step.text ?? '', step.photo ?? '');
	}

	return [
		item.title ?? '',
		item.slug ?? '',
		item.excerpt ?? '',
		item.hero_title ?? '',
		item.card_quote ?? '',
		item.hero_image ?? '',
		item.before_image ?? '',
		item.after_image ?? '',
		item.featured_image ?? '',
		item.photos ?? '',
		item.video ?? '',
		item.price ?? '',
		item.duration ?? '',
		taskDescription,
		caseDescription,
		item.what_we_did ?? '',
		workDoneJson,
		...flat,
		item.related_brands ?? '',
		item.related_services ?? '',
		item.old_url ?? '',
		seo['Focus Keyword'],
		seo['SEO Title'],
		seo['Meta Description'],
		seo['Meta Keywords'],
		seo['Facebook Title'],
		seo['Twitter Title'],
	];
}

async function loadPostPage(pageEntry) {
	const slug = pageEntry.local_dir?.split('/').pop()
		|| pageEntry.local_dir?.split('\\').pop();
	if (!slug) {
		throw new Error(`Missing local_dir for post ${pageEntry.id}`);
	}

	const pageDir = path.join(PAGES_DIR, 'post', slug);
	const [metaRaw, contentHtml] = await Promise.all([
		readFile(path.join(pageDir, 'meta.json'), 'utf8'),
		readFile(path.join(pageDir, 'content.html'), 'utf8'),
	]);

	return { meta: JSON.parse(metaRaw), contentHtml, pageDir };
}

function parsePortfolioCase({ pageEntry, meta, contentHtml, brandIndex, serviceIndex }) {
	const dom = new JSDOM(contentHtml);
	const { document } = dom.window;
	const article = document.querySelector('article');

	const metaTitle = cleanTitle(meta.title ?? pageEntry.title ?? '');
	const heroTitle = cleanTitle(
		document.querySelector('.entry-title-primary')?.textContent ?? metaTitle,
	);
	const cardQuote =
		document.querySelector('.entry-subtitle')?.textContent?.replace(/\s+/g, ' ').trim() ?? '';
	const title = heroTitle || metaTitle;
	const slug = slugFromPath(meta.path ?? pageEntry.path);

	const cleaned = cleanEntryContent(document);
	const cleanedHtml = cleaned?.innerHTML?.trim() ?? '';
	const plainText = cleaned?.textContent?.replace(/\s+/g, ' ').trim() ?? '';

	const photos = extractPhotos(document, meta.images ?? [], cleaned);
	const heroImage =
		localImagePathToAbsolute(
			document.querySelector('.entry-cover img[src]')?.getAttribute('src'),
		) || photos[0] || '';
	const video = extractVideoUrl(document);
	const price = extractPrice(plainText);
	const excerpt = cardQuote || truncate(firstParagraphText(document), 300);

	const brands = resolveBrands({ article, title, brandIndex });
	const tags = extractTags(document);
	const services = resolveServices({ tags, title, serviceIndex });

	const classification = classifyCase({
		title,
		brands,
		cleanedHtml,
		plainText,
	});

	return {
		include: classification.include,
		exclude_reason: classification.reason,
		title,
		slug,
		excerpt,
		hero_title: title,
		card_quote: cardQuote,
		hero_image: heroImage,
		before_image: '',
		after_image: '',
		featured_image: heroImage,
		photos: photos.join('|'),
		photos_list: photos,
		video,
		price,
		duration: '',
		task_description: '',
		case_description: cleanedHtml,
		what_we_did: '',
		work_done: [],
		related_brands: brands.map((b) => b.title).join('|'),
		related_services: services.map((s) => s.title).join('|'),
		old_url: meta.url ?? pageEntry.url ?? '',
		_plain_text: plainText,
		_service_titles: services.map((s) => s.title),
		_brand_titles: brands.map((b) => b.title),
	};
}

function extractJsonFromText(text) {
	const raw = String(text ?? '').trim();
	if (!raw) throw new Error('Empty LLM response');

	const fenced = raw.match(/```(?:json)?\s*([\s\S]*?)```/i);
	const candidate = fenced ? fenced[1].trim() : raw;
	const start = candidate.indexOf('{');
	const end = candidate.lastIndexOf('}');
	if (start === -1 || end === -1 || end <= start) {
		throw new Error('No JSON object in LLM response');
	}
	return JSON.parse(candidate.slice(start, end + 1));
}

function sanitizeLlmResult(result, base) {
	const photoSet = new Set(base.photos_list || []);
	const pickPhoto = (url) => {
		const value = String(url ?? '').trim();
		if (!value) return '';
		if (photoSet.has(value)) return value;
		const match = [...photoSet].find(
			(p) => p.endsWith(value) || value.endsWith(path.basename(p)),
		);
		return match || '';
	};

	const workDone = Array.isArray(result.work_done)
		? result.work_done
			.map((step) => ({
				title: truncate(String(step?.title ?? '').trim(), 120),
				text: truncate(String(step?.text ?? '').trim(), 400),
				photo: pickPhoto(step?.photo),
			}))
			.filter((step) => step.title || step.text)
			.slice(0, 12)
		: [];

	const before = result.before_confident === false ? '' : pickPhoto(result.before_image);
	const after = result.after_confident === false ? '' : pickPhoto(result.after_image);

	const brands = Array.isArray(result.related_brands)
		? result.related_brands.map((v) => String(v).trim()).filter(Boolean)
		: String(base.related_brands || '').split('|').filter(Boolean);
	const services = Array.isArray(result.related_services)
		? result.related_services.map((v) => String(v).trim()).filter(Boolean)
		: String(base.related_services || '').split('|').filter(Boolean);

	return {
		excerpt: truncate(String(result.excerpt ?? base.excerpt ?? '').trim(), 300) || base.excerpt,
		price: String(result.price ?? base.price ?? '').trim() || base.price,
		task_description: String(result.task_description ?? '').trim(),
		case_description: String(result.case_description ?? base.case_description ?? '').trim()
			|| base.case_description,
		work_done: workDone,
		what_we_did: workDoneToWhatWeDid(workDone),
		before_image: before,
		after_image: after,
		featured_image: after || before || base.featured_image || base.hero_image,
		video: String(result.video ?? base.video ?? '').trim() || base.video,
		related_brands: brands.join('|') || base.related_brands,
		related_services: services.join('|') || base.related_services,
	};
}

function buildLlmPrompt(item, brandTitles, serviceTitles) {
	return `Ты структурируешь кейс автосервиса по ремонту/тюнингу фар для импорта в WordPress.

Верни ТОЛЬКО JSON-объект (без markdown, без пояснений) со схемой:
{
  "excerpt": "краткое описание 1-2 предложения",
  "price": "цена работ вида \\"42 000 ₽\\" или пустая строка",
  "task_description": "что требовалось сделать (1-3 предложения; сгенерируй по смыслу если неявно)",
  "work_done": [{"title":"краткий заголовок шага","text":"1-2 предложения","photo":"URL из списка или \\"\\""}],
  "before_image": "URL из списка или \\"\\"",
  "after_image": "URL из списка или \\"\\"",
  "before_confident": true/false,
  "after_confident": true/false,
  "case_description": "остаток полезного описания HTML/текст без шагов и без контактов",
  "related_brands": ["марки из списка кандидатов"],
  "related_services": ["услуги из списка кандидатов"],
  "video": "youtube url или \\"\\""
}

Правила:
- before/after заполняй только если уверен (иначе пусто + confident=false).
- photo в work_done — только URL из переданного списка photos.
- Не выдумывай марки/услуги вне кандидатов.
- Не включай чужие кейсы, магазины, телефоны, youtube-канал как контент.
- work_done: 2–8 шагов по фактическим работам.

Заголовок: ${item.title}
Кандидаты марок: ${JSON.stringify(brandTitles)}
Кандидаты услуг: ${JSON.stringify(serviceTitles)}
Видео (если найдено): ${item.video || ''}
Фото: ${JSON.stringify(item.photos_list || [])}
Текст кейса:
${truncate(item._plain_text || '', LLM_TEXT_MAX)}
`;
}

async function callLlm(item, apiKey, retries = 3) {
	const { Agent } = await import('@cursor/sdk');
	const prompt = buildLlmPrompt(
		item,
		item._brand_titles?.length ? item._brand_titles : (item.related_brands || '').split('|').filter(Boolean),
		item._service_titles?.length
			? item._service_titles
			: (item.related_services || '').split('|').filter(Boolean),
	);

	let lastError;
	for (let attempt = 1; attempt <= retries; attempt += 1) {
		try {
			const result = await Agent.prompt(prompt, {
				apiKey,
				model: { id: 'gemini-3-flash' },
				local: { cwd: PARSING_ROOT },
			});

			const text = typeof result?.result === 'string'
				? result.result
				: JSON.stringify(result?.result ?? result ?? '');

			await appendFile(
				LLM_LOG,
				`[${new Date().toISOString()}] ${item.slug} status=${result?.status ?? 'unknown'} len=${text.length}\n`,
				'utf8',
			);

			const parsed = extractJsonFromText(text);
			return sanitizeLlmResult(parsed, item);
		} catch (error) {
			lastError = error;
			const msg = error?.message || String(error);
			await appendFile(
				LLM_LOG,
				`[${new Date().toISOString()}] RETRY ${item.slug} attempt=${attempt}: ${msg}\n`,
				'utf8',
			);
			if (attempt < retries) {
				await sleep(1000 * attempt);
			}
		}
	}
	throw lastError;
}

async function mapPool(items, concurrency, mapper) {
	const results = new Array(items.length);
	let next = 0;

	async function worker() {
		while (next < items.length) {
			const index = next;
			next += 1;
			results[index] = await mapper(items[index], index);
		}
	}

	const workers = Array.from({ length: Math.min(concurrency, items.length) }, () => worker());
	await Promise.all(workers);
	return results;
}

async function enrichWithLlm(cases, { forceLlm = false } = {}) {
	await loadEnvFile();
	const apiKey = process.env.CURSOR_API_KEY;
	if (!apiKey) {
		throw new Error('CURSOR_API_KEY is missing. Set it in .env or environment.');
	}

	const cache = (await readJson(LLM_CACHE_JSON, { cases: {} })) || { cases: {} };
	if (!cache.cases) cache.cases = {};

	await writeFile(LLM_LOG, '', 'utf8').catch(() => {});

	let fromCache = 0;
	let fresh = 0;
	let failed = 0;
	let cacheWriteChain = Promise.resolve();

	const persistCache = () => {
		cacheWriteChain = cacheWriteChain.then(() =>
			writeFile(LLM_CACHE_JSON, `${JSON.stringify(cache, null, 2)}\n`, 'utf8'),
		);
		return cacheWriteChain;
	};

	const enriched = await mapPool(cases, LLM_CONCURRENCY, async (item) => {
		if (!forceLlm && cache.cases[item.slug]) {
			fromCache += 1;
			return {
				...item,
				...cache.cases[item.slug],
				photos: item.photos,
				photos_list: item.photos_list,
				hero_image: item.hero_image,
				hero_title: item.hero_title,
				card_quote: item.card_quote,
				old_url: item.old_url,
				slug: item.slug,
				title: item.title,
			};
		}

		try {
			const llm = await callLlm(item, apiKey);
			cache.cases[item.slug] = llm;
			fresh += 1;
			await persistCache();
			if (fresh % 10 === 0) {
				console.log(`LLM progress: fresh=${fresh}, cache_total=${Object.keys(cache.cases).length}`);
			}
			return { ...item, ...llm };
		} catch (error) {
			failed += 1;
			await appendFile(
				LLM_LOG,
				`[${new Date().toISOString()}] FAIL ${item.slug}: ${error.message}\n`,
				'utf8',
			);
			return item;
		} finally {
			await sleep(150);
		}
	});

	await persistCache();
	console.log(`LLM: cache=${fromCache}, fresh=${fresh}, failed=${failed}`);
	return enriched;
}

function validateCases(cases) {
	if (!cases.length) {
		throw new Error('No portfolio cases to export');
	}
	const slugs = new Set();
	for (const item of cases) {
		if (!item.title) throw new Error(`Case "${item.slug}" is missing title`);
		if (!item.slug) throw new Error(`Case "${item.title}" is missing slug`);
		if (slugs.has(item.slug)) throw new Error(`Duplicate slug "${item.slug}"`);
		slugs.add(item.slug);
	}
}

function stripInternal(item) {
	const {
		_plain_text,
		_service_titles,
		_brand_titles,
		photos_list,
		include,
		exclude_reason,
		...rest
	} = item;
	return rest;
}

async function writeExcluded(excluded) {
	const rows = [
		['title', 'slug', 'old_url', 'reason'],
		...excluded.map((item) => [
			item.title ?? '',
			item.slug ?? '',
			item.old_url ?? '',
			item.exclude_reason ?? '',
		]),
	];

	await writeFile(
		EXCLUDED_JSON,
		`${JSON.stringify({
			generated_for: 'Portfolio import — excluded posts',
			count: excluded.length,
			items: excluded.map((item) => ({
				title: item.title,
				slug: item.slug,
				old_url: item.old_url,
				reason: item.exclude_reason,
			})),
		}, null, 2)}\n`,
		'utf8',
	);

	const csv = `\uFEFF${rows.map((row) => row.map(escapeCsvCell).join(';')).join('\r\n')}`;
	await writeFile(EXCLUDED_CSV, csv, 'utf8');
}

async function writeOutputs(cases) {
	const publicCases = cases.map(stripInternal);
	const excelRows = [HEADERS, ...publicCases.map((item) => portfolioToRow(item, { forExcel: true }))];

	const workbook = XLSX.utils.book_new();
	XLSX.utils.book_append_sheet(workbook, XLSX.utils.aoa_to_sheet(excelRows), 'portfolio');
	XLSX.writeFile(workbook, OUTPUT_XLSX);

	const truncatedCount = publicCases.filter(
		(item) => String(item.case_description ?? '').length > EXCEL_CELL_MAX,
	).length;

	const importJson = {
		generated_for: 'WP All Import — CPT portfolio',
		source: path.basename(OUTPUT_XLSX),
		note:
			'title → post_title, slug → post_name. photos — gallery URLs через |. work_done_json / work_done_N_* → ACF work_process. duration → ACF duration. related_brands / related_services — post title через |. Уникальный идентификатор — slug. Полный nested work_done — в JSON.',
		columns: HEADERS,
		truncated_case_descriptions: truncatedCount,
		cases: publicCases,
	};

	await writeFile(OUTPUT_JSON, `${JSON.stringify(importJson, null, 2)}\n`, 'utf8');

	const csvLines = excelRows.map((row) => row.map(escapeCsvCell).join(';'));
	await writeFile(OUTPUT_CSV, `\uFEFF${csvLines.join('\r\n')}`, 'utf8');
}

async function main() {
	const args = parseArgs(process.argv.slice(2));

	const [oldPages, brandsData, servicesData] = await Promise.all([
		readJson(OLD_PAGES_JSON),
		readJson(BRANDS_IMPORT_JSON),
		readJson(SERVICES_IMPORT_JSON),
	]);

	if (!oldPages?.pages?.length) {
		throw new Error(`Missing or empty ${OLD_PAGES_JSON}`);
	}
	if (!brandsData?.brands?.length) {
		throw new Error('Missing brands — run: npm run import:brands');
	}
	if (!servicesData?.services?.length) {
		throw new Error('Missing services — run: npm run import:services');
	}

	const brandIndex = buildBrandIndex(brandsData.brands);
	const serviceIndex = buildServiceIndex(servicesData.services);

	const postEntries = oldPages.pages.filter(
		(page) => page.type === 'post' && !page.is_demo && page.scraped && page.local_dir,
	);

	const included = [];
	const excluded = [];
	const errors = [];

	for (const pageEntry of postEntries) {
		try {
			const { meta, contentHtml } = await loadPostPage(pageEntry);
			const parsed = parsePortfolioCase({
				pageEntry,
				meta,
				contentHtml,
				brandIndex,
				serviceIndex,
			});
			if (parsed.include) {
				included.push(parsed);
			} else {
				excluded.push(parsed);
			}
		} catch (error) {
			errors.push({ id: pageEntry.id, error: error.message });
		}
	}

	if (errors.length) {
		console.warn(`Failed to parse ${errors.length} posts:`);
		for (const item of errors.slice(0, 5)) {
			console.warn(`  ${item.id}: ${item.error}`);
		}
	}

	await writeExcluded(excluded);

	let cases = included;
	if (args.limit > 0) {
		cases = included.slice(0, args.limit);
		console.log(`--limit=${args.limit}: enriching ${cases.length} of ${included.length}`);
	}

	if (!args.skipLlm) {
		cases = await enrichWithLlm(cases, { forceLlm: args.forceLlm });
	} else {
		console.log('Skipping LLM (--skip-llm)');
	}

	// If limited, still export only enriched subset for smoke; full set needs full run
	const exportCases = args.limit > 0
		? [
			...cases,
			...included.slice(args.limit).map((item) => ({
				...item,
				// keep deterministic fields only for non-enriched remainder in limited mode?
				// For limited smoke we export ONLY the limited cases.
			})),
		].slice(0, args.limit)
		: cases;

	validateCases(exportCases);
	await writeOutputs(exportCases);

	const withBrands = exportCases.filter((item) => item.related_brands).length;
	const withServices = exportCases.filter((item) => item.related_services).length;
	const withImages = exportCases.filter((item) => item.hero_image).length;
	const withVideo = exportCases.filter((item) => item.video).length;
	const withPrice = exportCases.filter((item) => item.price).length;
	const withWork = exportCases.filter((item) => (item.work_done || []).length).length;
	const withBefore = exportCases.filter((item) => item.before_image).length;
	const withAfter = exportCases.filter((item) => item.after_image).length;

	console.log(`Generated ${OUTPUT_XLSX}`);
	console.log(`Generated ${OUTPUT_JSON}`);
	console.log(`Generated ${OUTPUT_CSV}`);
	console.log(`Excluded list: ${EXCLUDED_JSON} (${excluded.length})`);
	console.log(`Cases exported: ${exportCases.length} / included ${included.length}`);
	console.log(`With brands: ${withBrands}/${exportCases.length}`);
	console.log(`With services: ${withServices}/${exportCases.length}`);
	console.log(`With hero_image: ${withImages}/${exportCases.length}`);
	console.log(`With video: ${withVideo}/${exportCases.length}`);
	console.log(`With price: ${withPrice}/${exportCases.length}`);
	console.log(`With work_done: ${withWork}/${exportCases.length}`);
	console.log(`With before/after: ${withBefore}/${withAfter}`);
}

const isDirectRun = process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url);

if (isDirectRun) {
	main().catch((error) => {
		console.error(error);
		process.exit(1);
	});
}
