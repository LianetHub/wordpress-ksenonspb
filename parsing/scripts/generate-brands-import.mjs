import { writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import XLSX from 'xlsx';

import { DATA_DIR, fetchWithRetry, readJson, writeJson } from './utils.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const SITE_BRANDS_JSON = path.join(DATA_DIR, 'brand-logos-site.json');
const DEFAULT_SITE_URL = 'http://ksenonspby.temp.swtest.ru';
const OUTPUT_XLSX = path.join(DATA_DIR, 'brands-import.xlsx');
const OUTPUT_JSON = path.join(DATA_DIR, 'brands-import.json');
const OUTPUT_CSV = path.join(DATA_DIR, 'brands-import.csv');

const BRAND = 'КБ АВТО';
const CITY = 'СПб';
const CITY_LONG = 'Санкт-Петербург';

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

	return text.replace(/-\d+x\d+(\.(png|jpe?g|webp|svg))$/i, '$1');
}

function titleFromCardHtml(cardHtml) {
	const match = cardHtml.match(/class="brand-card__title"[^>]*>([^<]+)/i);
	if (!match) {
		return '';
	}

	return match[1].replace(/\s*→\s*$/u, '').trim();
}

function imageFromCardHtml(cardHtml) {
	const match = cardHtml.match(/class="brand-card__logo"[^>]*src="([^"]+)"/i)
		|| cardHtml.match(/src="([^"]+)"[^>]*class="brand-card__logo"/i);

	return match ? normalizeUploadUrl(match[1]) : '';
}

async function fetchSiteBrands(siteUrl) {
	const archiveUrl = `${siteUrl.replace(/\/+$/, '')}/marki/`;
	const response = await fetchWithRetry(archiveUrl);
	const html = await response.text();
	const brands = [];

	const cardRe =
		/<a[^>]+class="brand-card__link"[^>]+href="([^"]+)"[^>]*>([\s\S]*?)<\/a>/gi;
	let match;

	while ((match = cardRe.exec(html)) !== null) {
		const slug = brandSlugFromHref(match[1]);
		const title = titleFromCardHtml(match[2]);
		const image = imageFromCardHtml(match[2]);

		if (!slug) {
			continue;
		}

		brands.push({
			slug,
			title,
			image,
			image_source: 'site',
		});
	}

	return brands;
}

function emptySeoFields() {
	return {
		focus_keyword: '',
		seo_title: '',
		meta_description: '',
		meta_keywords: '',
		facebook_title: '',
		twitter_title: '',
	};
}

async function loadSiteBrands({ shouldFetch, siteUrl }) {
	if (shouldFetch) {
		const brands = await fetchSiteBrands(siteUrl);
		await writeJson(SITE_BRANDS_JSON, {
			fetched_at: new Date().toISOString(),
			source: `${siteUrl.replace(/\/+$/, '')}/marki/`,
			brands,
		});
		return brands;
	}

	const cached = await readJson(SITE_BRANDS_JSON);
	if (cached?.brands?.length) {
		return cached.brands;
	}

	return null;
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

function validateBrands(brands) {
	if (!brands.length) {
		throw new Error('No brands to export');
	}

	const slugs = new Set();

	for (const brand of brands) {
		if (!brand.title) {
			throw new Error(`Brand "${brand.slug}" is missing title`);
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
		logo_source: 'Список марок и image URL с /marki/ staging/production',
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

	const brands = await loadSiteBrands({ shouldFetch: shouldFetchSite, siteUrl });
	if (!brands?.length) {
		throw new Error(
			`No brands in cache. Run: npm run import:brands -- --from-site`,
		);
	}

	const enriched = brands.map((brand) =>
		enrichBrand({
			...emptySeoFields(),
			...brand,
		}),
	);

	const missingImages = enriched.filter((brand) => !brand.image).map((brand) => brand.slug);

	validateBrands(enriched);
	await writeOutputs(enriched);

	console.log(
		`Source: ${shouldFetchSite ? `${siteUrl}/marki/` : SITE_BRANDS_JSON}`,
	);
	console.log(`Generated ${OUTPUT_XLSX} (sheet: brands)`);
	console.log(`Generated ${OUTPUT_JSON}`);
	console.log(`Generated ${OUTPUT_CSV}`);
	console.log(`Brands: ${enriched.length}`);
	console.log(`Images: ${enriched.length - missingImages.length}/${enriched.length}`);

	if (missingImages.length) {
		console.warn(`Missing images for: ${missingImages.join(', ')}`);
	}
}

const isDirectRun = process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url);

if (isDirectRun) {
	main().catch((error) => {
		console.error(error);
		process.exit(1);
	});
}
