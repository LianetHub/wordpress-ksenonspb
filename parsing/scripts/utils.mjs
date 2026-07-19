import { mkdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export const PARSING_ROOT = path.resolve(__dirname, '..');
export const DATA_DIR = path.join(PARSING_ROOT, 'data');
export const SITEMAP_DIR = path.join(PARSING_ROOT, 'sitemap');
export const PAGES_DIR = path.join(PARSING_ROOT, 'pages');
export const IMAGES_DIR = path.join(PARSING_ROOT, 'images');
export const LOGS_DIR = path.join(PARSING_ROOT, 'logs');

export const BASE_URL = 'https://ksenonspb.ru';
export const SITEMAP_URL = `${BASE_URL}/sitemap.xml`;
export const USER_AGENT = 'ksenonspb-migration-parser/1.0 (+https://ksenonspb.ru)';

export const DEMO_SLUGS = new Set([
	'homepage-05-one-page',
	'homepage-04',
	'homepage-03',
	'homepage-02',
	'homepage-07-blog-magazine',
	'home-corporate',
	'home',
	'shop',
	'cart',
	'checkout',
	'my-account',
	'coming-soon',
	'blank-page',
	'maintenance-mode',
	'elements',
	'gallery-style-03',
	'gallery-style-02',
	'gallery',
	'right-sidebar',
	'left-sidebar',
	'services',
]);

export const IMAGE_EXT = /\.(jpe?g|png|webp|gif|svg)(\?.*)?$/i;

export async function ensureDirs() {
	await Promise.all([
		mkdir(DATA_DIR, { recursive: true }),
		mkdir(SITEMAP_DIR, { recursive: true }),
		mkdir(PAGES_DIR, { recursive: true }),
		mkdir(IMAGES_DIR, { recursive: true }),
		mkdir(LOGS_DIR, { recursive: true }),
	]);
}

export async function writeJson(filePath, data) {
	await mkdir(path.dirname(filePath), { recursive: true });
	await writeFile(filePath, `${JSON.stringify(data, null, 2)}\n`, 'utf8');
}

export async function readJson(filePath, fallback = null) {
	try {
		const raw = await readFile(filePath, 'utf8');
		return JSON.parse(raw);
	} catch {
		return fallback;
	}
}

export function sleep(ms) {
	return new Promise((resolve) => setTimeout(resolve, ms));
}

export async function fetchWithRetry(url, options = {}, retries = 3) {
	let lastError;
	for (let attempt = 1; attempt <= retries; attempt++) {
		try {
			const response = await fetch(url, {
				...options,
				headers: {
					'User-Agent': USER_AGENT,
					...(options.headers || {}),
				},
			});
			if (response.ok || (response.status < 500 && response.status !== 429)) {
				return response;
			}
			lastError = new Error(`HTTP ${response.status} for ${url}`);
			if (attempt < retries) {
				await sleep(1000 * attempt);
			}
		} catch (error) {
			lastError = error;
			if (attempt < retries) {
				await sleep(1000 * attempt);
			}
		}
	}
	throw lastError;
}

export function parseSitemapUrls(xml, sitemapFile = '') {
	const urls = [];
	const urlBlocks = xml.match(/<url>[\s\S]*?<\/url>/g) || [];
	for (const block of urlBlocks) {
		const loc = block.match(/<loc>([^<]+)<\/loc>/)?.[1]?.trim();
		if (!loc) continue;
		const lastmod = block.match(/<lastmod>([^<]+)<\/lastmod>/)?.[1]?.trim() || null;
		const priority = block.match(/<priority>([^<]+)<\/priority>/)?.[1]?.trim() || null;
		urls.push({ url: loc, lastmod, priority, sitemap_file: sitemapFile });
	}
	return urls;
}

export function typeFromSitemapFile(filename) {
	if (filename === 'sitemap-misc.xml') return 'misc';
	if (filename.startsWith('sitemap-pt-page-')) return 'page';
	if (filename.startsWith('sitemap-pt-post-')) return 'post';
	if (filename === 'sitemap-tax-category.xml') return 'category';
	if (filename === 'sitemap-tax-post_tag.xml') return 'tag';
	if (filename.startsWith('sitemap-pt-product-')) return 'product';
	return 'unknown';
}

export function urlToPath(urlString) {
	try {
		const u = new URL(urlString);
		let p = decodeURIComponent(u.pathname);
		if (!p.endsWith('/')) p += '/';
		return p;
	} catch {
		return '/';
	}
}

export function slugFromPath(pathname) {
	const decoded = decodeURIComponent(pathname).replace(/^\/+|\/+$/g, '');
	if (!decoded) return 'home';
	return decoded.replace(/[^\w\u0400-\u04FF.-]+/gi, '-').replace(/-+/g, '-').replace(/^-|-$/g, '') || 'page';
}

const TRANSLIT_MAP = {
	а: 'a', б: 'b', в: 'v', г: 'g', д: 'd', е: 'e', ё: 'e', ж: 'zh',
	з: 'z', и: 'i', й: 'j', к: 'k', л: 'l', м: 'm', н: 'n', о: 'o',
	п: 'p', р: 'r', с: 's', т: 't', у: 'u', ф: 'f', х: 'h', ц: 'c',
	ч: 'ch', ш: 'sh', щ: 'sch', ъ: '', ы: 'y', ь: '', э: 'e', ю: 'yu', я: 'ya',
};

/**
 * Latin slug via Russian transliteration (WP-friendly post_name).
 * @param {string} value
 * @returns {string}
 */
export function transliterateSlug(value) {
	const text = String(value ?? '')
		.trim()
		.toLowerCase()
		.replace(/ё/g, 'е');

	let out = '';
	for (const char of text) {
		if (Object.prototype.hasOwnProperty.call(TRANSLIT_MAP, char)) {
			out += TRANSLIT_MAP[char];
		} else if (/[a-z0-9]/.test(char)) {
			out += char;
		} else {
			out += '-';
		}
	}

	return out.replace(/-+/g, '-').replace(/^-|-$/g, '') || 'page';
}

export function makePageId(type, pathname) {
	const slug = slugFromPath(pathname);
	if (type === 'category') {
		const parts = pathname.replace(/^\/+|\/+$/g, '').split('/');
		const catSlug = parts[parts.length - 1] || slug;
		return `category__${catSlug}`;
	}
	if (type === 'tag') {
		const parts = pathname.replace(/^\/+|\/+$/g, '').split('/');
		const tagSlug = parts[parts.length - 1] || slug;
		return `tag__${tagSlug}`;
	}
	return `${type}__${slug}`;
}

export function localDirForEntry(entry) {
	const pathname = urlToPath(entry.url);
	const slug = slugFromPath(pathname);

	if (entry.type === 'misc') {
		return path.join('pages', 'misc', slug === 'home' ? 'home' : slug);
	}
	if (entry.type === 'category') {
		const parts = pathname.replace(/^\/+|\/+$/g, '').split('/');
		const catParts = parts.slice(1);
		return path.join('pages', 'category', ...catParts);
	}
	if (entry.type === 'tag') {
		const parts = pathname.replace(/^\/+|\/+$/g, '').split('/');
		const tagSlug = parts[parts.length - 1] || slug;
		return path.join('pages', 'tag', tagSlug);
	}
	return path.join('pages', entry.type, slug);
}

export function isDemoPage(pathname) {
	if (pathname === '/' || pathname === '') return false;
	const slug = slugFromPath(pathname);
	if (DEMO_SLUGS.has(slug)) return true;
	if (/^homepage-\d+/i.test(slug)) return true;
	if (pathname.startsWith('/services/') && !pathname.includes('pricing')) return true;
	return false;
}

export function isLegacyServicePage(pathname) {
	const decoded = decodeURIComponent(pathname).toLowerCase();
	return /ремонт-фар/i.test(decoded) || /remont-far/i.test(decoded);
}
