import { writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import XLSX from 'xlsx';

import { BASE_URL, DATA_DIR, fetchWithRetry, readJson, writeJson } from './utils.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const OLD_PAGES_JSON = path.join(DATA_DIR, 'old-pages.json');
const LOGOS_CACHE_JSON = path.join(DATA_DIR, 'brand-logos.json');
const OUTPUT_XLSX = path.join(DATA_DIR, 'brands-import.xlsx');
const OUTPUT_JSON = path.join(DATA_DIR, 'brands-import.json');
const OUTPUT_CSV = path.join(DATA_DIR, 'brands-import.csv');

const CATALOG_URL = `${BASE_URL}/%D0%BA%D0%B0%D1%82%D0%B0%D0%BB%D0%BE%D0%B3-%D0%BF%D0%BE-%D0%BC%D0%B0%D1%88%D0%B8%D0%BD%D0%B0%D0%BC/`;

const BRAND = 'КБ АВТО';
const CITY = 'СПб';
const CITY_LONG = 'Санкт-Петербург';

/** Slug из спарсинга → slug по ТЗ */
const SLUG_MAP = {
	huyndai: 'hyundai',
	scoda: 'skoda',
};

/** Человекочитаемое название для slug с исправлениями */
const TITLE_BY_SLUG = {
	hyundai: 'Hyundai',
	skoda: 'Skoda',
	'rolls-royce': 'Rolls-Royce',
	'land-rover': 'Land Rover',
	vw: 'Volkswagen',
};

/**
 * Имя файла логотипа на старом сайте ≠ slug категории.
 * Ключ — old slug из /category/portfolio/{slug}/
 */
const LOGO_FILE_ALIASES = {
	mercedes: 'mb',
	huyndai: 'hyundai',
	scoda: 'skoda',
	maserati: 'maseratti',
	'rolls-royce': 'rolls-r',
	'land-rover': 'landrover',
	bmw: 'bmw-100x100',
	cadillac: 'cadillac-100x100',
	dodge: 'dodge-100x100',
	kia: 'kia-100x100',
	mazda: 'mazda-100x100',
	nissan: 'nissan-100x100',
	opel: 'opel-100x100',
};

const BRAND_HEADERS = ['title', 'slug', 'image'];

const SEO_HEADERS = [
	'Focus Keyword',
	'SEO Title',
	'Meta Description',
	'Meta Keywords',
	'Facebook Title',
	'Twitter Title',
];

const HEADERS = [...BRAND_HEADERS, ...SEO_HEADERS];

const UPLOADS_LOGO_RE =
	/https:\/\/ksenonspb\.ru\/wp-content\/uploads\/(?:\d{4}\/\d{2}\/)?([a-z0-9._-]+)\.(jpe?g|png|webp)/i;

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

function brandSlugFromPath(pagePath) {
	const parts = pagePath.replace(/^\/+|\/+$/g, '').split('/');
	return parts[parts.length - 1] || '';
}

function titleFromOldPageTitle(rawTitle) {
	const text = String(rawTitle ?? '').trim();
	if (!text) {
		return '';
	}
	return text.split(/\s+[—–-]\s+/)[0].trim();
}

function mapSlug(oldSlug) {
	return SLUG_MAP[oldSlug] ?? oldSlug;
}

function normalizeImageUrl(url) {
	if (!url) {
		return '';
	}
	try {
		const parsed = new URL(url);
		if (parsed.hostname !== 'ksenonspb.ru') {
			return '';
		}
		return parsed.href;
	} catch {
		return '';
	}
}

function logoBasenameFromUrl(url) {
	const match = String(url).match(UPLOADS_LOGO_RE);
	return match ? match[1].toLowerCase() : '';
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

function readSeoOverride(item, jsonKey, headerKey) {
	return item[jsonKey] ?? item[headerKey] ?? '';
}

function buildSeoFields(item) {
	const title = String(item.title ?? '').trim();
	const focusKeyword =
		readSeoOverride(item, 'focus_keyword', 'Focus Keyword') ||
		`ремонт фар ${title}`.toLowerCase();

	const seoTitle =
		readSeoOverride(item, 'seo_title', 'SEO Title') ||
		truncate(`Ремонт и тюнинг фар ${title} в ${CITY} | ${BRAND}`, 60);

	const metaDescription =
		readSeoOverride(item, 'meta_description', 'Meta Description') ||
		truncate(
			`Ремонт и тюнинг фар ${title} в Санкт-Петербурге — восстановление оптики, установка линз, устранение запотевания. ${BRAND}, ${CITY_LONG}.`,
			160,
		);

	const metaKeywords =
		readSeoOverride(item, 'meta_keywords', 'Meta Keywords') || buildMetaKeywords(focusKeyword);

	const facebookTitle =
		readSeoOverride(item, 'facebook_title', 'Facebook Title') || seoTitle;

	const twitterTitle =
		readSeoOverride(item, 'twitter_title', 'Twitter Title') || seoTitle;

	return {
		'Focus Keyword': focusKeyword,
		'SEO Title': seoTitle,
		'Meta Description': metaDescription,
		'Meta Keywords': metaKeywords,
		'Facebook Title': facebookTitle,
		'Twitter Title': twitterTitle,
	};
}

function enrichBrand(brand) {
	const seo = buildSeoFields(brand);

	return {
		...brand,
		focus_keyword: brand.focus_keyword || seo['Focus Keyword'],
		seo_title: brand.seo_title || seo['SEO Title'],
		meta_description: brand.meta_description || seo['Meta Description'],
		meta_keywords: brand.meta_keywords || seo['Meta Keywords'],
		facebook_title: brand.facebook_title || seo['Facebook Title'],
		twitter_title: brand.twitter_title || seo['Twitter Title'],
	};
}

function brandToRow(item) {
	const seo = buildSeoFields(item);

	return [
		item.title ?? '',
		item.slug ?? '',
		item.image ?? '',
		seo['Focus Keyword'],
		seo['SEO Title'],
		seo['Meta Description'],
		seo['Meta Keywords'],
		seo['Facebook Title'],
		seo['Twitter Title'],
	];
}

function readBrandsFromOldPages(oldPages) {
	const pages = oldPages?.pages ?? [];

	return pages
		.filter(
			(entry) =>
				entry.type === 'category' &&
				entry.path.startsWith('/category/portfolio/') &&
				entry.path !== '/category/portfolio/',
		)
		.map((entry) => {
			const oldSlug = brandSlugFromPath(entry.path);
			const slug = mapSlug(oldSlug);
			const parsedTitle = titleFromOldPageTitle(entry.title);

			return {
				old_slug: oldSlug,
				slug,
				title: TITLE_BY_SLUG[slug] ?? parsedTitle,
				old_url: entry.url,
				image: '',
				focus_keyword: '',
				seo_title: '',
				meta_description: '',
				meta_keywords: '',
				facebook_title: '',
				twitter_title: '',
			};
		})
		.sort((a, b) => a.title.localeCompare(b.title, 'ru'));
}

function extractSlugHintFromHref(href) {
	const decoded = decodeURIComponent(String(href));

	const remontMatch = decoded.match(/ремонт-фар-([a-z0-9-]+)/i);
	if (remontMatch) {
		return remontMatch[1].toLowerCase();
	}

	const portfolioMatch = decoded.match(/\/category\/portfolio\/([^/?#]+)/i);
	if (portfolioMatch) {
		return portfolioMatch[1].toLowerCase();
	}

	return '';
}

async function fetchCatalogLogos() {
	const response = await fetchWithRetry(CATALOG_URL);
	const html = await response.text();
	const logosByOldSlug = new Map();

	const pairRe = /<a[^>]+href="([^"]+)"[^>]*>[\s\S]*?<img[^>]+src="([^"]+)"[^>]*>/gi;
	let match;

	while ((match = pairRe.exec(html)) !== null) {
		const href = match[1];
		const imageUrl = normalizeImageUrl(match[2]);

		if (!imageUrl || !imageUrl.includes('/wp-content/uploads/')) {
			continue;
		}

		const hint = extractSlugHintFromHref(href);
		if (hint) {
			logosByOldSlug.set(hint, imageUrl);
		}

		const basename = logoBasenameFromUrl(imageUrl);
		if (basename) {
			logosByOldSlug.set(basename, imageUrl);
		}
	}

	return Object.fromEntries(logosByOldSlug);
}

async function loadLogoMap(shouldFetch) {
	if (!shouldFetch) {
		const cached = await readJson(LOGOS_CACHE_JSON);
		if (cached?.logos && Object.keys(cached.logos).length) {
			return cached.logos;
		}
	}

	const logos = await fetchCatalogLogos();
	await writeJson(LOGOS_CACHE_JSON, {
		fetched_at: new Date().toISOString(),
		source: CATALOG_URL,
		logos,
	});

	return logos;
}

function resolveLogoFromMap(oldSlug, logoMap) {
	const candidates = new Set([oldSlug]);

	if (LOGO_FILE_ALIASES[oldSlug]) {
		candidates.add(LOGO_FILE_ALIASES[oldSlug]);
	}

	for (const candidate of candidates) {
		if (logoMap[candidate]) {
			return normalizeImageUrl(logoMap[candidate]);
		}
	}

	for (const [key, url] of Object.entries(logoMap)) {
		const basename = logoBasenameFromUrl(url);
		for (const candidate of candidates) {
			if (
				key === candidate ||
				basename === candidate ||
				basename === candidate.replace(/-100x100$/, '')
			) {
				return normalizeImageUrl(url);
			}
		}
	}

	return '';
}

async function fetchCategoryFallbackImage(oldSlug) {
	const url = `${BASE_URL}/category/portfolio/${oldSlug}/`;

	try {
		const response = await fetchWithRetry(url);
		const html = await response.text();
		const matches = [
			...html.matchAll(
				/https:\/\/ksenonspb\.ru\/wp-content\/uploads\/[^"'\s>]+\.(?:jpe?g|png|webp)/gi,
			),
		];

		for (const item of matches) {
			const imageUrl = normalizeImageUrl(item[0]);
			if (imageUrl && !imageUrl.includes('logooo')) {
				return imageUrl;
			}
		}
	} catch (error) {
		console.warn(`Failed to fetch fallback image for ${oldSlug}: ${error.message}`);
	}

	return '';
}

async function attachImages(brands, logoMap) {
	const warnings = [];

	for (const brand of brands) {
		let image = resolveLogoFromMap(brand.old_slug, logoMap);

		if (!image) {
			image = await fetchCategoryFallbackImage(brand.old_slug);
		}

		brand.image = image;

		if (!image) {
			warnings.push(brand.old_slug);
		}
	}

	return warnings;
}

function validateBrands(brands) {
	if (brands.length !== 42) {
		throw new Error(`Expected 42 brands, got ${brands.length}`);
	}

	const slugs = new Set();

	for (const brand of brands) {
		if (!brand.title) {
			throw new Error(`Brand "${brand.old_slug}" is missing title`);
		}
		if (!brand.slug) {
			throw new Error(`Brand "${brand.title}" is missing slug`);
		}
		if (slugs.has(brand.slug)) {
			throw new Error(`Duplicate slug "${brand.slug}"`);
		}
		slugs.add(brand.slug);

		if (brand.image && !brand.image.startsWith('https://ksenonspb.ru/wp-content/uploads/')) {
			throw new Error(`Invalid image URL for "${brand.slug}": ${brand.image}`);
		}
	}
}

async function writeOutputs(brands) {
	const rows = [HEADERS, ...brands.map((brand) => brandToRow(brand))];

	const workbook = XLSX.utils.book_new();
	XLSX.utils.book_append_sheet(workbook, XLSX.utils.aoa_to_sheet(rows), 'brands');
	XLSX.writeFile(workbook, OUTPUT_XLSX);

	const importJson = {
		generated_for: 'WP All Import — CPT brand',
		source: path.basename(OUTPUT_XLSX),
		note:
			'title → post_title, slug → post_name (URL /marki/{slug}/), image → Featured Image и/или ACF logo, SEO-колонки → Rank Math / Yoast. Уникальный идентификатор — slug.',
		columns: HEADERS,
		brands,
	};

	await writeFile(OUTPUT_JSON, `${JSON.stringify(importJson, null, 2)}\n`, 'utf8');

	const csvLines = rows.map((row) => row.map(escapeCsvCell).join(';'));
	const csvContent = `\uFEFF${csvLines.join('\r\n')}`;
	await writeFile(OUTPUT_CSV, csvContent, 'utf8');
}

async function main() {
	const shouldFetchLogos = process.argv.includes('--fetch-logos');

	const oldPages = await readJson(OLD_PAGES_JSON);
	if (!oldPages?.pages?.length) {
		throw new Error(`No pages found in ${OLD_PAGES_JSON}`);
	}

	const brands = readBrandsFromOldPages(oldPages);
	const logoMap = await loadLogoMap(shouldFetchLogos);
	const missingImageWarnings = await attachImages(brands, logoMap);
	const enriched = brands.map((brand) => enrichBrand(brand));

	validateBrands(enriched);
	await writeOutputs(enriched);

	console.log(`Source: ${OLD_PAGES_JSON}`);
	console.log(`Logos: ${shouldFetchLogos ? 'fetched from catalog' : LOGOS_CACHE_JSON}`);
	console.log(`Generated ${OUTPUT_XLSX} (sheet: brands)`);
	console.log(`Generated ${OUTPUT_JSON}`);
	console.log(`Generated ${OUTPUT_CSV}`);
	console.log(`Brands: ${enriched.length}`);

	if (missingImageWarnings.length) {
		console.warn(`Missing images for: ${missingImageWarnings.join(', ')}`);
	}

	const withImages = enriched.filter((brand) => brand.image).length;
	console.log(`Images resolved: ${withImages}/${enriched.length}`);
}

const isDirectRun = process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url);

if (isDirectRun) {
	main().catch((error) => {
		console.error(error);
		process.exit(1);
	});
}
