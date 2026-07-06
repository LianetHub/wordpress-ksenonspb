import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { JSDOM } from 'jsdom';
import XLSX from 'xlsx';

import {
	BASE_URL,
	DATA_DIR,
	PAGES_DIR,
	readJson,
	slugFromPath,
} from './utils.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const OLD_PAGES_JSON = path.join(DATA_DIR, 'old-pages.json');
const BRANDS_IMPORT_JSON = path.join(DATA_DIR, 'brands-import.json');
const SERVICES_IMPORT_JSON = path.join(DATA_DIR, 'services-import.json');
const OUTPUT_XLSX = path.join(DATA_DIR, 'portfolio-import.xlsx');
const OUTPUT_JSON = path.join(DATA_DIR, 'portfolio-import.json');
const OUTPUT_CSV = path.join(DATA_DIR, 'portfolio-import.csv');

const BRAND = 'КБ АВТО';
const CITY = 'СПб';
const CITY_LONG = 'Санкт-Петербург';

const TITLE_SUFFIX_RE = /\s*[—–-]\s*Автостудия КБ АВТО\s*$/i;

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
	'price',
	'case_description',
	'what_we_did',
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

/** Маппинг тегов старого блога → канонические услуги (services-import.json) */
const LEGACY_TAG_MAP = {
	'ремонт фар': 'Ремонт фар',
	'замена стёкол фар': 'Замена стёкол фар',
	'замена стекол фар': 'Замена стёкол фар',
	'полировка фар': 'Полировка и шлифовка фар',
	'чистка фар': 'Химическая чистка фары изнутри',
	'покраска фар': 'Покраска масок фар',
	'ретрофит': 'Замена линз в фарах',
	'замена линз': 'Замена линз в фарах',
	'установка светодиодных линз': 'Установка Bi-LED (светодиодных) линз',
	'bi-led': 'Установка Bi-LED (светодиодных) линз',
	'установка би-ксенона': 'Установка ксенона',
	'установка ксенона': 'Установка ксенона',
	'дхо': 'Изготовление дневных ходовых огней (ДХО)',
	'drl': 'Изготовление дневных ходовых огней (ДХО)',
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

function escapeCsvCell(value) {
	const text = String(value ?? '');
	if (/[;"\n\r]/.test(text)) {
		return `"${text.replace(/"/g, '""')}"`;
	}
	return text;
}

function truncate(text, max) {
	const normalized = String(text ?? '').replace(/\s+/g, ' ').trim();
	if (normalized.length <= max) {
		return normalized;
	}
	return `${normalized.slice(0, max - 1).trim()}…`;
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

/** Убирает суффикс WordPress -300x162, оставляет полный файл из uploads */
function normalizeUploadUrl(url) {
	const text = String(url ?? '').trim();
	if (!text) {
		return '';
	}
	return text.replace(/-\d+x\d+(\.(png|jpe?g|webp|svg|gif))$/i, '$1');
}

function localImagePathToAbsolute(src) {
	const text = String(src ?? '').trim();
	if (!text) {
		return '';
	}
	if (/^https?:\/\//i.test(text)) {
		return normalizeUploadUrl(text);
	}

	const uploadsMatch = text.match(/(?:^|\/)images\/uploads\/(.+)$/i)
		|| text.match(/(?:^|\/)uploads\/(.+)$/i);
	if (uploadsMatch) {
		return normalizeUploadUrl(`${BASE_URL}/wp-content/uploads/${uploadsMatch[1]}`);
	}

	return '';
}

function extractUploadImages(document, metaImages = []) {
	const urls = new Set();

	for (const relPath of metaImages) {
		const absolute = localImagePathToAbsolute(relPath);
		if (absolute) {
			urls.add(absolute);
		}
	}

	for (const img of document.querySelectorAll('img[src]')) {
		const absolute = localImagePathToAbsolute(img.getAttribute('src'));
		if (absolute) {
			urls.add(absolute);
		}
	}

	return [...urls];
}

function pickHeroImage(document, uploadImages) {
	const cover = document.querySelector('.entry-cover img[src]');
	const coverUrl = localImagePathToAbsolute(cover?.getAttribute('src'));
	if (coverUrl) {
		return coverUrl;
	}
	return uploadImages[0] ?? '';
}

function pickContentImages(document) {
	const coverSrc = document.querySelector('.entry-cover img[src]')?.getAttribute('src') ?? '';
	const content = document.querySelector('.entry-content');
	if (!content) {
		return [];
	}

	const images = [];
	for (const img of content.querySelectorAll('img[src]')) {
		const src = img.getAttribute('src');
		if (!src || src === coverSrc) {
			continue;
		}
		const absolute = localImagePathToAbsolute(src);
		if (absolute) {
			images.push(absolute);
		}
	}
	return images;
}

function extractBrandSlugsFromArticle(article) {
	if (!article) {
		return [];
	}

	const className = article.getAttribute('class') ?? '';
	const slugs = [];
	for (const token of className.split(/\s+/)) {
		const match = token.match(/^category-([a-z0-9-]+)$/i);
		if (!match) {
			continue;
		}
		const slug = match[1].toLowerCase();
		if (/^\d+$/.test(slug) || IGNORED_CATEGORY_SLUGS.has(slug)) {
			continue;
		}
		slugs.push(slug);
	}
	return [...new Set(slugs)];
}

function extractTags(document) {
	return [...document.querySelectorAll('.entry-tags a[rel="tag"]')]
		.map((link) => link.textContent.replace(/\s+/g, ' ').trim())
		.filter(Boolean);
}

function extractCaseDescriptionHtml(document) {
	const content = document.querySelector('.entry-content');
	if (!content) {
		return '';
	}

	const clone = content.cloneNode(true);
	clone.querySelectorAll('.entry-footer, .post-navigation, .related-posts-box').forEach((node) => {
		node.remove();
	});

	let html = clone.innerHTML.trim();
	html = html.replace(/src="(?:\.\.\/)+images\/uploads\/([^"]+)"/gi, `src="${BASE_URL}/wp-content/uploads/$1"`);
	return html;
}

function extractPrice(text) {
	const match = String(text ?? '').match(/(\d[\d\s]{2,})\s*₽/);
	if (!match) {
		return '';
	}
	return `${match[1].replace(/\s/g, ' ').trim()} ₽`;
}

function firstParagraphText(document) {
	const paragraph = document.querySelector('.entry-content p');
	if (!paragraph) {
		return '';
	}
	return paragraph.textContent.replace(/\s+/g, ' ').trim();
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
	const byNormalized = new Map();

	for (const service of services) {
		byTitle.set(service.title, service);
		byNormalized.set(normalizeText(service.title), service);
	}

	return { byTitle, byNormalized, list: services };
}

function matchBrandFromTitle(title, brandIndex) {
	const normalizedTitle = normalizeText(title);
	let best = null;

	for (const brand of brandIndex.list) {
		const normalizedBrand = normalizeText(brand.title);
		if (!normalizedBrand) {
			continue;
		}
		if (normalizedTitle.includes(normalizedBrand)) {
			if (!best || normalizedBrand.length > normalizeText(best.title).length) {
				best = brand;
			}
		}
	}

	return best;
}

function resolveBrands({ article, title, brandIndex }) {
	const slugs = extractBrandSlugsFromArticle(article);
	const brands = [];

	for (const slug of slugs) {
		const brand = brandIndex.bySlug.get(slug);
		if (brand) {
			brands.push(brand);
		}
	}

	if (!brands.length) {
		const fallback = matchBrandFromTitle(title, brandIndex);
		if (fallback) {
			brands.push(fallback);
		}
	}

	const unique = new Map();
	for (const brand of brands) {
		unique.set(brand.title, brand);
	}
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
		if (service) {
			services.set(service.title, service);
		}
	}

	if (!services.size) {
		for (const service of matchServicesFromTitle(title, serviceIndex)) {
			services.set(service.title, service);
		}
	}

	return [...services.values()];
}

function buildMetaKeywords(focusKeyword) {
	const keyword = focusKeyword.trim().toLowerCase();
	const parts = [
		keyword,
		`${keyword} ${CITY.toLowerCase()}`,
		`ремонт фар ${CITY}`,
		`тюнинг оптики ${CITY}`,
		BRAND,
	];
	return [...new Set(parts.filter(Boolean))].join(', ');
}

function buildSeoFields(item) {
	const title = String(item.title ?? '').trim();
	const excerpt = String(item.excerpt ?? '').trim();
	const brandPart = String(item.related_brands ?? '').split('|')[0].trim();
	const focusKeyword = truncate(
		[title, brandPart].filter(Boolean).join(' ').toLowerCase(),
		80,
	);

	const seoTitle = truncate(`${title} в ${CITY} | ${BRAND}`, 60);
	const metaDescription = truncate(
		`${excerpt || title}. ${BRAND}, ${CITY_LONG}.`,
		160,
	);

	return {
		'Focus Keyword': focusKeyword,
		'SEO Title': seoTitle,
		'Meta Description': metaDescription,
		'Meta Keywords': buildMetaKeywords(focusKeyword),
		'Facebook Title': seoTitle,
		'Twitter Title': seoTitle,
	};
}

const EXCEL_CELL_MAX = 32700;

function truncateForExcel(value) {
	const text = String(value ?? '');
	if (text.length <= EXCEL_CELL_MAX) {
		return text;
	}
	return `${text.slice(0, EXCEL_CELL_MAX - 1)}…`;
}

function portfolioToRow(item, { forExcel = false } = {}) {
	const seo = buildSeoFields(item);
	const caseDescription = forExcel ? truncateForExcel(item.case_description) : item.case_description ?? '';

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
		item.price ?? '',
		caseDescription,
		item.what_we_did ?? '',
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
	const slug = pageEntry.local_dir?.split('/').pop();
	if (!slug) {
		throw new Error(`Missing local_dir for post ${pageEntry.id}`);
	}

	const pageDir = path.join(PAGES_DIR, 'post', slug);
	const [metaRaw, contentHtml] = await Promise.all([
		readFile(path.join(pageDir, 'meta.json'), 'utf8'),
		readFile(path.join(pageDir, 'content.html'), 'utf8'),
	]);

	return {
		meta: JSON.parse(metaRaw),
		contentHtml,
	};
}

function parsePortfolioCase({ pageEntry, meta, contentHtml, brandIndex, serviceIndex }) {
	const dom = new JSDOM(contentHtml);
	const { document } = dom.window;
	const article = document.querySelector('article');

	const metaTitle = cleanTitle(meta.title ?? pageEntry.title ?? '');
	const heroTitle = cleanTitle(
		document.querySelector('.entry-title-primary')?.textContent ?? metaTitle,
	);
	const cardQuote = document.querySelector('.entry-subtitle')?.textContent?.replace(/\s+/g, ' ').trim() ?? '';
	const excerpt = cardQuote || truncate(firstParagraphText(document), 300);
	const slug = slugFromPath(meta.path ?? pageEntry.path);

	const uploadImages = extractUploadImages(document, meta.images ?? []);
	const contentImages = pickContentImages(document);
	const heroImage = pickHeroImage(document, uploadImages);
	const beforeImage = contentImages[0] ?? '';
	const afterImage = contentImages[contentImages.length - 1] ?? heroImage;
	const featuredImage = afterImage || heroImage;

	const plainText = document.body?.textContent ?? '';
	const price = extractPrice(plainText);
	const caseDescription = extractCaseDescriptionHtml(document);

	const brands = resolveBrands({ article, title: heroTitle || metaTitle, brandIndex });
	const tags = extractTags(document);
	const services = resolveServices({ tags, title: heroTitle || metaTitle, serviceIndex });

	return {
		title: heroTitle || metaTitle,
		slug,
		excerpt,
		hero_title: heroTitle || metaTitle,
		card_quote: cardQuote,
		hero_image: heroImage,
		before_image: beforeImage,
		after_image: afterImage,
		featured_image: featuredImage,
		price,
		case_description: caseDescription,
		what_we_did: '',
		related_brands: brands.map((brand) => brand.title).join('|'),
		related_services: services.map((service) => service.title).join('|'),
		old_url: meta.url ?? pageEntry.url ?? '',
	};
}

function validateCases(cases) {
	if (!cases.length) {
		throw new Error('No portfolio cases to export');
	}

	const slugs = new Set();
	for (const item of cases) {
		if (!item.title) {
			throw new Error(`Case "${item.slug}" is missing title`);
		}
		if (!item.slug) {
			throw new Error(`Case "${item.title}" is missing slug`);
		}
		if (slugs.has(item.slug)) {
			throw new Error(`Duplicate slug "${item.slug}"`);
		}
		slugs.add(item.slug);
	}
}

async function writeOutputs(cases) {
	const excelRows = [HEADERS, ...cases.map((item) => portfolioToRow(item, { forExcel: true }))];
	const csvRows = excelRows;

	const workbook = XLSX.utils.book_new();
	XLSX.utils.book_append_sheet(workbook, XLSX.utils.aoa_to_sheet(excelRows), 'portfolio');
	XLSX.writeFile(workbook, OUTPUT_XLSX);

	const truncatedCount = cases.filter(
		(item) => String(item.case_description ?? '').length > EXCEL_CELL_MAX,
	).length;

	const importJson = {
		generated_for: 'WP All Import — CPT portfolio',
		source: path.basename(OUTPUT_XLSX),
		note:
			'title → post_title, slug → post_name (URL /portfolio/{slug}/). ACF-поля и featured_image — по именам колонок. related_brands / related_services — post title через |. Уникальный идентификатор — slug. case_description в xlsx обрезается до лимита Excel (32767); полный HTML — в portfolio-import.json.',
		columns: HEADERS,
		truncated_case_descriptions: truncatedCount,
		cases,
	};

	await writeFile(OUTPUT_JSON, `${JSON.stringify(importJson, null, 2)}\n`, 'utf8');

	const csvLines = csvRows.map((row) => row.map(escapeCsvCell).join(';'));
	const csvContent = `\uFEFF${csvLines.join('\r\n')}`;
	await writeFile(OUTPUT_CSV, csvContent, 'utf8');
}

async function main() {
	const [oldPages, brandsData, servicesData] = await Promise.all([
		readJson(OLD_PAGES_JSON),
		readJson(BRANDS_IMPORT_JSON),
		readJson(SERVICES_IMPORT_JSON),
	]);

	if (!oldPages?.pages?.length) {
		throw new Error(`Missing or empty ${OLD_PAGES_JSON}`);
	}
	if (!brandsData?.brands?.length) {
		throw new Error(`Missing brands — run: npm run import:brands`);
	}
	if (!servicesData?.services?.length) {
		throw new Error(`Missing services — run: npm run import:services`);
	}

	const brandIndex = buildBrandIndex(brandsData.brands);
	const serviceIndex = buildServiceIndex(servicesData.services);

	const postEntries = oldPages.pages.filter(
		(page) => page.type === 'post' && !page.is_demo && page.scraped && page.local_dir,
	);

	const cases = [];
	const errors = [];

	for (const pageEntry of postEntries) {
		try {
			const { meta, contentHtml } = await loadPostPage(pageEntry);
			cases.push(
				parsePortfolioCase({
					pageEntry,
					meta,
					contentHtml,
					brandIndex,
					serviceIndex,
				}),
			);
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

	validateCases(cases);
	await writeOutputs(cases);

	const withBrands = cases.filter((item) => item.related_brands).length;
	const withServices = cases.filter((item) => item.related_services).length;
	const withImages = cases.filter((item) => item.hero_image).length;

	console.log(`Generated ${OUTPUT_XLSX} (sheet: portfolio)`);
	console.log(`Generated ${OUTPUT_JSON}`);
	console.log(`Generated ${OUTPUT_CSV}`);
	console.log(`Cases: ${cases.length}`);
	console.log(`With brands: ${withBrands}/${cases.length}`);
	console.log(`With services: ${withServices}/${cases.length}`);
	console.log(`With hero_image: ${withImages}/${cases.length}`);
}

const isDirectRun = process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url);

if (isDirectRun) {
	main().catch((error) => {
		console.error(error);
		process.exit(1);
	});
}
