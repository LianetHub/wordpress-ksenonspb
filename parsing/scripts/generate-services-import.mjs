import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import XLSX from 'xlsx';

import { DATA_DIR } from './utils.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SOURCE_XLSX = path.join(DATA_DIR, 'services-import.xlsx');
const OUTPUT_JSON = path.join(DATA_DIR, 'services-import.json');
const OUTPUT_XLSX = SOURCE_XLSX;
const OUTPUT_CSV = path.join(DATA_DIR, 'services-import.csv');
const SERVICE_IMAGES_MAP = path.join(DATA_DIR, 'service-images-map.json');

const BRAND = 'КБ АВТО';
const CITY = 'СПб';
const CITY_LONG = 'Санкт-Петербург';

const WARRANTY_TEXT =
	'Гарантия распространяется на установленные компоненты и герметизацию. Предоплата не требуется. Все работы фиксируются в договоре — вы забираете автомобиль с письменной гарантией.';

const FAQ_TITLE = 'Частые вопросы';

const BRAND_POOLS = {
	popular: [
		'BMW',
		'MERCEDES',
		'AUDI',
		'TOYOTA',
		'LEXUS',
		'Volkswagen',
		'KIA',
		'Hyundai',
		'NISSAN',
		'FORD',
		'HONDA',
		'MAZDA',
		'Skoda',
		'Land Rover',
	],
	european: [
		'BMW',
		'MERCEDES',
		'AUDI',
		'Volkswagen',
		'PORSCHE',
		'VOLVO',
		'Land Rover',
		'JAGUAR',
		'MINI',
		'Skoda',
		'OPEL',
		'PEUGEOT',
		'RENAULT',
	],
	asian: [
		'TOYOTA',
		'LEXUS',
		'NISSAN',
		'HONDA',
		'MAZDA',
		'MITSUBISHI',
		'SUBARU',
		'SUZUKI',
		'KIA',
		'Hyundai',
		'INFINITI',
		'SsangYong',
	],
	american: [
		'FORD',
		'CHEVROLET',
		'CADILLAC',
		'JEEP',
		'DODGE',
		'CHRYSLER',
		'HUMMER',
		'ACURA',
	],
	premium: [
		'BMW',
		'MERCEDES',
		'AUDI',
		'LEXUS',
		'PORSCHE',
		'Land Rover',
		'JAGUAR',
		'INFINITI',
		'Maserati',
		'BENTLEY',
	],
};

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

/** Профиль прайса / марок по названию услуги */
const SERVICE_PROFILE_BY_TITLE = {
	'Замена линз в фарах': 'lens',
	'Установка лазерных (Laser) линз': 'lens_premium',
	'Установка Bi-LED (светодиодных) линз': 'lens',
	'Установка ксенона': 'xenon',
	'Замена штатных линз на оригинал или аналог': 'lens',
	'Комплексное улучшение света фар': 'lens',
	'Тюнинг подсветки — Ангельские и Дьявольские глазки': 'tuning_light',
	'Изготовление дневных ходовых огней (ДХО)': 'tuning_light',
	'Установка динамических («бегущих») поворотников': 'tuning_light',
	'Установка автокорректоров фар': 'autocorrector',
	'Полировка и шлифовка фар': 'polish',
	'Покраска масок фар': 'paint',
	'Замена стёкол фар': 'glass',
	'Устранение запотевания фар': 'fogging',
	'Ремонт отражателей фар — чистка или замена рефлекторов': 'reflector',
	'Восстановление отражателей методом вакуумного напыления': 'vacuum',
	'Ремонт корпуса фар': 'body',
	'Восстановление креплений («ушек») фары': 'body',
	'Химическая чистка фары изнутри': 'chem_clean',
	'Ремонт фар': 'repair_general',
	'Ремонт фар европейских производителей': 'repair_eu',
	'Ремонт фар китайских производителей': 'repair_cn',
	'Ремонт фар американского рынка': 'repair_us',
	'Замена штатных блоков розжига и ламп': 'electrical',
	'Ремонт штатных ДХО': 'electrical',
	'Ремонт штатных светодиодных фар (LED-ремонт)': 'led_repair',
	'Ремонт штатных светодиодных фонарей': 'led_repair',
	'Ремонт и перепайка драйверов (плат управления) LED': 'pcb',
	'Ремонт контроллеров управления фарой (ЭБУ)': 'pcb',
	'Программное отключение опроса ламп («обманки»)': 'coding',
	'Замена внутренней проводки фары': 'electrical',
	'Ремонт корректора фар — ручные и автоматические корректоры': 'mechanics',
	'Ремонт омывателя фар': 'mechanics',
	'Ремонт ксеноновых фар': 'xenon_repair',
	'Регулировка света фар на оптическом стенде': 'adjust',
	'Ремонт систем адаптивного освещения (AFS)': 'afs',
	'Кодирование и программирование штатных блоков управления светом + компьютерная диагностика':
		'coding',
	'Установка и программная активация ПТФ': 'ptf_install',
	'Установка светодиодных противотуманных фар': 'ptf_install',
	'Замена стёкол противотуманных фар': 'ptf_glass',
	'Замена ламп в ПТФ': 'ptf_lamp',
	'Замена ламп на светодиодные — салон, габариты, подсветка номера': 'interior_led',
};

const SERVICE_HEADERS = [
	'title',
	'slug',
	'price',
	'desc',
	'benefits',
	'category',
	'image',
	'price_main',
	'price_extra',
	'price_diagnostics',
	'warranty_text',
	'related_brands',
	'faq_title',
	'faq',
];

const SEO_HEADERS = [
	'Focus Keyword',
	'SEO Title',
	'Meta Description',
	'Meta Keywords',
	'Facebook Title',
	'Twitter Title',
];

const HEADERS = [...SERVICE_HEADERS, ...SEO_HEADERS];

const IMPORT_NOTE =
	'title → post_title (уникальный ID). slug → meta service_slug. price → ACF price_from. desc → card_excerpt. benefits → card_labels. category → taxonomy service_category (по названию). image → Featured Image + ACF card_image (URL со старого сайта; карта в service-images-map.json). price_main / price_extra / price_diagnostics / faq — строки name::price::duration::warranty (faq: question::answer) через |; в WPAI НЕ мапить subfields PHP-функцией (Variable Repeater ломается). Парсинг: тема inc/wp-all-import.php (pmxi_saved_post). Запасной путь: custom fields ksenon_raw_price_main / ksenon_raw_price_extra / ksenon_raw_price_diagnostics / ksenon_raw_faq = {column[1]} целиком. warranty_text → ACF. related_brands → relationship по title через |. faq_title → ACF. Портфолио — только portfolio.related_services.';


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

function priceFromLabel(price) {
	const formatted = formatPrice(price);
	return formatted ? `от ${formatted} ₽` : 'по согласованию';
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

function encodePriceRows(rows) {
	return rows
		.map((row) =>
			[row.name, row.price, row.duration, row.warranty || '-'].join('::'),
		)
		.join('|');
}

function encodeFaq(items) {
	return items.map(([question, answer]) => `${question}::${answer}`).join('|');
}

function brandsForProfile(profile) {
	switch (profile) {
		case 'repair_eu':
			return BRAND_POOLS.european;
		case 'repair_cn':
			return BRAND_POOLS.asian;
		case 'repair_us':
			return BRAND_POOLS.american;
		case 'lens_premium':
		case 'vacuum':
		case 'afs':
			return BRAND_POOLS.premium;
		default:
			return BRAND_POOLS.popular;
	}
}

function extraOpticsRows() {
	return [
		{
			name: 'Полировка стекла изнутри',
			price: 'от 1 500 ₽',
			duration: '+1 ч',
			warranty: '1 год',
		},
		{
			name: 'Замена уплотнителя корпуса',
			price: 'от 800 ₽',
			duration: '+30 мин',
			warranty: '-',
		},
		{
			name: 'Покраска масок и декоративных элементов',
			price: 'от 2 000 ₽',
			duration: '+1 день',
			warranty: '-',
		},
	];
}

function diagnosticsRow(free = true) {
	return [
		{
			name: 'Осмотр и заключение по фаре',
			price: free ? 'Бесплатно' : 'от 500 ₽',
			duration: '30 мин',
			warranty: '-',
		},
	];
}

function buildPriceTables(title, price, profile) {
	const from = priceFromLabel(price);
	const demount = {
		name: 'Демонтаж фары и разборка корпуса',
		price: 'Входит в стоимость',
		duration: '1-2 ч',
		warranty: '-',
	};
	const seal = {
		name: 'Герметизация и сборка',
		price: 'входит в стоимость',
		duration: '1 ч',
		warranty: '1 год',
	};
	const calibrate = {
		name: 'Калибровка светового пучка',
		price: 'входит в стоимость',
		duration: '30 мин',
		warranty: '-',
	};

	const extras = extraOpticsRows();
	const diagnostics = diagnosticsRow(true);

	/** @type {{ main: object[], extra: object[], diagnostics: object[] }} */
	let tables;

	switch (profile) {
		case 'lens':
		case 'lens_premium':
			tables = {
				main: [
					demount,
					{
						name:
							profile === 'lens_premium'
								? 'Установка лазерных модулей'
								: 'Замена линз Bi-LED / Xenon',
						price: `${from} / шт.`,
						duration: profile === 'lens_premium' ? '3-5 ч' : '2-3 ч',
						warranty: '2 года',
					},
					calibrate,
					seal,
				],
				extra: extras,
				diagnostics,
			};
			break;
		case 'xenon':
			tables = {
				main: [
					demount,
					{
						name: 'Установка ксенонового комплекта',
						price: from,
						duration: '2-4 ч',
						warranty: '1 год',
					},
					calibrate,
					seal,
				],
				extra: extras,
				diagnostics,
			};
			break;
		case 'polish':
			tables = {
				main: [
					{
						name: 'Шлифовка и полировка стекла',
						price: from,
						duration: '1-2 ч',
						warranty: '6 мес.',
					},
					{
						name: 'Защитное покрытие',
						price: 'входит в стоимость',
						duration: '30 мин',
						warranty: '-',
					},
				],
				extra: [
					{
						name: 'Полировка изнутри (при разборке)',
						price: 'от 1 500 ₽',
						duration: '+1 ч',
						warranty: '1 год',
					},
					{
						name: 'Устранение глубоких царапин',
						price: 'от 1 000 ₽',
						duration: '+1 ч',
						warranty: '-',
					},
				],
				diagnostics,
			};
			break;
		case 'paint':
			tables = {
				main: [
					demount,
					{
						name: 'Покраска масок фар',
						price: from,
						duration: '1 день',
						warranty: '1 год',
					},
					seal,
				],
				extra: [
					{
						name: 'Покраска декоративных элементов',
						price: 'от 1 500 ₽',
						duration: '+4 ч',
						warranty: '-',
					},
					...extras.slice(0, 2),
				],
				diagnostics,
			};
			break;
		case 'glass':
			tables = {
				main: [
					demount,
					{
						name: 'Замена стекла фары',
						price: from,
						duration: '2-4 ч',
						warranty: '1 год',
					},
					seal,
				],
				extra: extras,
				diagnostics,
			};
			break;
		case 'fogging':
			tables = {
				main: [
					demount,
					{
						name: 'Устранение запотевания и просушка',
						price: from,
						duration: '2-3 ч',
						warranty: '1 год',
					},
					{
						name: 'Замена уплотнителя / клапанов',
						price: 'входит в стоимость',
						duration: '30 мин',
						warranty: '-',
					},
					seal,
				],
				extra: extras.slice(0, 2),
				diagnostics,
			};
			break;
		case 'reflector':
			tables = {
				main: [
					demount,
					{
						name: 'Чистка или замена отражателей',
						price: from,
						duration: '3-5 ч',
						warranty: '1 год',
					},
					seal,
				],
				extra: [
					{
						name: 'Вакуумное напыление отражателя',
						price: 'от 6 000 ₽',
						duration: '+1 день',
						warranty: '1 год',
					},
					...extras.slice(0, 2),
				],
				diagnostics,
			};
			break;
		case 'vacuum':
			tables = {
				main: [
					demount,
					{
						name: 'Вакуумное напыление отражателей',
						price: from,
						duration: '1-2 дня',
						warranty: '1 год',
					},
					seal,
				],
				extra: extras.slice(0, 2),
				diagnostics,
			};
			break;
		case 'body':
			tables = {
				main: [
					demount,
					{
						name: title.includes('креплений')
							? 'Восстановление креплений («ушек»)'
							: 'Ремонт корпуса фары',
						price: from,
						duration: '2-4 ч',
						warranty: '1 год',
					},
					seal,
				],
				extra: extras.slice(0, 2),
				diagnostics,
			};
			break;
		case 'chem_clean':
			tables = {
				main: [
					demount,
					{
						name: 'Химическая чистка изнутри',
						price: from,
						duration: '2-3 ч',
						warranty: '-',
					},
					seal,
				],
				extra: extras,
				diagnostics,
			};
			break;
		case 'repair_general':
		case 'repair_eu':
		case 'repair_cn':
		case 'repair_us':
			tables = {
				main: [
					{
						name: 'Диагностика и оценка объёма работ',
						price: 'Входит в стоимость',
						duration: '30 мин',
						warranty: '-',
					},
					{
						name: 'Ремонт / восстановление фары',
						price: from,
						duration: 'от 2 ч',
						warranty: '1 год',
					},
					seal,
				],
				extra: extras,
				diagnostics,
			};
			break;
		case 'electrical':
		case 'led_repair':
		case 'pcb':
		case 'xenon_repair':
			tables = {
				main: [
					demount,
					{
						name:
							profile === 'pcb'
								? 'Ремонт / перепайка платы'
								: profile === 'led_repair'
									? 'Ремонт светодиодных модулей'
									: 'Ремонт электрики фары',
						price: from,
						duration: '2-6 ч',
						warranty: '1 год',
					},
					seal,
				],
				extra: [
					{
						name: 'Замена блоков / модулей (при необходимости)',
						price: 'по прайсу запчастей',
						duration: '+1-2 ч',
						warranty: 'по запчасти',
					},
					...extras.slice(0, 2),
				],
				diagnostics,
			};
			break;
		case 'mechanics':
			tables = {
				main: [
					{
						name: 'Диагностика узла',
						price: 'Входит в стоимость',
						duration: '30 мин',
						warranty: '-',
					},
					{
						name: 'Ремонт механики',
						price: from,
						duration: '1-3 ч',
						warranty: '1 год',
					},
				],
				extra: [
					{
						name: 'Замена узла целиком',
						price: 'по согласованию',
						duration: '+1-2 ч',
						warranty: 'по запчасти',
					},
				],
				diagnostics,
			};
			break;
		case 'tuning_light':
			tables = {
				main: [
					demount,
					{
						name: 'Установка / изготовление подсветки',
						price: from,
						duration: '3-8 ч',
						warranty: '1 год',
					},
					seal,
				],
				extra: extras,
				diagnostics,
			};
			break;
		case 'autocorrector':
			tables = {
				main: [
					{
						name: 'Установка автокорректора',
						price: from,
						duration: '2-4 ч',
						warranty: '1 год',
					},
					calibrate,
				],
				extra: [
					{
						name: 'Адаптация / кодирование',
						price: 'от 2 000 ₽',
						duration: '+1 ч',
						warranty: '-',
					},
				],
				diagnostics,
			};
			break;
		case 'adjust':
			tables = {
				main: [
					{
						name: 'Регулировка на оптическом стенде',
						price: from,
						duration: '30-60 мин',
						warranty: '-',
					},
				],
				extra: [
					{
						name: 'Диагностика оптики перед регулировкой',
						price: 'Бесплатно',
						duration: '+15 мин',
						warranty: '-',
					},
				],
				diagnostics: [
					{
						name: 'Проверка светового пучка',
						price: 'Входит в стоимость',
						duration: '15 мин',
						warranty: '-',
					},
				],
			};
			break;
		case 'afs':
			tables = {
				main: [
					{
						name: 'Диагностика системы AFS',
						price: 'Входит в стоимость',
						duration: '30-60 мин',
						warranty: '-',
					},
					{
						name: 'Ремонт / адаптация AFS',
						price: from,
						duration: '2-6 ч',
						warranty: '1 год',
					},
				],
				extra: [
					{
						name: 'Замена датчиков / блоков',
						price: 'по согласованию',
						duration: '+2-4 ч',
						warranty: 'по запчасти',
					},
				],
				diagnostics,
			};
			break;
		case 'coding':
			tables = {
				main: [
					{
						name: 'Компьютерная диагностика',
						price: 'Входит в стоимость',
						duration: '30 мин',
						warranty: '-',
					},
					{
						name: 'Кодирование / программирование',
						price: from,
						duration: '1-2 ч',
						warranty: '-',
					},
				],
				extra: [
					{
						name: 'Установка «обманок» (при необходимости)',
						price: 'от 1 500 ₽',
						duration: '+30 мин',
						warranty: '1 год',
					},
				],
				diagnostics,
			};
			break;
		case 'ptf_install':
			tables = {
				main: [
					{
						name: 'Установка ПТФ',
						price: from,
						duration: '2-4 ч',
						warranty: '1 год',
					},
					{
						name: 'Программная активация (при необходимости)',
						price: 'входит в стоимость / от 2 000 ₽',
						duration: '+30-60 мин',
						warranty: '-',
					},
				],
				extra: [
					{
						name: 'Замена стекла / ламп ПТФ',
						price: 'от 800 ₽',
						duration: '+30 мин',
						warranty: '-',
					},
				],
				diagnostics,
			};
			break;
		case 'ptf_glass':
		case 'ptf_lamp':
			tables = {
				main: [
					{
						name:
							profile === 'ptf_lamp'
								? 'Замена ламп в ПТФ'
								: 'Замена стёкол ПТФ',
						price: from,
						duration: '30-90 мин',
						warranty: profile === 'ptf_lamp' ? '-' : '6 мес.',
					},
				],
				extra: [
					{
						name: 'Полировка стекла ПТФ',
						price: 'от 800 ₽',
						duration: '+30 мин',
						warranty: '-',
					},
				],
				diagnostics,
			};
			break;
		case 'interior_led':
			tables = {
				main: [
					{
						name: 'Замена ламп на светодиодные',
						price: from,
						duration: '30-90 мин',
						warranty: '1 год',
					},
				],
				extra: [
					{
						name: 'Установка «обманок» (при ошибках в бортовом)',
						price: 'от 500 ₽',
						duration: '+15 мин',
						warranty: '-',
					},
				],
				diagnostics,
			};
			break;
		default:
			tables = {
				main: [
					{
						name: title,
						price: from,
						duration: 'от 1 ч',
						warranty: '1 год',
					},
				],
				extra: extras.slice(0, 2),
				diagnostics,
			};
	}

	return {
		price_main: encodePriceRows(tables.main),
		price_extra: encodePriceRows(tables.extra),
		price_diagnostics: encodePriceRows(tables.diagnostics),
	};
}

function buildFaq(title, price, profile) {
	const keyword = focusKeywordFromTitle(title);
	const priceLabel = formatPrice(price);
	const priceHint = priceLabel ? ` Ориентир по цене — от ${priceLabel} ₽.` : '';

	const durationByProfile = {
		polish: 'Обычно 1–2 часа на пару фар.',
		adjust: 'Регулировка занимает 30–60 минут.',
		coding: 'Диагностика и кодирование — обычно 1–2 часа.',
		interior_led: 'Замена ламп занимает от 30 минут.',
		ptf_lamp: 'Работы занимают 30–90 минут.',
		ptf_glass: 'Замена стёкол ПТФ — обычно до 1,5 часов.',
		vacuum: 'Вакуумное напыление занимает 1–2 рабочих дня.',
		lens_premium: 'Установка лазерных модулей — обычно 3–5 часов.',
		lens: 'Базовые работы по линзам — обычно 2–4 часа на фару.',
	};

	const duration =
		durationByProfile[profile] ||
		'Срок зависит от объёма работ; точное время скажем после осмотра.';

	const needsDisassembly = ![
		'adjust',
		'coding',
		'interior_led',
		'ptf_lamp',
		'ptf_glass',
	].includes(profile);

	return encodeFaq([
		[
			`Сколько времени занимает ${keyword}?`,
			duration,
		],
		[
			'Какая гарантия на работы?',
			'На установленные компоненты и герметизацию даём письменную гарантию. Срок по позициям указан в таблице цен.',
		],
		[
			'Нужна ли предварительная запись?',
			'Да, работы выполняем по записи. Осмотр и заключение по фаре — бесплатно.',
		],
		[
			'От чего зависит итоговая цена?',
			`На стоимость влияют состояние оптики, марка и модель автомобиля, а также дополнительные работы (полировка изнутри, уплотнители и т.п.).${priceHint}`,
		],
		[
			'Нужна ли разборка фары?',
			needsDisassembly
				? 'В большинстве случаев да — для качественного результата фару снимаем и разбираем. Демонтаж обычно входит в стоимость основных работ.'
				: 'Разборка фары обычно не требуется. Если по результатам осмотра она понадобится — согласуем заранее.',
		],
	]);
}

function buildServiceExtras(service, imageByTitle = {}) {
	const title = service.title;
	const profile = SERVICE_PROFILE_BY_TITLE[title] || 'repair_general';
	const prices = buildPriceTables(title, service.price, profile);

	return {
		image: String(imageByTitle[title] ?? '').trim(),
		...prices,
		warranty_text: WARRANTY_TEXT,
		related_brands: brandsForProfile(profile).join('|'),
		faq_title: FAQ_TITLE,
		faq: buildFaq(title, service.price, profile),
	};
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
		image: String(raw.image ?? '').trim(),
		price_main: String(raw.price_main ?? '').trim(),
		price_extra: String(raw.price_extra ?? '').trim(),
		price_diagnostics: String(raw.price_diagnostics ?? '').trim(),
		warranty_text: String(raw.warranty_text ?? '').trim(),
		related_brands: String(raw.related_brands ?? '').trim(),
		faq_title: String(raw.faq_title ?? '').trim(),
		faq: String(raw.faq ?? '').trim(),
		focus_keyword: readSeoOverride(raw, 'focus_keyword', 'Focus Keyword'),
		seo_title: readSeoOverride(raw, 'seo_title', 'SEO Title'),
		meta_description: readSeoOverride(raw, 'meta_description', 'Meta Description'),
		meta_keywords: readSeoOverride(raw, 'meta_keywords', 'Meta Keywords'),
		facebook_title: readSeoOverride(raw, 'facebook_title', 'Facebook Title'),
		twitter_title: readSeoOverride(raw, 'twitter_title', 'Twitter Title'),
	};
}

function enrichService(service, imageByTitle = {}) {
	const referenceUrl = STRUKTURA_URL_BY_TITLE[service.title] ?? '';
	const seo = buildSeoFields(service);
	const generated = buildServiceExtras(service, imageByTitle);

	return {
		...service,
		reference_url: referenceUrl,
		category: service.category || categoryFromUrl(referenceUrl),
		// Карта фото всегда перезаписывает image (пустая карта = очистка).
		image: Object.prototype.hasOwnProperty.call(imageByTitle, service.title)
			? generated.image
			: service.image || generated.image,
		price_main: service.price_main || generated.price_main,
		price_extra: service.price_extra || generated.price_extra,
		price_diagnostics: service.price_diagnostics || generated.price_diagnostics,
		warranty_text: service.warranty_text || generated.warranty_text,
		related_brands: service.related_brands || generated.related_brands,
		faq_title: service.faq_title || generated.faq_title,
		faq: service.faq || generated.faq,
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
		item.image ?? '',
		item.price_main ?? '',
		item.price_extra ?? '',
		item.price_diagnostics ?? '',
		item.warranty_text ?? '',
		item.related_brands ?? '',
		item.faq_title ?? '',
		item.faq ?? '',
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

		if (!service.price_main) {
			throw new Error(`Service "${service.title}": missing price_main`);
		}
		if (!service.warranty_text) {
			throw new Error(`Service "${service.title}": missing warranty_text`);
		}
		if (!service.related_brands) {
			throw new Error(`Service "${service.title}": missing related_brands`);
		}
		if (!service.faq) {
			throw new Error(`Service "${service.title}": missing faq`);
		}
	}

	const missing = [...strukturaTitles].filter((title) => !titles.has(title));
	if (missing.length) {
		throw new Error(`Missing services from canonical list: ${missing.join('; ')}`);
	}

	if (services.length !== strukturaTitles.size) {
		throw new Error(`Expected ${strukturaTitles.size} services, got ${services.length}`);
	}

	const unprofiled = [...strukturaTitles].filter((title) => !SERVICE_PROFILE_BY_TITLE[title]);
	if (unprofiled.length) {
		throw new Error(`Missing SERVICE_PROFILE_BY_TITLE for: ${unprofiled.join('; ')}`);
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
			image: String(row.image ?? '').trim(),
			price_main: String(row.price_main ?? '').trim(),
			price_extra: String(row.price_extra ?? '').trim(),
			price_diagnostics: String(row.price_diagnostics ?? '').trim(),
			warranty_text: String(row.warranty_text ?? '').trim(),
			related_brands: String(row.related_brands ?? '').trim(),
			faq_title: String(row.faq_title ?? '').trim(),
			faq: String(row.faq ?? '').trim(),
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
			image: legacy.image ?? '',
			price_main: legacy.price_main ?? '',
			price_extra: legacy.price_extra ?? '',
			price_diagnostics: legacy.price_diagnostics ?? '',
			warranty_text: legacy.warranty_text ?? '',
			related_brands: legacy.related_brands ?? '',
			faq_title: legacy.faq_title ?? '',
			faq: legacy.faq ?? '',
			focus_keyword: legacy.focus_keyword ?? '',
			seo_title: legacy.seo_title ?? '',
			meta_description: legacy.meta_description ?? '',
			meta_keywords: legacy.meta_keywords ?? '',
			facebook_title: legacy.facebook_title ?? '',
			twitter_title: legacy.twitter_title ?? '',
		});
	});
}

async function loadServiceImagesMap() {
	try {
		const raw = JSON.parse(await readFile(SERVICE_IMAGES_MAP, 'utf8'));
		return raw.images && typeof raw.images === 'object' ? raw.images : {};
	} catch {
		return {};
	}
}

async function rebuildSourceXlsx(imageByTitle = {}) {
	const services = buildCanonicalServices();
	const serviceRows = [
		HEADERS,
		...services.map((service) => serviceToRow(enrichService(service, imageByTitle))),
	];

	const workbook = XLSX.utils.book_new();
	XLSX.utils.book_append_sheet(workbook, XLSX.utils.aoa_to_sheet(serviceRows), 'services');
	try {
		XLSX.writeFile(workbook, OUTPUT_XLSX);
	} catch (error) {
		if (error && error.code === 'EBUSY') {
			console.warn(`WARN: ${OUTPUT_XLSX} is locked (close Excel). JSON/CSV still written.`);
		} else {
			throw error;
		}
	}

	return services.length;
}

async function main() {
	const imageByTitle = await loadServiceImagesMap();
	const shouldRebuild = process.argv.includes('--rebuild-xlsx');
	if (shouldRebuild) {
		const count = await rebuildSourceXlsx(imageByTitle);
		console.log(`Rebuilt ${OUTPUT_XLSX} with ${count} canonical services`);
	}

	const services = readServicesFromXlsx().map((service) => enrichService(service, imageByTitle));
	validateServices(services);

	const withImages = services.filter((service) => service.image).length;

	const importJson = {
		generated_for: 'WP All Import — CPT service',
		source: path.basename(SOURCE_XLSX),
		note: IMPORT_NOTE,
		columns: HEADERS,
		services,
	};

	await writeFile(OUTPUT_JSON, `${JSON.stringify(importJson, null, 2)}\n`, 'utf8');

	const serviceRows = [
		HEADERS,
		...services.map((service) => serviceToRow(enrichService(service, imageByTitle))),
	];

	const workbook = XLSX.utils.book_new();
	XLSX.utils.book_append_sheet(workbook, XLSX.utils.aoa_to_sheet(serviceRows), 'services');
	try {
		XLSX.writeFile(workbook, OUTPUT_XLSX);
	} catch (error) {
		if (error && error.code === 'EBUSY') {
			console.warn(`WARN: ${OUTPUT_XLSX} is locked (close Excel). JSON/CSV still written.`);
		} else {
			throw error;
		}
	}

	const csvLines = serviceRows.map((row) => row.map(escapeCsvCell).join(';'));
	const csvContent = `\uFEFF${csvLines.join('\r\n')}`;
	await writeFile(OUTPUT_CSV, csvContent, 'utf8');

	console.log(`Source: ${SOURCE_XLSX}`);
	console.log(`Generated ${OUTPUT_JSON}`);
	console.log(`Generated ${OUTPUT_XLSX} (sheet: services)`);
	console.log(`Generated ${OUTPUT_CSV}`);
	console.log(`Services: ${services.length}`);
	console.log(`With image: ${withImages}/${services.length}`);
	console.log('Sample URLs:');
	for (const service of services.slice(0, 3)) {
		console.log(`  ${buildServiceUrl(service)} (slug: ${service.slug})`);
		if (service.image) console.log(`    image: ${service.image}`);
	}
	const nested = services.find((service) => service.title === 'Полировка и шлифовка фар');
	if (nested) {
		console.log(`  ${buildServiceUrl(nested)} (slug: ${nested.slug})`);
		if (nested.image) console.log(`    image: ${nested.image}`);
	}
}

const isDirectRun = process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url);

if (isDirectRun) {
	main().catch((error) => {
		console.error(error);
		process.exit(1);
	});
}
