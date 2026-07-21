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

const BRAND_HEADERS = ['title', 'slug', 'image', 'hero_title', 'features_title', 'features'];

const SEO_HEADERS = [
	'Focus Keyword',
	'SEO Title',
	'Meta Description',
	'Meta Keywords',
	'Facebook Title',
	'Twitter Title',
];

const HEADERS = [...BRAND_HEADERS, ...SEO_HEADERS];

/** BMW — контент из макета Figma (узел 414:660). */
const BMW_FEATURES = [
	{
		title: 'Герметизация и «стекло»',
		text: 'Фары BMW из поликарбоната мутнеют и царапаются. Заводской лак на них очень твердый, но тонкий. Грубая полировка стирает УФ-защиту навсегда. Правильный ремонт — это полное снятие старого лака, глубокая шлифовка и нанесение нового двухкомпонентного защитного слоя.',
	},
	{
		title: 'Электроника и датчики',
		text: 'Просто заменить диод нельзя. Светодиодные фары плотно интегрированы с блоками TMS, которые горят при замыкании или сырости. Часто проблема в сгоревших драйверах на плате. Ремонт требует микроэлектроники: диагностики, пайки шлейфов и чистки под микроскопом.',
	},
	{
		title: 'Разгерметизация и дренаж',
		text: 'Корпуса имеют сложную вентиляцию с мембранами, а не просто заглушки. Запотевание почти всегда вызвано забитыми или разрушенными мембранами, а не только трещинами. Если просто залить шов герметиком, не заменив мембраны, фара превратится в «аквариум».',
	},
	{
		title: 'Неразборный корпус и линзы',
		text: 'Современные модели (G-серия, лазерный свет) собраны на жестком полиуретановом герметике. Вскрытие требует точного нагрева или аккуратного распила шва. Кустарный разбор ведет к трещинам дорогого пластика. Замена битых линз или LED-модулей — это ювелирная пайка на едином радиаторе.',
	},
];

/** Специфичные особенности отдельных марок (поверх базового шаблона). */
const BRAND_FEATURE_OVERRIDES = {
	bmw: BMW_FEATURES,
	audi: [
		{
			title: 'Матричный свет и Matrix LED',
			text: 'На Audi (A6 C7/C8, A8, Q7/Q8) часто выгорают отдельные сегменты Matrix LED и блоки управления. Простая замена лампы не поможет — нужна диагностика шины, прошивка и точечный ремонт драйверов.',
		},
		{
			title: 'Герметизация и стёкла',
			text: 'Поликарбонатные стёкла Audi мутнеют и желтеют. Заводской лак тонкий: грубая полировка снимает УФ-защиту. Корректный ремонт — снятие лака, шлифовка и новый двухкомпонентный защитный слой.',
		},
		{
			title: 'Запотевание и дренаж',
			text: 'Вентиляционные мембраны в корпусах Audi часто забиваются. Если загерметизировать шов без замены дренажа, внутри скапливается конденсат — «аквариум» и коррозия контактов.',
		},
		{
			title: 'Разбор корпуса и линзы',
			text: 'Современные фары Audi собраны на жёстком герметике. Вскрытие только с контролем температуры. Замена линз или LED-модулей требует аккуратной пайки и калибровки светотеневой границы.',
		},
	],
	mercedes: [
		{
			title: 'Multibeam и ILS',
			text: 'На Mercedes (W213, W222, X253) сложные Multibeam/ILS-блоки. Проблемы часто в платах управления и датчиках, а не в «сгоревшей лампе». Нужны диагностика и микроэлектронный ремонт.',
		},
		{
			title: 'Герметизация и «стекло»',
			text: 'Стёкла мутнеют и царапаются. Грубая полировка убивает заводской УФ-лак. Восстанавливаем прозрачность со снятием старого покрытия и нанесением нового защитного слоя.',
		},
		{
			title: 'Разгерметизация и дренаж',
			text: 'Запотевание обычно связано с мембранами и вентиляцией корпуса. Просто «промазать шов» без замены дренажа усугубляет конденсат и повреждает электронику.',
		},
		{
			title: 'Неразборный корпус',
			text: 'Фары на современном герметике требуют точного нагрева при разборе. Любительское вскрытие даёт трещины. Линзы и LED-модули меняем с ювелирной пайкой и проверкой на стенде.',
		},
	],
	'land-rover': [
		{
			title: 'LED-секции и ДХО',
			text: 'У Range Rover / Evoque часто выгорают полосы ДХО и модули ближнего. Нужна точечная замена секций и драйверов, а не вся фара целиком.',
		},
		{
			title: 'Герметизация и стёкла',
			text: 'Поликарбонат мутнеет и царапается. Правильный ремонт — снятие старого лака, шлифовка и новый УФ-защитный слой, иначе через сезон фара снова «мутная».',
		},
		{
			title: 'Запотевание и мембраны',
			text: 'Корпуса Land Rover чувствительны к забитым мембранам. Герметизация без дренажа превращает фару в «аквариум» и убивает электронику.',
		},
		{
			title: 'Разбор и линзы',
			text: 'Вскрытие по штатному герметику требует нагрева. Меняем линзы и LED-модули с сохранением геометрии корпуса и герметичности.',
		},
	],
	porsche: [
		{
			title: 'Матричные и PDLS-системы',
			text: 'На Porsche фары тесно связаны с блоками PDLS/матричного света. Ошибки часто в драйверах и шине, а не в самом диоде. Нужна диагностика и точечный ремонт плат.',
		},
		{
			title: 'Герметизация и «стекло»',
			text: 'Поликарбонат мутнеет быстро на трассе. Грубая полировка снимает УФ-лак навсегда. Делаем полное восстановление покрытия, а не «глянец на неделю».',
		},
		{
			title: 'Разгерметизация и дренаж',
			text: 'Запотевание почти всегда связано с мембранами. Без их замены любая герметизация шва даёт обратный эффект.',
		},
		{
			title: 'Неразборный корпус и линзы',
			text: 'Корпуса на жёстком герметике. Вскрытие только с контролем температуры. Замена линз — аккуратная работа с сохранением штатной оптики пучка.',
		},
	],
};

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

function buildDefaultFeatures(title) {
	const name = String(title ?? '').trim() || 'марки';

	return [
		{
			title: 'Герметизация и «стекло»',
			text: `Фары ${name} из поликарбоната мутнеют и царапаются. Заводской лак твёрдый, но тонкий: грубая полировка стирает УФ-защиту. Правильный ремонт — снятие старого лака, шлифовка и новый двухкомпонентный защитный слой.`,
		},
		{
			title: 'Электроника и датчики',
			text: `На ${name} светодиодные и адаптивные фары связаны с блоками управления. Часто проблема в драйверах, шлейфах и датчиках, а не в «сгоревшей лампе». Нужны диагностика и микроэлектронный ремонт.`,
		},
		{
			title: 'Разгерметизация и дренаж',
			text: `Запотевание фар ${name} чаще вызвано забитыми мембранами и вентиляцией, а не только трещиной. Герметизация шва без замены дренажа превращает корпус в «аквариум».`,
		},
		{
			title: 'Неразборный корпус и линзы',
			text: `Современные фары ${name} собраны на жёстком герметике. Вскрытие требует точного нагрева. Замена линз и LED-модулей — аккуратная пайка с сохранением герметичности и геометрии пучка.`,
		},
	];
}

function encodeFeatures(features) {
	return features
		.map((item) => `${item.title}::${item.text}`)
		.join('|');
}

function buildBrandContent(item) {
	const title = String(item.title ?? '').trim();
	const slug = String(item.slug ?? '').trim().toLowerCase();

	const heroTitle =
		readSeoOverride(item, 'hero_title', 'hero_title') ||
		(title ? `Ремонт фар ${title}` : '');

	const featuresTitle =
		readSeoOverride(item, 'features_title', 'features_title') ||
		(title ? `Особенности ремонта фар ${title}` : '');

	let featuresEncoded = String(item.features ?? '').trim();
	if (!featuresEncoded) {
		const featureList = BRAND_FEATURE_OVERRIDES[slug] || buildDefaultFeatures(title);
		featuresEncoded = encodeFeatures(featureList);
	}

	return {
		hero_title: heroTitle,
		features_title: featuresTitle,
		features: featuresEncoded,
	};
}

function enrichBrand(brand) {
	const seo = buildSeoFields(brand);
	const content = buildBrandContent(brand);

	return {
		...brand,
		...content,
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
	const content = buildBrandContent(item);

	return [
		item.title ?? '',
		item.slug ?? '',
		item.image ?? '',
		content.hero_title,
		content.features_title,
		content.features,
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

		if (!brand.hero_title) {
			throw new Error(`Missing hero_title for "${brand.slug}"`);
		}
		if (!brand.features_title) {
			throw new Error(`Missing features_title for "${brand.slug}"`);
		}
		if (!brand.features || !brand.features.includes('::')) {
			throw new Error(`Missing features for "${brand.slug}"`);
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
			'title → post_title, slug → post_name (URL /marki/{slug}/), image → Featured Image (изображение марки). hero_title / features_title → ACF. features — строки title::text через |; в WPAI НЕ мапить subfields PHP-функцией. Парсинг: тема inc/wp-all-import.php (pmxi_saved_post) или meta ksenon_raw_features. SEO-колонки → Rank Math / Yoast. Уникальный идентификатор — slug.',
		image_source: 'Список марок и image URL с /marki/ staging/production → Featured Image',
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
	const missingFeatures = enriched.filter((brand) => !brand.features).map((brand) => brand.slug);

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
	console.log(`Features: ${enriched.length - missingFeatures.length}/${enriched.length}`);

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
