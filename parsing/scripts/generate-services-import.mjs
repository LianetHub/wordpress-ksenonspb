import { writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import XLSX from 'xlsx';

import { DATA_DIR } from './utils.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SOURCE_XLSX = path.join(DATA_DIR, 'services-import.xlsx');
const OUTPUT_JSON = path.join(DATA_DIR, 'services-import.json');
const OUTPUT_XLSX = SOURCE_XLSX;
const OUTPUT_CSV = path.join(DATA_DIR, 'services-import.csv');

const BRAND = 'КБ АВТО';
const CITY = 'СПб';
const CITY_LONG = 'Санкт-Петербург';

/**
 * Канонический список из 42 услуг (раздел 6.3 ТЗ).
 * Ключ — заголовок услуги, значение — целевой URL на сайте.
 */
const STRUKTURA_URL_BY_TITLE = {
	'Замена линз в фарах': '/tyuning-far/ustanovka-linz-xenon-biled/',
	'Установка лазерных (Laser) линз': '/tyuning-far/lazernye-linzy/',
	'Полировка и шлифовка фар': '/remont-far/optika-i-korpus/polirovka-shlifovka/',
	'Ремонт фар': '/remont-far/optika-i-korpus/remont-posle-povrezhdeniy/',
	'Замена стёкол фар': '/remont-far/optika-i-korpus/zamena-stekol/',
	'Устранение запотевания фар': '/remont-far/optika-i-korpus/ustranenie-zapotevaniya/',
	'Установка Bi-LED (светодиодных) линз': '/tyuning-far/ustanovka-biled/',
	'Установка ксенона': '/tyuning-far/ustanovka-ksenona/',
	'Замена штатных линз на оригинал или аналог': '/tyuning-far/zamena-shtatnyh-linz/',
	'Тюнинг подсветки — Ангельские и Дьявольские глазки': '/tyuning-far/angelskie-dyavolskie-glazki/',
	'Покраска масок фар': '/remont-far/optika-i-korpus/pokraska-masok/',
	'Изготовление дневных ходовых огней (ДХО)': '/tyuning-far/izgotovlenie-dho/',
	'Регулировка света фар на оптическом стенде': '/regulirovka-diagnostika/regulirovka-sveta/',
	'Замена штатных блоков розжига и ламп': '/remont-far/elektrika/zamena-blokov-rozzhiga/',
	'Ремонт штатных ДХО': '/remont-far/elektrika/remont-dho/',
	'Установка динамических («бегущих») поворотников': '/tyuning-far/begushchie-povorotniki/',
	'Ремонт штатных светодиодных фар (LED-ремонт)': '/remont-far/elektrika/remont-led-far/',
	'Ремонт штатных светодиодных фонарей': '/remont-far/elektrika/remont-zadnih-fonarey/',
	'Установка и программная активация ПТФ': '/ptf/ustanovka-aktivaciya/',
	'Установка светодиодных противотуманных фар': '/ptf/ustanovka-led-ptf/',
	'Замена стёкол противотуманных фар': '/ptf/zamena-stekol/',
	'Замена ламп в ПТФ': '/ptf/zamena-lamp/',
	'Замена ламп на светодиодные — салон, габариты, подсветка номера':
		'/dop-uslugi/zamena-lamp-na-led/',
	'Ремонт корректора фар — ручные и автоматические корректоры':
		'/remont-far/mehanika/remont-korrektora/',
	'Ремонт омывателя фар': '/remont-far/mehanika/remont-omyvatelya/',
	'Ремонт отражателей фар — чистка или замена рефлекторов':
		'/remont-far/optika-i-korpus/remont-otrazhateley/',
	'Ремонт корпуса фар': '/remont-far/optika-i-korpus/remont-korpusa/',
	'Восстановление креплений («ушек») фары': '/remont-far/optika-i-korpus/vosstanovlenie-krepleniy/',
	'Ремонт ксеноновых фар': '/remont-far/mehanika/remont-ksenonovyh-far/',
	'Комплексное улучшение света фар': '/tyuning-far/kompleksnoe-uluchshenie-sveta/',
	'Ремонт фар европейских производителей': '/remont-far/remont-evropejskih-proizvoditelej/',
	'Ремонт фар китайских производителей': '/remont-far/remont-kitajskih-proizvoditelej/',
	'Ремонт фар американского рынка': '/remont-far/remont-amerikanskogo-rynka/',
	'Ремонт систем адаптивного освещения (AFS)': '/regulirovka-diagnostika/remont-afs/',
	'Восстановление отражателей методом вакуумного напыления':
		'/remont-far/optika-i-korpus/vakuumnoe-napylenie/',
	'Ремонт и перепайка драйверов (плат управления) LED': '/remont-far/elektrika/remont-drayverov/',
	'Ремонт контроллеров управления фарой (ЭБУ)': '/remont-far/elektrika/remont-kontrollerov/',
	'Химическая чистка фары изнутри': '/remont-far/optika-i-korpus/himchistka/',
	'Программное отключение опроса ламп («обманки»)': '/remont-far/elektrika/obmanki/',
	'Замена внутренней проводки фары': '/remont-far/elektrika/zamena-provodki/',
	'Установка автокорректоров фар': '/tyuning-far/avtokorrektory/',
	'Кодирование и программирование штатных блоков управления светом + компьютерная диагностика':
		'/regulirovka-diagnostika/kodirovanie-programmirovanie/',
};

/** Маппинг старых заголовков (§2.1–2.5) → канонический список §6.3 */
const LEGACY_TITLE_MAP = {
	'Установка линз (Xenon/Bi-LED)': 'Замена линз в фарах',
	'Установка ксенона (штатные комплекты)': 'Установка ксенона',
	'Установка Bi-LED линз': 'Установка Bi-LED (светодиодных) линз',
	'Замена штатных линз на оригинал/аналог': 'Замена штатных линз на оригинал или аналог',
	'Тюнинг подсветки (Ангельские/Дьявольские глазки)':
		'Тюнинг подсветки — Ангельские и Дьявольские глазки',
	'Изготовление ДХО': 'Изготовление дневных ходовых огней (ДХО)',
	'Динамические («бегущие») поворотники': 'Установка динамических («бегущих») поворотников',
	'Комплексное улучшение света': 'Комплексное улучшение света фар',
	'Ремонт фар (комплексный)': 'Ремонт фар',
	'Восстановление креплений («ушек»)': 'Восстановление креплений («ушек») фары',
	'Химчистка фары изнутри': 'Химическая чистка фары изнутри',
	'Ремонт отражателей / вакуумное напыление': 'Ремонт отражателей фар — чистка или замена рефлекторов',
	'Замена блоков розжига и ламп': 'Замена штатных блоков розжига и ламп',
	'Ремонт светодиодных фар (LED)': 'Ремонт штатных светодиодных фар (LED-ремонт)',
	'Ремонт светодиодных фонарей (задняя оптика)': 'Ремонт штатных светодиодных фонарей',
	'Ремонт/перепайка драйверов (плат) LED': 'Ремонт и перепайка драйверов (плат управления) LED',
	'Ремонт корректора фар (ручной/авто)': 'Ремонт корректора фар — ручные и автоматические корректоры',
	'Ремонт ксеноновых фар (спец. узлы)': 'Ремонт ксеноновых фар',
	'Регулировка света на стенде': 'Регулировка света фар на оптическом стенде',
	'Кодирование/программирование блоков + диагностика':
		'Кодирование и программирование штатных блоков управления светом + компьютерная диагностика',
	'Установка и активация ПТФ (с прошивкой ЭБУ)': 'Установка и программная активация ПТФ',
	'Установка светодиодных ПТФ': 'Установка светодиодных противотуманных фар',
	'Замена стёкол ПТФ': 'Замена стёкол противотуманных фар',
	'Замена ламп на светодиодные (салон, габариты, подсветка номера)':
		'Замена ламп на светодиодные — салон, габариты, подсветка номера',
};

/** Контент для новых позиций, которых не было в старом импорте */
const NEW_SERVICE_DEFAULTS = {
	'Ремонт фар европейских производителей': {
		price: 3500,
		desc: 'Ремонт и восстановление фар европейских брендов — Valeo, Hella, Bosch, Magneti Marelli и аналоги.',
		benefits: 'Опыт с европейской оптикой',
	},
	'Ремонт фар китайских производителей': {
		price: 2500,
		desc: 'Восстанавливаем фары китайских марок — часто неразборные LED-модули и нестандартная геометрия.',
		benefits: 'Неразборные модули',
	},
	'Ремонт фар американского рынка': {
		price: 3500,
		desc: 'Ремонт оптики американского рынка — отличия в креплениях, проводке и светораспределении.',
		benefits: 'Специфика USDM',
	},
	'Восстановление отражателей методом вакуумного напыления': {
		price: 6000,
		desc: 'Восстанавливаем выгоревшие отражатели методом вакуумного напыления — заводское качество отражения.',
		benefits: 'Как с завода',
	},
	'Ремонт отражателей фар — чистка или замена рефлекторов': {
		price: 4500,
		desc: 'Чистим, заменяем или восстанавливаем отражатели при выгорании и помутнении.',
		benefits: 'Восстановление света',
	},
};

const SERVICE_HEADERS = ['title', 'slug', 'price', 'desc', 'benefits', 'category'];

const SEO_HEADERS = [
	'Focus Keyword',
	'SEO Title',
	'Meta Description',
	'Meta Keywords',
	'Facebook Title',
	'Twitter Title',
];

const HEADERS = [...SERVICE_HEADERS, ...SEO_HEADERS];

/**
 * Название категории по URL услуги — struktura-sayta-remont-far.md §2.1–2.5.
 * Порядок важен: сначала вложенные пути remont-far.
 */
const CATEGORY_BY_URL_PREFIX = [
	['/remont-far/optika-i-korpus/', 'Оптика и корпус'],
	['/remont-far/elektrika/', 'Электрика и электроника'],
	['/remont-far/mehanika/', 'Механика'],
	['/remont-far/', 'Ремонт фар'],
	['/tyuning-far/', 'Тюнинг фар'],
	['/regulirovka-diagnostika/', 'Регулировка и диагностика'],
	['/ptf/', 'Противотуманные фары (ПТФ)'],
	['/dop-uslugi/', 'Доп. услуги'],
];

function categoryFromUrl(url) {
	const normalized = String(url ?? '').trim();
	for (const [prefix, name] of CATEGORY_BY_URL_PREFIX) {
		if (normalized.startsWith(prefix)) {
			return name;
		}
	}
	return '';
}

function slugFromUrl(url) {
	return url.replace(/^\/+|\/+$/g, '').split('/').pop() ?? '';
}

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

function normalizeService(raw) {
	const title = String(raw.title ?? '').trim();
	const slug = String(
		raw.slug ?? raw.url_slug ?? slugFromUrl(STRUKTURA_URL_BY_TITLE[title] ?? '') ?? '',
	).trim();

	return {
		title,
		slug,
		price: raw.price ?? '',
		desc: String(raw.desc ?? '').trim(),
		benefits: String(raw.benefits ?? '').trim(),
		category: String(raw.category ?? '').trim(),
		focus_keyword: readSeoOverride(raw, 'focus_keyword', 'Focus Keyword'),
		seo_title: readSeoOverride(raw, 'seo_title', 'SEO Title'),
		meta_description: readSeoOverride(raw, 'meta_description', 'Meta Description'),
		meta_keywords: readSeoOverride(raw, 'meta_keywords', 'Meta Keywords'),
		facebook_title: readSeoOverride(raw, 'facebook_title', 'Facebook Title'),
		twitter_title: readSeoOverride(raw, 'twitter_title', 'Twitter Title'),
	};
}

function enrichService(service) {
	const referenceUrl = STRUKTURA_URL_BY_TITLE[service.title] ?? '';
	const seo = buildSeoFields(service);

	return {
		...service,
		reference_url: referenceUrl,
		category: service.category || categoryFromUrl(referenceUrl),
		focus_keyword: service.focus_keyword || seo['Focus Keyword'],
		seo_title: service.seo_title || seo['SEO Title'],
		meta_description: service.meta_description || seo['Meta Description'],
		meta_keywords: service.meta_keywords || seo['Meta Keywords'],
		facebook_title: service.facebook_title || seo['Facebook Title'],
		twitter_title: service.twitter_title || seo['Twitter Title'],
	};
}

function serviceToRow(item) {
	const seo = buildSeoFields(item);

	return [
		item.title ?? '',
		item.slug ?? '',
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

export function buildServiceUrl(service) {
	if (service.reference_url) {
		return service.reference_url;
	}
	if (service.slug) {
		return `/${service.slug}/`;
	}
	return '/';
}

function readServicesFromXlsx() {
	const workbook = XLSX.readFile(SOURCE_XLSX);
	if (!workbook.SheetNames.includes('services')) {
		throw new Error('Sheet "services" not found in services-import.xlsx');
	}

	const rows = XLSX.utils.sheet_to_json(workbook.Sheets.services);
	return rows.map(normalizeService);
}

function validateServices(services) {
	if (!services.length) {
		throw new Error('No services found in services-import.xlsx');
	}

	const titles = new Set();
	const strukturaTitles = new Set(Object.keys(STRUKTURA_URL_BY_TITLE));

	for (const service of services) {
		if (!service.title) {
			throw new Error('Service row is missing title');
		}
		if (!service.slug) {
			throw new Error(`Missing slug for service "${service.title}"`);
		}
		if (titles.has(service.title)) {
			throw new Error(`Duplicate title "${service.title}"`);
		}
		titles.add(service.title);

		const expectedUrl = STRUKTURA_URL_BY_TITLE[service.title];
		if (!expectedUrl) {
			throw new Error(`Service "${service.title}" is not in canonical list (§6.3)`);
		}

		const expectedSlug = slugFromUrl(expectedUrl);
		if (service.slug !== expectedSlug) {
			throw new Error(
				`Service "${service.title}": slug must be "${expectedSlug}", got "${service.slug}"`,
			);
		}

		const expectedCategory = categoryFromUrl(expectedUrl);
		if (service.category !== expectedCategory) {
			throw new Error(
				`Service "${service.title}": category must be "${expectedCategory}", got "${service.category}"`,
			);
		}
	}

	const missing = [...strukturaTitles].filter((title) => !titles.has(title));
	if (missing.length) {
		throw new Error(`Missing services from canonical list: ${missing.join('; ')}`);
	}

	if (services.length !== strukturaTitles.size) {
		throw new Error(`Expected ${strukturaTitles.size} services, got ${services.length}`);
	}
}

function readLegacyContentMap() {
	const workbook = XLSX.readFile(SOURCE_XLSX);
	if (!workbook.SheetNames.includes('services')) {
		return new Map();
	}

	const rows = XLSX.utils.sheet_to_json(workbook.Sheets.services);
	const contentByCanonical = new Map();

	for (const row of rows) {
		const legacyTitle = String(row.title ?? '').trim();
		const canonicalTitle = LEGACY_TITLE_MAP[legacyTitle] ?? legacyTitle;
		if (!STRUKTURA_URL_BY_TITLE[canonicalTitle]) {
			continue;
		}

		contentByCanonical.set(canonicalTitle, {
			price: row.price ?? '',
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

	return contentByCanonical;
}

function buildCanonicalServices() {
	const contentByCanonical = readLegacyContentMap();

	return Object.entries(STRUKTURA_URL_BY_TITLE).map(([title, url]) => {
		const legacy = contentByCanonical.get(title) ?? {};
		const defaults = NEW_SERVICE_DEFAULTS[title] ?? {};

		return normalizeService({
			title,
			slug: slugFromUrl(url),
			price: legacy.price ?? defaults.price ?? '',
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

function rebuildSourceXlsx() {
	const services = buildCanonicalServices();
	const serviceRows = [HEADERS, ...services.map((service) => serviceToRow(enrichService(service)))];

	const workbook = XLSX.utils.book_new();
	XLSX.utils.book_append_sheet(workbook, XLSX.utils.aoa_to_sheet(serviceRows), 'services');
	XLSX.writeFile(workbook, OUTPUT_XLSX);

	return services.length;
}

async function main() {
	const shouldRebuild = process.argv.includes('--rebuild-xlsx');
	if (shouldRebuild) {
		const count = rebuildSourceXlsx();
		console.log(`Rebuilt ${OUTPUT_XLSX} with ${count} canonical services`);
	}

	const services = readServicesFromXlsx().map((service) => enrichService(service));
	validateServices(services);

	const importJson = {
		generated_for: 'WP All Import — CPT service',
		source: path.basename(SOURCE_XLSX),
		note: 'Колонка category — название категории service_category для привязки в админке / WP All Import. slug → кастомное поле service_slug. Уникальный идентификатор записи — title.',
		columns: HEADERS,
		services,
	};

	await writeFile(OUTPUT_JSON, `${JSON.stringify(importJson, null, 2)}\n`, 'utf8');

	const serviceRows = [HEADERS, ...services.map((service) => serviceToRow(enrichService(service)))];

	const workbook = XLSX.utils.book_new();
	XLSX.utils.book_append_sheet(workbook, XLSX.utils.aoa_to_sheet(serviceRows), 'services');
	XLSX.writeFile(workbook, OUTPUT_XLSX);

	const csvLines = serviceRows.map((row) => row.map(escapeCsvCell).join(';'));
	const csvContent = `\uFEFF${csvLines.join('\r\n')}`;
	await writeFile(OUTPUT_CSV, csvContent, 'utf8');

	console.log(`Source: ${SOURCE_XLSX}`);
	console.log(`Generated ${OUTPUT_JSON}`);
	console.log(`Generated ${OUTPUT_XLSX} (sheet: services)`);
	console.log(`Generated ${OUTPUT_CSV}`);
	console.log(`Services: ${services.length}`);
	console.log('Sample URLs:');
	for (const service of services.slice(0, 3)) {
		console.log(`  ${buildServiceUrl(service)} (slug: ${service.slug})`);
	}
	const nested = services.find((service) => service.title === 'Полировка и шлифовка фар');
	if (nested) {
		console.log(`  ${buildServiceUrl(nested)} (slug: ${nested.slug})`);
	}
}

const isDirectRun = process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url);

if (isDirectRun) {
	main().catch((error) => {
		console.error(error);
		process.exit(1);
	});
}
