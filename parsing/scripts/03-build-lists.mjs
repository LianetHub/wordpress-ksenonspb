import { access } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import {
	BASE_URL,
	DATA_DIR,
	LOGS_DIR,
	PARSING_ROOT,
	localDirForEntry,
	readJson,
	slugFromPath,
	urlToPath,
	writeJson,
} from './utils.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SERVICES_IMPORT_PATH = path.join(DATA_DIR, 'services-import.json');

function buildServiceUrl(service) {
	if (service.reference_url) {
		return service.reference_url;
	}
	if (service.slug) {
		return `/${service.slug}/`;
	}
	return '/';
}

function buildServicePages(importData) {
	return (importData.services ?? []).map((service) => {
		const slug = service.slug ?? '';

		return {
			id: `service_${slug}`,
			title: service.title,
			url: buildServiceUrl(service),
			content_type: 'service',
			template: 'single-service.php',
			acf_group: 'group_ksenon_service.json',
			slug,
		};
	});
}

const INFO_PAGES = [
	{ slug: 'o-kompanii', title: 'О компании', template: 'page-o-kompanii.php', old_paths: ['/about-us/'] },
	{ slug: 'komanda', title: 'Команда', template: 'page.php', old_paths: ['/our-team/'] },
	{ slug: 'garantiya', title: 'Гарантия', template: 'page.php', old_paths: [] },
	{ slug: 'sertifikaty', title: 'Сертификаты', template: 'page.php', old_paths: [] },
	{ slug: 'stoimost', title: 'Стоимость услуг', template: 'page-stoimost.php', old_paths: ['/services/pricing/'] },
	{ slug: 'rassrochka', title: 'Рассрочка', template: 'page.php', old_paths: [] },
	{ slug: 'podarochnye-sertifikaty', title: 'Подарочные сертификаты', template: 'page.php', old_paths: [] },
	{ slug: 'priem-far-pochtoj', title: 'Приём фар почтой', template: 'page.php', old_paths: [] },
	{ slug: 'otzyvy', title: 'Отзывы', template: 'page.php', old_paths: ['/about-us/reviews/'] },
	{ slug: 'privacy-policy', title: 'Политика конфиденциальности', template: 'page-policy.php', old_paths: [], note: 'WP page ID 3' },
	{ slug: 'soglasie-na-obrabotku-personalnyh-dannyh', title: 'Согласие на обработку персональных данных', template: 'page-policy.php', old_paths: [], note: 'WP page ID 3555' },
];

const OLD_SERVICE_PATHS = [
	'/ремонт-фар/',
	'/автоохрана/',
	'/автоэлектрика/',
	'/мультимедиа/',
	'/другие-услуги/',
	'/светодиодный-тюнинг-light-label/',
	'/стекла-для-фар/',
	'/партнёры/',
];

function normalizePath(p) {
	let decoded = decodeURIComponent(p);
	if (!decoded.startsWith('/')) decoded = `/${decoded}`;
	if (!decoded.endsWith('/')) decoded += '/';
	return decoded.toLowerCase();
}

async function fileExists(filePath) {
	try {
		await access(filePath);
		return true;
	} catch {
		return false;
	}
}

function findEntryByPath(entries, pathname) {
	const norm = normalizePath(pathname);
	return entries.find((e) => normalizePath(e.path) === norm);
}

function portfolioSlugFromOld(entry) {
	return slugFromPath(entry.path);
}

function brandSlugFromCategory(entry) {
	const parts = entry.path.replace(/^\/+|\/+$/g, '').split('/');
	return parts[parts.length - 1] || slugFromPath(entry.path);
}

function buildUrlMapping(entries) {
	const mappings = [];

	const add = (oldPath, newPath, note = '') => {
		mappings.push({
			old_url: `${BASE_URL}${oldPath.startsWith('/') ? oldPath : `/${oldPath}`}`,
			new_url: newPath.startsWith('http') ? newPath : `${BASE_URL}${newPath.startsWith('/') ? newPath : `/${newPath}`}`,
			old_path: normalizePath(oldPath),
			new_path: newPath.startsWith('#') ? newPath : normalizePath(newPath),
			note,
		});
	};

	add('/blog/', '/portfolio/', 'Блог → портфолио');
	add('/каталог-работ-по-машинам/', '/portfolio/', 'Каталог работ');
	add('/каталог-работ-по-машинам/наши-работы/', '/portfolio/', 'Наши работы');
	add('/каталог-по-машинам/', '/marki/', 'Каталог по маркам');
	add('/ремонт-фар/', '/uslugi/', 'Главная услуга');
	add('/другие-услуги/', '/uslugi/', 'Другие услуги');
	add('/контакты/', '/kontakty/', 'Контакты');
	add('/contact-us/', '/kontakty/', 'Contact us → Контакты');
	add('/about-us/', '/o-kompanii/', 'О компании');
	add('/about-us/reviews/', '/otzyvy/', 'Отзывы');
	add('/our-team/', '/komanda/', 'Команда');
	add('/services/pricing/', '/stoimost/', 'Цены');

	for (const entry of entries.filter((e) => e.type === 'category')) {
		const slug = brandSlugFromCategory(entry);
		if (entry.path.includes('/category/portfolio/')) {
			add(entry.path, `/marki/${slug}/`, 'Марка из категории portfolio');
		}
	}

	for (const entry of entries.filter((e) => e.type === 'post' && !e.is_demo)) {
		const slug = portfolioSlugFromOld(entry);
		add(entry.path, `/portfolio/${slug}/`, 'Кейс блога → portfolio');
	}

	for (const entry of entries.filter((e) => e.type === 'page' && e.is_legacy_service)) {
		const slug = slugFromPath(entry.path);
		add(entry.path, `/marki/${slug.replace(/^ремонт-фар-|^remont-far-/i, '')}/`, 'Legacy brand service page');
	}

	return mappings;
}

async function buildOldPages(entries, progress) {
	const pages = [];

	for (const entry of entries) {
		const localDir = localDirForEntry(entry).replace(/\\/g, '/');
		const metaPath = path.join(PARSING_ROOT, localDir, 'meta.json');
		const scraped = !!(progress?.completed?.[entry.url] && (await fileExists(metaPath)));
		const prog = progress?.completed?.[entry.url];

		pages.push({
			id: entry.id,
			url: entry.url,
			path: entry.path,
			type: entry.type,
			title: prog?.title || null,
			lastmod: entry.lastmod,
			is_demo: entry.is_demo,
			is_legacy_service: entry.is_legacy_service,
			scraped,
			local_dir: scraped ? localDir : null,
			images_count: prog?.images_count ?? null,
		});
	}

	const byType = {};
	for (const p of pages) {
		byType[p.type] = (byType[p.type] || 0) + 1;
	}

	return {
		generated_at: new Date().toISOString(),
		source: `${BASE_URL}/sitemap.xml`,
		total: pages.length,
		by_type: byType,
		scraped_count: pages.filter((p) => p.scraped).length,
		pages,
	};
}

function buildNewPages(entries, importData) {
	const posts = entries.filter((e) => e.type === 'post' && !e.is_demo);
	const brands = entries.filter((e) => e.type === 'category' && e.path.includes('/category/portfolio/'));
	const tags = entries.filter((e) => e.type === 'tag');
	const products = entries.filter((e) => e.type === 'product');
	const demoPages = entries.filter((e) => e.is_demo);
	const servicePages = buildServicePages(importData);

	const pages = [
		{
			id: 'home',
			title: 'Главная',
			url: '/',
			content_type: 'page',
			template: 'front-page.php',
			acf_group: 'group_ksenon_front_page.json',
			source_urls: [`${BASE_URL}/`],
			migrate: true,
			status: 'implemented',
		},
		{
			id: 'services_archive',
			title: 'Наши услуги',
			url: '/uslugi/',
			content_type: 'service_archive',
			template: 'archive-service.php',
			source_urls: [
				`${BASE_URL}/ремонт-фар/`,
				`${BASE_URL}/другие-услуги/`,
				`${BASE_URL}/автоохрана/`,
				`${BASE_URL}/автоэлектрика/`,
				`${BASE_URL}/мультимедиа/`,
			],
			migrate: true,
			status: 'implemented',
		},
		...servicePages,
		{
			id: 'portfolio_archive',
			title: 'Наши работы',
			url: '/portfolio/',
			content_type: 'portfolio_archive',
			template: 'archive-portfolio.php',
			source_urls: [
				`${BASE_URL}/blog/`,
				`${BASE_URL}/каталог-работ-по-машинам/`,
			],
			migrate: true,
			status: 'implemented',
		},
		{
			id: 'portfolio_single',
			title_pattern: 'Кейс: {post_title}',
			url_pattern: '/portfolio/{slug}/',
			content_type: 'portfolio',
			template: 'single-portfolio.php',
			acf_group: 'group_ksenon_portfolio.json',
			source_filter: { old_type: 'post', is_demo: false },
			count_estimate: posts.length,
			migrate: true,
			status: 'implemented',
		},
		{
			id: 'brands_archive',
			title: 'Марки автомобилей',
			url: '/marki/',
			content_type: 'brand_archive',
			template: 'archive-brand.php',
			source_urls: [`${BASE_URL}/каталог-по-машинам/`],
			migrate: true,
			status: 'implemented',
		},
		{
			id: 'brand_single',
			title_pattern: 'Марка: {brand_name}',
			url_pattern: '/marki/{slug}/',
			content_type: 'brand',
			template: 'single-brand.php',
			acf_group: 'group_ksenon_brand.json',
			source_filter: { old_type: 'category', path_contains: '/category/portfolio/' },
			count_estimate: brands.length,
			migrate: true,
			status: 'implemented',
		},
		{
			id: 'promotions_archive',
			title: 'Акции',
			url: '/akcii/',
			content_type: 'promotion_archive',
			template: 'archive-promotion.php',
			source_urls: [],
			migrate: true,
			status: 'implemented',
			note: 'Новый контент, источников на старом сайте нет',
		},
		{
			id: 'promotion_single',
			title_pattern: 'Акция: {title}',
			url_pattern: '/akcii/{slug}/',
			content_type: 'promotion',
			template: 'single-promotion.php',
			acf_group: 'group_ksenon_promotion.json',
			source_urls: [],
			migrate: true,
			status: 'implemented',
		},
		...INFO_PAGES.map((p) => ({
			id: `page_${p.slug}`,
			title: p.title,
			url: `/${p.slug}/`,
			content_type: 'page',
			template: p.template,
			source_urls: p.old_paths.map((op) => `${BASE_URL}${op}`),
			migrate: true,
			status: p.template === 'page.php' ? 'fallback' : 'implemented',
		})),
		{
			id: 'search',
			title: 'Поиск',
			url: '/?s=',
			content_type: 'search',
			template: 'search.php',
			source_urls: [],
			migrate: true,
			status: 'implemented',
		},
		{
			id: 'not_found',
			title: '404',
			url: '/404',
			content_type: 'error',
			template: '404.php',
			source_urls: [],
			migrate: true,
			status: 'implemented',
		},
		{
			id: 'old_tags',
			title_pattern: 'Тег: {slug}',
			url_pattern: '/tag/{slug}/',
			content_type: 'tag',
			source_filter: { old_type: 'tag' },
			count_estimate: tags.length,
			migrate: false,
			status: 'not_migrated',
			note: 'Теги не переносятся; возможен 301 на /portfolio/ или noindex',
		},
		{
			id: 'old_products',
			title_pattern: 'Товар: {slug}',
			url_pattern: '/product/{slug}/',
			content_type: 'product',
			source_filter: { old_type: 'product' },
			count_estimate: products.length,
			migrate: false,
			status: 'not_migrated',
			note: 'WooCommerce вне scope новой темы',
		},
		{
			id: 'demo_pages',
			title: 'Демо-страницы старой темы',
			source_filter: { is_demo: true },
			count_estimate: demoPages.length,
			migrate: false,
			status: 'ignored',
			note: 'homepage-*, shop, cart и прочий мусор темы',
		},
	];

	return {
		generated_at: new Date().toISOString(),
		theme: 'ksenonspb 2.0.0',
		total_templates: pages.filter((p) => p.migrate !== false).length,
		pages,
	};
}

async function main() {
	const raw = await readJson(path.join(DATA_DIR, 'urls-raw.json'));
	if (!raw?.entries?.length) {
		console.error('urls-raw.json not found — run parse:sitemap first');
		process.exit(1);
	}

	const progress = await readJson(path.join(LOGS_DIR, 'scrape-progress.json'), { completed: {} });
	const importData = await readJson(SERVICES_IMPORT_PATH);

	console.log('Building old-pages.json...');
	const oldPages = await buildOldPages(raw.entries, progress);
	await writeJson(path.join(DATA_DIR, 'old-pages.json'), oldPages);

	console.log('Building new-pages.json...');
	const newPages = buildNewPages(raw.entries, importData);
	await writeJson(path.join(DATA_DIR, 'new-pages.json'), newPages);

	console.log('Building url-mapping.json...');
	const urlMapping = {
		generated_at: new Date().toISOString(),
		total: 0,
		mappings: buildUrlMapping(raw.entries),
	};
	urlMapping.total = urlMapping.mappings.length;
	await writeJson(path.join(DATA_DIR, 'url-mapping.json'), urlMapping);

	console.log('\nDone.');
	console.log(`old-pages.json: ${oldPages.total} entries (${oldPages.scraped_count} scraped)`);
	console.log(`new-pages.json: ${newPages.pages.length} template definitions`);
	console.log(`url-mapping.json: ${urlMapping.total} redirect rules`);
}

main().catch((error) => {
	console.error(error);
	process.exit(1);
});
