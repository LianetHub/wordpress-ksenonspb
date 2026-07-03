import { writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import XLSX from 'xlsx';

import {
	CATEGORY_DEFAULTS,
	CATEGORY_SLUGS,
	SERVICE_CATEGORIES,
	categoryBySlug,
} from './category-constants.mjs';
import { DATA_DIR } from './utils.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SOURCE_XLSX = path.join(DATA_DIR, 'categories-import.xlsx');
const OUTPUT_JSON = path.join(DATA_DIR, 'categories-import.json');
const OUTPUT_XLSX = SOURCE_XLSX;
const OUTPUT_CSV = path.join(DATA_DIR, 'categories-import.csv');

const BRAND = 'КБ АВТО';
const CITY = 'СПб';
const CITY_LONG = 'Санкт-Петербург';

const CATEGORY_HEADERS = ['title', 'slug', 'parent_slug', 'desc', 'benefits'];

const SEO_HEADERS = [
	'Focus Keyword',
	'SEO Title',
	'Meta Description',
	'Meta Keywords',
	'Facebook Title',
	'Twitter Title',
];

const HEADERS = [...CATEGORY_HEADERS, ...SEO_HEADERS];

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
	const focusKeyword =
		readSeoOverride(item, 'focus_keyword', 'Focus Keyword') || focusKeywordFromTitle(title);

	const seoTitle =
		readSeoOverride(item, 'seo_title', 'SEO Title') ||
		truncate(`${title} в ${CITY} | ${BRAND}`, 60);

	const metaDescription =
		readSeoOverride(item, 'meta_description', 'Meta Description') ||
		truncate(`${desc} ${BRAND}, ${CITY_LONG}.`, 160);

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

function normalizeCategory(raw) {
	const slug = String(raw.slug ?? '').trim();
	const canonical = categoryBySlug(slug);

	return {
		title: String(raw.title ?? canonical?.name ?? '').trim(),
		slug,
		parent_slug: String(raw.parent_slug ?? canonical?.parent_slug ?? '').trim(),
		desc: String(raw.desc ?? '').trim(),
		benefits: String(raw.benefits ?? '').trim(),
		focus_keyword: readSeoOverride(raw, 'focus_keyword', 'Focus Keyword'),
		seo_title: readSeoOverride(raw, 'seo_title', 'SEO Title'),
		meta_description: readSeoOverride(raw, 'meta_description', 'Meta Description'),
		meta_keywords: readSeoOverride(raw, 'meta_keywords', 'Meta Keywords'),
		facebook_title: readSeoOverride(raw, 'facebook_title', 'Facebook Title'),
		twitter_title: readSeoOverride(raw, 'twitter_title', 'Twitter Title'),
	};
}

function enrichCategory(category) {
	const seo = buildSeoFields(category);

	return {
		...category,
		focus_keyword: category.focus_keyword || seo['Focus Keyword'],
		seo_title: category.seo_title || seo['SEO Title'],
		meta_description: category.meta_description || seo['Meta Description'],
		meta_keywords: category.meta_keywords || seo['Meta Keywords'],
		facebook_title: category.facebook_title || seo['Facebook Title'],
		twitter_title: category.twitter_title || seo['Twitter Title'],
	};
}

function categoryToRow(item) {
	const seo = buildSeoFields(item);

	return [
		item.title ?? '',
		item.slug ?? '',
		item.parent_slug ?? '',
		item.desc ?? '',
		item.benefits ?? '',
		seo['Focus Keyword'],
		seo['SEO Title'],
		seo['Meta Description'],
		seo['Meta Keywords'],
		seo['Facebook Title'],
		seo['Twitter Title'],
	];
}

function readCategoriesFromXlsx() {
	const workbook = XLSX.readFile(SOURCE_XLSX);
	if (!workbook.SheetNames.includes('categories')) {
		throw new Error('Sheet "categories" not found in categories-import.xlsx');
	}

	const rows = XLSX.utils.sheet_to_json(workbook.Sheets.categories);
	return rows.map(normalizeCategory);
}

function readLegacyContentMap() {
	try {
		const workbook = XLSX.readFile(SOURCE_XLSX);
		if (!workbook.SheetNames.includes('categories')) {
			return new Map();
		}

		const rows = XLSX.utils.sheet_to_json(workbook.Sheets.categories);
		const contentBySlug = new Map();

		for (const row of rows) {
			const slug = String(row.slug ?? '').trim();
			if (!CATEGORY_SLUGS.has(slug)) {
				continue;
			}

			contentBySlug.set(slug, {
				desc: String(row.desc ?? '').trim(),
				benefits: String(row.benefits ?? '').trim(),
				focus_keyword: readSeoOverride(row, 'focus_keyword', 'Focus Keyword'),
				seo_title: readSeoOverride(row, 'seo_title', 'SEO Title'),
				meta_description: readSeoOverride(row, 'meta_description', 'Meta Description'),
				meta_keywords: readSeoOverride(row, 'meta_keywords', 'Meta Keywords'),
				facebook_title: readSeoOverride(row, 'facebook_title', 'Facebook Title'),
				twitter_title: readSeoOverride(row, 'twitter_title', 'Twitter Title'),
			});
		}

		return contentBySlug;
	} catch {
		return new Map();
	}
}

function buildCanonicalCategories() {
	const contentBySlug = readLegacyContentMap();

	return SERVICE_CATEGORIES.map((category) => {
		const legacy = contentBySlug.get(category.slug) ?? {};
		const defaults = CATEGORY_DEFAULTS[category.slug] ?? {};

		return normalizeCategory({
			title: category.name,
			slug: category.slug,
			parent_slug: category.parent_slug,
			desc: legacy.desc ?? defaults.desc ?? '',
			benefits: legacy.benefits ?? defaults.benefits ?? '',
			focus_keyword: legacy.focus_keyword ?? '',
			seo_title: legacy.seo_title ?? '',
			meta_description: legacy.meta_description ?? '',
			meta_keywords: legacy.meta_keywords ?? '',
			facebook_title: legacy.facebook_title ?? '',
			twitter_title: legacy.twitter_title ?? '',
		});
	});
}

function validateCategories(categories) {
	if (!categories.length) {
		throw new Error('No categories found in categories-import.xlsx');
	}

	const slugs = new Set();

	for (const category of categories) {
		if (!category.title) {
			throw new Error('Category row is missing title');
		}
		if (!category.slug) {
			throw new Error(`Missing slug for category "${category.title}"`);
		}
		if (!CATEGORY_SLUGS.has(category.slug)) {
			throw new Error(`Unknown category slug "${category.slug}"`);
		}
		if (slugs.has(category.slug)) {
			throw new Error(`Duplicate slug "${category.slug}"`);
		}
		slugs.add(category.slug);

		const canonical = categoryBySlug(category.slug);
		if (category.title !== canonical.name) {
			throw new Error(
				`Category "${category.slug}": title must be "${canonical.name}", got "${category.title}"`,
			);
		}
		if (category.parent_slug !== canonical.parent_slug) {
			throw new Error(
				`Category "${category.slug}": parent_slug must be "${canonical.parent_slug}", got "${category.parent_slug}"`,
			);
		}
	}

	const missing = [...CATEGORY_SLUGS].filter((slug) => !slugs.has(slug));
	if (missing.length) {
		throw new Error(`Missing categories from canonical list: ${missing.join('; ')}`);
	}

	if (categories.length !== CATEGORY_SLUGS.size) {
		throw new Error(`Expected ${CATEGORY_SLUGS.size} categories, got ${categories.length}`);
	}
}

function rebuildSourceXlsx() {
	const categories = buildCanonicalCategories().map((category) => enrichCategory(category));
	const categoryRows = [HEADERS, ...categories.map((category) => categoryToRow(category))];

	const workbook = XLSX.utils.book_new();
	XLSX.utils.book_append_sheet(workbook, XLSX.utils.aoa_to_sheet(categoryRows), 'categories');
	XLSX.writeFile(workbook, OUTPUT_XLSX);

	return categories.length;
}

async function main() {
	const shouldRebuild = process.argv.includes('--rebuild-xlsx');
	if (shouldRebuild) {
		const count = rebuildSourceXlsx();
		console.log(`Rebuilt ${OUTPUT_XLSX} with ${count} canonical categories`);
	}

	const categories = readCategoriesFromXlsx().map((category) => enrichCategory(category));
	validateCategories(categories);

	const importJson = {
		generated_for: 'WP All Import — taxonomy service_category',
		source: path.basename(SOURCE_XLSX),
		note: 'title → term.name, slug → term.slug, parent_slug → parent term (lookup by slug), desc → term.description, benefits → ACF card_labels (comma-separated). Уникальный идентификатор — slug.',
		columns: HEADERS,
		categories,
	};

	await writeFile(OUTPUT_JSON, `${JSON.stringify(importJson, null, 2)}\n`, 'utf8');

	const categoryRows = [HEADERS, ...categories.map((category) => categoryToRow(category))];

	const workbook = XLSX.utils.book_new();
	XLSX.utils.book_append_sheet(workbook, XLSX.utils.aoa_to_sheet(categoryRows), 'categories');
	XLSX.writeFile(workbook, OUTPUT_XLSX);

	const csvLines = categoryRows.map((row) => row.map(escapeCsvCell).join(';'));
	const csvContent = `\uFEFF${csvLines.join('\r\n')}`;
	await writeFile(OUTPUT_CSV, csvContent, 'utf8');

	console.log(`Source: ${SOURCE_XLSX}`);
	console.log(`Generated ${OUTPUT_JSON}`);
	console.log(`Generated ${OUTPUT_XLSX} (sheet: categories)`);
	console.log(`Generated ${OUTPUT_CSV}`);
	console.log(`Categories: ${categories.length}`);
}

const isDirectRun = process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url);

if (isDirectRun) {
	main().catch((error) => {
		console.error(error);
		process.exit(1);
	});
}
