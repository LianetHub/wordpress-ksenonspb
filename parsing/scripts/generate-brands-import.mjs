import { writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import XLSX from 'xlsx';

import { resolveOfficialLogoUrl } from './brand-official-logos.mjs';
import { DATA_DIR, fetchWithRetry, readJson, writeJson } from './utils.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const OLD_PAGES_JSON = path.join(DATA_DIR, 'old-pages.json');
const SITE_LOGOS_JSON = path.join(DATA_DIR, 'brand-logos-site.json');
const DEFAULT_SITE_URL = 'http://ksenonspby.temp.swtest.ru';
const OUTPUT_XLSX = path.join(DATA_DIR, 'brands-import.xlsx');
const OUTPUT_JSON = path.join(DATA_DIR, 'brands-import.json');
const OUTPUT_CSV = path.join(DATA_DIR, 'brands-import.csv');

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
	drl: 'ДХО',
	hpl: 'HPL',
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

function brandSlugFromHref(href) {
	try {
		const pathname = new URL(href).pathname.replace(/^\/+|\/+$/g, '');
		const parts = pathname.split('/');
		return parts[parts.length - 1] || '';
	} catch {
		return '';
	}
}

/** Убирает суффикс WordPress -300x162, оставляет полный файл из uploads */
function normalizeUploadUrl(url) {
	const text = String(url ?? '').trim();
	if (!text) {
		return '';
	}

	return text.replace(/-\d+x\d+(\.(png|jpe?g|webp))$/i, '$1');
}

async function fetchSiteLogos(siteUrl) {
	const archiveUrl = `${siteUrl.replace(/\/+$/, '')}/marki/`;
	const response = await fetchWithRetry(archiveUrl);
	const html = await response.text();
	const logosBySlug = new Map();

	const cardRe =
		/<a[^>]+class="brand-card__link"[^>]+href="([^"]+)"[^>]*>[\s\S]*?<img[^>]+src="([^"]+)"[^>]*class="brand-card__logo"/gi;
	let match;

	while ((match = cardRe.exec(html)) !== null) {
		const slug = brandSlugFromHref(match[1]);
		const imageUrl = normalizeUploadUrl(match[2]);

		if (slug && imageUrl) {
			logosBySlug.set(slug, imageUrl);
		}
	}

	return Object.fromEntries(logosBySlug);
}

async function loadSiteLogoMap({ shouldFetch, siteUrl }) {
	if (shouldFetch) {
		const logos = await fetchSiteLogos(siteUrl);
		await writeJson(SITE_LOGOS_JSON, {
			fetched_at: new Date().toISOString(),
			source: `${siteUrl.replace(/\/+$/, '')}/marki/`,
			logos,
		});
		return logos;
	}

	const cached = await readJson(SITE_LOGOS_JSON);
	if (cached?.logos && Object.keys(cached.logos).length) {
		return cached.logos;
	}

	return null;
}

function attachImages(brands, siteLogos) {
	const warnings = [];

	for (const brand of brands) {
		const siteImage = siteLogos?.[brand.slug];
		brand.image = siteImage || resolveOfficialLogoUrl(brand.slug);
		brand.image_source = siteImage ? 'site' : 'official';

		if (!brand.image) {
			warnings.push(brand.slug);
		}
	}

	return warnings;
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
				image_source: '',
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

		if (!brand.image || !/^https?:\/\//.test(brand.image)) {
			throw new Error(`Missing image URL for "${brand.slug}"`);
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
		logo_sources: {
			site: 'Логотипы с /marki/ staging/production (wp-content/uploads)',
			official_fallback: 'Wikimedia Commons / carlogos.org — если нет в кэше сайта',
		},
		columns: HEADERS,
		brands,
	};

	await writeFile(OUTPUT_JSON, `${JSON.stringify(importJson, null, 2)}\n`, 'utf8');

	const csvLines = rows.map((row) => row.map(escapeCsvCell).join(';'));
	const csvContent = `\uFEFF${csvLines.join('\r\n')}`;
	await writeFile(OUTPUT_CSV, csvContent, 'utf8');
}

async function main() {
	const shouldFetchSite = process.argv.includes('--from-site');
	const siteUrl = process.env.BRANDS_SITE_URL || DEFAULT_SITE_URL;

	const oldPages = await readJson(OLD_PAGES_JSON);
	if (!oldPages?.pages?.length) {
		throw new Error(`No pages found in ${OLD_PAGES_JSON}`);
	}

	const brands = readBrandsFromOldPages(oldPages);
	const siteLogos = await loadSiteLogoMap({ shouldFetch: shouldFetchSite, siteUrl });
	const missingImageWarnings = attachImages(brands, siteLogos);
	const enriched = brands.map((brand) => enrichBrand(brand));

	validateBrands(enriched);
	await writeOutputs(enriched);

	const fromSite = enriched.filter((brand) => brand.image_source === 'site').length;

	console.log(`Source: ${OLD_PAGES_JSON}`);
	console.log(
		`Logos: ${shouldFetchSite ? `fetched from ${siteUrl}/marki/` : SITE_LOGOS_JSON}`,
	);
	console.log(`Generated ${OUTPUT_XLSX} (sheet: brands)`);
	console.log(`Generated ${OUTPUT_JSON}`);
	console.log(`Generated ${OUTPUT_CSV}`);
	console.log(`Brands: ${enriched.length}`);
	console.log(`Images from site: ${fromSite}/${enriched.length}`);

	if (missingImageWarnings.length) {
		console.warn(`Missing images for: ${missingImageWarnings.join(', ')}`);
	}
}

const isDirectRun = process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url);

if (isDirectRun) {
	main().catch((error) => {
		console.error(error);
		process.exit(1);
	});
}
