import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import XLSX from 'xlsx';

import { DATA_DIR } from './utils.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SOURCE_JSON = path.join(DATA_DIR, 'services-import.json');
const OUTPUT_JSON = SOURCE_JSON;
const OUTPUT_XLSX = path.join(DATA_DIR, 'services-import.xlsx');
const OUTPUT_CSV = path.join(DATA_DIR, 'services-import.csv');

const BRAND = 'КБ АВТО';
const CITY = 'СПб';
const CITY_LONG = 'Санкт-Петербург';

const BASE_HEADERS = [
	'title',
	'slug',
	'category_slug',
	'parent_category_slug',
	'price',
	'desc',
	'benefits',
	'category',
];

const SEO_HEADERS = [
	'Focus Keyword',
	'SEO Title',
	'Meta Description',
	'Meta Keywords',
	'Facebook Title',
	'Twitter Title',
];

const HEADERS = [...BASE_HEADERS, ...SEO_HEADERS];

function escapeCsvCell(value) {
	const text = String(value ?? '');
	if (/[;"\n\r]/.test(text)) {
		return `"${text.replace(/"/g, '""')}"`;
	}
	return text;
}

function formatPrice(price) {
	const amount = Number(price);
	if (!Number.isFinite(amount) || amount <= 0) {
		return '';
	}
	return amount.toLocaleString('ru-RU');
}

function truncate(text, max) {
	const normalized = String(text ?? '').replace(/\s+/g, ' ').trim();
	if (normalized.length <= max) {
		return normalized;
	}
	return `${normalized.slice(0, max - 1).trim()}…`;
}

function focusKeywordFromTitle(title) {
	return title.split(/[—(]/)[0].trim().toLowerCase();
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
	const desc = String(item.desc ?? '').trim();
	const priceFormatted = formatPrice(item.price);
	const focusKeyword =
		readSeoOverride(item, 'focus_keyword', 'Focus Keyword') || focusKeywordFromTitle(title);

	const seoTitle =
		readSeoOverride(item, 'seo_title', 'SEO Title') ||
		(priceFormatted
			? truncate(`${title} в ${CITY} — от ${priceFormatted} ₽ | ${BRAND}`, 60)
			: truncate(`${title} в ${CITY} | ${BRAND}`, 60));

	const metaDescription =
		readSeoOverride(item, 'meta_description', 'Meta Description') ||
		truncate(
			`${desc}${priceFormatted ? ` Цена от ${priceFormatted} ₽.` : ''} ${BRAND}, ${CITY_LONG}.`,
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

function enrichService(service) {
	const seo = buildSeoFields(service);

	return {
		...service,
		focus_keyword: readSeoOverride(service, 'focus_keyword', 'Focus Keyword') || seo['Focus Keyword'],
		seo_title: readSeoOverride(service, 'seo_title', 'SEO Title') || seo['SEO Title'],
		meta_description:
			readSeoOverride(service, 'meta_description', 'Meta Description') || seo['Meta Description'],
		meta_keywords: readSeoOverride(service, 'meta_keywords', 'Meta Keywords') || seo['Meta Keywords'],
		facebook_title: readSeoOverride(service, 'facebook_title', 'Facebook Title') || seo['Facebook Title'],
		twitter_title: readSeoOverride(service, 'twitter_title', 'Twitter Title') || seo['Twitter Title'],
	};
}

function serviceToRow(item) {
	const seo = buildSeoFields(item);

	return [
		item.title ?? '',
		item.slug ?? '',
		item.category_slug ?? '',
		item.parent_category_slug ?? '',
		item.price ?? '',
		item.desc ?? '',
		item.benefits ?? '',
		item.category ?? '',
		seo['Focus Keyword'],
		seo['SEO Title'],
		seo['Meta Description'],
		seo['Meta Keywords'],
		seo['Facebook Title'],
		seo['Twitter Title'],
	];
}

function buildServiceUrl(service) {
	const segments = [];
	if (service.parent_category_slug) {
		segments.push(service.parent_category_slug);
	}
	if (service.category_slug) {
		segments.push(service.category_slug);
	}
	if (service.slug) {
		segments.push(service.slug);
	}
	return `/${segments.join('/')}/`;
}

async function main() {
	const sourceRaw = await readFile(SOURCE_JSON, 'utf8');
	const source = JSON.parse(sourceRaw);
	const services = (source.services ?? []).map((service) => enrichService(service));

	if (!services.length) {
		throw new Error('No services found in services-import.json');
	}

	const slugs = new Set();
	for (const service of services) {
		const slug = String(service.slug ?? '');
		if (!slug) {
			throw new Error(`Missing slug for service "${service.title}"`);
		}
		if (slugs.has(slug)) {
			throw new Error(`Duplicate slug "${slug}" for service "${service.title}"`);
		}
		slugs.add(slug);
	}

	const importJson = {
		generated_for: 'WP All Import — CPT service',
		columns: HEADERS,
		services,
	};

	await writeFile(OUTPUT_JSON, `${JSON.stringify(importJson, null, 2)}\n`, 'utf8');

	const rows = [HEADERS, ...services.map(serviceToRow)];

	const workbook = XLSX.utils.book_new();
	const worksheet = XLSX.utils.aoa_to_sheet(rows);
	XLSX.utils.book_append_sheet(workbook, worksheet, 'services');
	XLSX.writeFile(workbook, OUTPUT_XLSX);

	const csvLines = rows.map((row) => row.map(escapeCsvCell).join(';'));
	const csvContent = `\uFEFF${csvLines.join('\r\n')}`;
	await writeFile(OUTPUT_CSV, csvContent, 'utf8');

	console.log(`Source: ${SOURCE_JSON}`);
	console.log(`Generated ${OUTPUT_JSON}`);
	console.log(`Generated ${OUTPUT_XLSX}`);
	console.log(`Generated ${OUTPUT_CSV}`);
	console.log(`Columns: ${HEADERS.join(', ')}`);
	console.log(`Rows: ${services.length} services + 1 header`);
	console.log('Sample URLs:');
	for (const service of services.slice(0, 3)) {
		console.log(`  ${buildServiceUrl(service)}`);
	}
}

main().catch((error) => {
	console.error(error);
	process.exit(1);
});
