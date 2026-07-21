import { writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import XLSX from 'xlsx';

import { DATA_DIR } from './utils.mjs';

const OUTPUT_XLSX = path.join(DATA_DIR, 'promotions-import.xlsx');
const OUTPUT_JSON = path.join(DATA_DIR, 'promotions-import.json');
const OUTPUT_CSV = path.join(DATA_DIR, 'promotions-import.csv');

const BRAND = 'КБ АВТО';
const CITY = 'СПб';

const PROMO_HEADERS = [
	'title',
	'slug',
	'excerpt',
	'badge',
	'hero_title',
	'hero_subtitle',
	'price_old',
	'price_new',
	'price_savings',
	'package_name',
	'package_items',
	'benefits',
	'valid_until',
	'poster',
	'featured_image',
	'before_after',
];

const SEO_HEADERS = [
	'Focus Keyword',
	'SEO Title',
	'Meta Description',
	'Meta Keywords',
	'Facebook Title',
	'Twitter Title',
];

const HEADERS = [...PROMO_HEADERS, ...SEO_HEADERS];

/**
 * Фото из кейсов портфолио (реальные до/после, без плейсхолдеров).
 * before_after — URL через | (до|после) для ACF gallery.
 * poster — заглавное фото карточки архива; featured_image — WP Featured Image.
 */
const CASE_IMAGES = {
	/** Kia Ceed III — BI LED Aozoom A4 вместо штатного галогена */
	aozoom: {
		before: 'https://ksenonspb.ru/wp-content/uploads/2020/11/52AAAgHa0-A-9601.jpg',
		after: 'https://ksenonspb.ru/portfolio/0MAAAgO60-A-960.jpg',
	},
	/** AUDI R8 — установка Bi-LED (результат как идея подарка) */
	gift: {
		before: 'https://ksenonspb.ru/portfolio/jwAAAgDI0OA-960.jpg',
		after: 'https://ksenonspb.ru/wp-content/uploads/2018/07/YYAAAgDI0OA-960.jpg',
	},
	/** MERСEDES ML — полировка и шлифовка фар */
	polish: {
		before: 'https://ksenonspb.ru/wp-content/uploads/2016/08/1-1.jpg',
		after: 'https://ksenonspb.ru/wp-content/uploads/2016/08/5.jpg',
		/** Обложка услуги «Полировка, шлифовка и бронировка стекол фар» */
		poster: 'https://ksenonspb.ru/wp-content/uploads/2016/09/polish-1.jpg',
	},
	/** AUDI Q3 — ремонт фар после ДТП */
	dtp: {
		before: 'https://ksenonspb.ru/portfolio/KkAAAgII9OA-1920.jpg',
		after: 'https://ksenonspb.ru/wp-content/uploads/2020/03/YwAAAgKI9OA-19201.jpg',
	},
};

function encodeBeforeAfter(before, after) {
	return [before, after].map((url) => String(url ?? '').trim()).filter(Boolean).join('|');
}

function imagesFromCase(key) {
	const images = CASE_IMAGES[key];
	if (!images) {
		return { poster: '', featured_image: '', before_after: '' };
	}

	const poster = images.poster || images.after || '';
	const featured = images.after || poster;

	return {
		poster,
		featured_image: featured,
		before_after: encodeBeforeAfter(images.before, images.after),
	};
}

/** Статический сид — на старом сайте акций не было. */
const PROMOTIONS = [
	{
		title: 'Замена галогенных ламп на Bi-LED AOZOOM',
		slug: 'zamena-galogen-na-bi-led-aozoom',
		excerpt:
			'Видите дорогу в 3 раза лучше с первого включения. Официальная гарантия 3 года на модули AOZOOM.',
		badge: 'Акция месяца',
		hero_title: 'Замена галогенных ламп на Bi-LED AOZOOM',
		hero_subtitle:
			'Видите дорогу в 3 раза лучше с первого включения. Официальная гарантия 3 года на модули.',
		price_old: 'от 18 000 ₽',
		price_new: 'от 14 900 ₽',
		price_savings: 'экономия 3 100 ₽',
		package_name: 'пакет BI-LED',
		package_items: [
			'Установка модулей Bi-LED AOZOOM вместо галогенных ламп',
			'Регулировка светотеневой границы на стенде',
			'Подключение дальнего света — бесплатно',
			'Демонтаж, монтаж и герметизация фар',
		],
		benefits: [
			{ title: '8 лет опыта', text: 'Более 900 выполненных установок' },
			{ title: 'Только оригинальные модули', text: 'AOZOOM, Optima, Dixel' },
			{ title: 'Переделаем бесплатно', text: 'Если результат не устроит' },
			{ title: 'Бесплатная диагностика фар', text: 'Перед началом работ' },
		],
		valid_until: '31.12.2026',
		...imagesFromCase('aozoom'),
	},
	{
		title: 'Идеи подарков автомобилистам: подарочные сертификаты',
		slug: 'podarochnye-sertifikaty',
		excerpt:
			'Идеи подарков для владельцев автомобилей: от 2 500 ₽ до бесконечности. Для мужчин и женщин. Под любой запрос и кошелёк.',
		badge: 'Подарок',
		hero_title: 'Подарочные сертификаты на услуги лаборатории автосвета',
		hero_subtitle:
			'Выглядят дорого и презентабельно. Выбирайте номинал или проконсультируйтесь с менеджером — подберём пакет под задачу.',
		price_old: '',
		price_new: 'от 2 500 ₽',
		price_savings: '',
		package_name: 'сертификат',
		package_items: [
			'Номинал от 2 500 ₽ — без ограничения сверху',
			'Действует на все услуги лаборатории',
			'Красивое оформление — можно вручить сразу',
			'Срок действия — 12 месяцев с даты покупки',
		],
		benefits: [
			{ title: 'Универсальный подарок', text: 'Подходит мужчинам и женщинам' },
			{ title: 'Любой номинал', text: 'От 2 500 ₽ без верхней границы' },
			{ title: 'Все услуги', text: 'Ретрофит, ремонт, полировка, тюнинг' },
			{ title: 'Консультация', text: 'Поможем выбрать подходящий пакет' },
		],
		valid_until: '31.12.2026',
		...imagesFromCase('gift'),
	},
	{
		title: 'Полировка и бронирование стекол фар со скидкой',
		slug: 'polirovka-i-bronirovanie-far',
		excerpt:
			'Восстановим прозрачность поликарбоната и защитим стёкла плёнкой. Комплект «полировка + бронь» дешевле, чем по отдельности.',
		badge: 'Комплект',
		hero_title: 'Полировка и бронирование стекол фар',
		hero_subtitle:
			'Снимаем мутный заводской лак, шлифуем и наносим новый УФ-слой. Сверху — защитная плёнка, чтобы результат держался дольше.',
		price_old: 'от 12 000 ₽',
		price_new: 'от 9 900 ₽',
		price_savings: 'экономия 2 100 ₽',
		package_name: 'полировка + бронь',
		package_items: [
			'Полная полировка двух фар со снятием старого лака',
			'Нанесение двухкомпонентного УФ-защитного слоя',
			'Бронирование стекол защитной плёнкой',
			'Контроль герметичности и внешний осмотр',
		],
		benefits: [
			{ title: 'Правильная технология', text: 'Не «глянец на неделю», а новый лак' },
			{ title: 'Защита плёнкой', text: 'Меньше сколов и повторного помутнения' },
			{ title: 'Обе фары', text: 'Работаем комплектом, без разницы по цвету' },
			{ title: 'Гарантия на работу', text: 'Письменно — до 12 месяцев' },
		],
		valid_until: '31.08.2026',
		...imagesFromCase('polish'),
	},
	{
		title: 'Ремонт оптики после ДТП — скидка 15%',
		slug: 'remont-optiki-posle-dtp-skidka',
		excerpt:
			'Чиним фары, которые другие предлагают менять целиком. Диагностика, восстановление корпуса, герметизация и регулировка света.',
		badge: '−15%',
		hero_title: 'Ремонт оптики после ДТП со скидкой 15%',
		hero_subtitle:
			'Восстанавливаем геометрию, крепления и герметичность. Письменная гарантия до 2 лет — без покупки новой фары.',
		price_old: 'от 25 000 ₽',
		price_new: 'от 21 250 ₽',
		price_savings: 'скидка 15%',
		package_name: 'ремонт после ДТП',
		package_items: [
			'Диагностика повреждений и смета до начала работ',
			'Восстановление корпуса, креплений и направляющих',
			'Герметизация шва и замена дренажных мембран',
			'Регулировка света на стенде после сборки',
		],
		benefits: [
			{ title: 'Дешевле новой фары', text: 'Ремонт вместо замены целиком' },
			{ title: 'Гарантия до 2 лет', text: 'Письменно на выполненные работы' },
			{ title: 'Смета заранее', text: 'Фиксируем объём до старта' },
			{ title: 'Стенд регулировки', text: 'Свет как с завода после ремонта' },
		],
		valid_until: '30.09.2026',
		...imagesFromCase('dtp'),
	},
];

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

function encodePackageItems(items) {
	return (items ?? []).map((text) => String(text).trim()).filter(Boolean).join('|');
}

function encodeBenefits(items) {
	return (items ?? [])
		.map((item) => `${String(item.title ?? '').trim()}::${String(item.text ?? '').trim()}`)
		.filter((row) => row !== '::')
		.join('|');
}

function buildSeoFields(promo) {
	const focus = truncate(promo.title, 60);
	const seoTitle = truncate(`${promo.title} — акция | ${BRAND} ${CITY}`, 60);
	const meta = truncate(promo.excerpt || promo.hero_subtitle || promo.title, 155);

	return {
		'Focus Keyword': focus,
		'SEO Title': seoTitle,
		'Meta Description': meta,
		'Meta Keywords': `${promo.title}, акция, ${BRAND}, автосвет, ${CITY}`,
		'Facebook Title': seoTitle,
		'Twitter Title': seoTitle,
	};
}

function enrichPromotion(promo) {
	const seo = buildSeoFields(promo);

	return {
		...promo,
		poster: promo.poster ?? '',
		featured_image: promo.featured_image ?? '',
		before_after: promo.before_after ?? '',
		package_items_encoded: encodePackageItems(promo.package_items),
		benefits_encoded: encodeBenefits(promo.benefits),
		focus_keyword: seo['Focus Keyword'],
		seo_title: seo['SEO Title'],
		meta_description: seo['Meta Description'],
		meta_keywords: seo['Meta Keywords'],
		facebook_title: seo['Facebook Title'],
		twitter_title: seo['Twitter Title'],
	};
}

function promotionToRow(item) {
	const seo = buildSeoFields(item);

	return [
		item.title ?? '',
		item.slug ?? '',
		item.excerpt ?? '',
		item.badge ?? '',
		item.hero_title ?? '',
		item.hero_subtitle ?? '',
		item.price_old ?? '',
		item.price_new ?? '',
		item.price_savings ?? '',
		item.package_name ?? '',
		item.package_items_encoded ?? encodePackageItems(item.package_items),
		item.benefits_encoded ?? encodeBenefits(item.benefits),
		item.valid_until ?? '',
		item.poster ?? '',
		item.featured_image ?? '',
		item.before_after ?? '',
		seo['Focus Keyword'],
		seo['SEO Title'],
		seo['Meta Description'],
		seo['Meta Keywords'],
		seo['Facebook Title'],
		seo['Twitter Title'],
	];
}

function validatePromotions(promotions) {
	if (!promotions.length) {
		throw new Error('No promotions to export');
	}

	const slugs = new Set();

	for (const promo of promotions) {
		if (!promo.title) {
			throw new Error(`Promotion "${promo.slug}" is missing title`);
		}
		if (!promo.slug) {
			throw new Error(`Promotion "${promo.title}" is missing slug`);
		}
		if (slugs.has(promo.slug)) {
			throw new Error(`Duplicate slug "${promo.slug}"`);
		}
		slugs.add(promo.slug);

		if (!promo.hero_title) {
			throw new Error(`Missing hero_title for "${promo.slug}"`);
		}
		if (!promo.package_items_encoded && !encodePackageItems(promo.package_items)) {
			throw new Error(`Missing package_items for "${promo.slug}"`);
		}
		if (!promo.benefits_encoded && !encodeBenefits(promo.benefits)) {
			throw new Error(`Missing benefits for "${promo.slug}"`);
		}
	}
}

async function writeOutputs(promotions) {
	const rows = [HEADERS, ...promotions.map((promo) => promotionToRow(promo))];

	const exportList = promotions.map((promo) => ({
		title: promo.title,
		slug: promo.slug,
		excerpt: promo.excerpt,
		badge: promo.badge,
		hero_title: promo.hero_title,
		hero_subtitle: promo.hero_subtitle,
		price_old: promo.price_old,
		price_new: promo.price_new,
		price_savings: promo.price_savings,
		package_name: promo.package_name,
		package_items: promo.package_items_encoded,
		benefits: promo.benefits_encoded,
		valid_until: promo.valid_until,
		poster: promo.poster,
		featured_image: promo.featured_image,
		before_after: promo.before_after,
		focus_keyword: promo.focus_keyword,
		seo_title: promo.seo_title,
		meta_description: promo.meta_description,
		meta_keywords: promo.meta_keywords,
		facebook_title: promo.facebook_title,
		twitter_title: promo.twitter_title,
	}));

	const importJson = {
		generated_for: 'WP All Import — CPT promotion',
		source: path.basename(OUTPUT_XLSX),
		note:
			'title → post_title, slug → post_name (URL /akcii/{slug}/), excerpt → post_excerpt (карточка архива). badge / hero_* / price_* / package_name → ACF. package_items / benefits — НЕ мапить в ACF Variable Mode: пишет тема (inc/wp-all-import.php); запасной путь ksenon_raw_package_items / ksenon_raw_benefits = {column[1]}. package_items — строки через |; benefits — title::text через | → ACF benefits_cards. poster → ACF poster; featured_image → WP Featured Image; before_after — до|после (ACF gallery). SEO-колонки → Rank Math. Уникальный идентификатор — slug. На старом сайте акций не было — контент сгенерирован.',
		columns: HEADERS,
		promotions: exportList,
	};

	await writeFile(OUTPUT_JSON, `${JSON.stringify(importJson, null, 2)}\n`, 'utf8');

	const csvLines = rows.map((row) => row.map(escapeCsvCell).join(';'));
	const csvContent = `\uFEFF${csvLines.join('\r\n')}`;
	await writeFile(OUTPUT_CSV, csvContent, 'utf8');

	const workbook = XLSX.utils.book_new();
	XLSX.utils.book_append_sheet(workbook, XLSX.utils.aoa_to_sheet(rows), 'promotions');

	try {
		XLSX.writeFile(workbook, OUTPUT_XLSX);
	} catch (error) {
		if (error && (error.code === 'EBUSY' || error.code === 'EPERM')) {
			const fallback = path.join(DATA_DIR, 'promotions-import.new.xlsx');
			XLSX.writeFile(workbook, fallback);
			console.warn(
				`XLSX locked (${OUTPUT_XLSX}). Wrote fallback: ${fallback}. Close Excel and rename/replace.`
			);
		} else {
			throw error;
		}
	}
}

async function main() {
	const enriched = PROMOTIONS.map((promo) => enrichPromotion(promo));

	validatePromotions(enriched);
	await writeOutputs(enriched);

	console.log(`Generated ${OUTPUT_XLSX} (sheet: promotions)`);
	console.log(`Generated ${OUTPUT_JSON}`);
	console.log(`Generated ${OUTPUT_CSV}`);
	console.log(`Promotions: ${enriched.length}`);
}

const isDirectRun = process.argv[1] && path.resolve(process.argv[1]) === fileURLToPath(import.meta.url);

if (isDirectRun) {
	main().catch((error) => {
		console.error(error);
		process.exit(1);
	});
}
