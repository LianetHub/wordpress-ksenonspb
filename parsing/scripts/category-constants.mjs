/**
 * Canonical service_category taxonomy terms.
 * @see struktura-sayta-remont-far.md §2.1–2.5
 */
export const SERVICE_CATEGORIES = [
	{ slug: 'tyuning-far', name: 'Тюнинг фар', parent_slug: '', order: 1 },
	{ slug: 'remont-far', name: 'Ремонт фар', parent_slug: '', order: 2 },
	{ slug: 'optika-i-korpus', name: 'Оптика и корпус', parent_slug: 'remont-far', order: 1 },
	{ slug: 'elektrika', name: 'Электрика и электроника', parent_slug: 'remont-far', order: 2 },
	{ slug: 'mehanika', name: 'Механика', parent_slug: 'remont-far', order: 3 },
	{ slug: 'regulirovka-diagnostika', name: 'Регулировка и диагностика', parent_slug: '', order: 3 },
	{ slug: 'ptf', name: 'Противотуманные фары (ПТФ)', parent_slug: '', order: 4 },
	{ slug: 'dop-uslugi', name: 'Доп. услуги', parent_slug: '', order: 5 },
];

/** Default card copy for --rebuild-xlsx when xlsx is empty or missing. */
export const CATEGORY_DEFAULTS = {
	'tyuning-far': {
		desc: 'Улучшение света фар: установка линз, ксенона, Bi-LED, тюнинг подсветки и комплексные решения.',
		benefits: 'Современные модули, гарантия',
	},
	'remont-far': {
		desc: 'Комплексный ремонт и восстановление автомобильной оптики любой сложности.',
		benefits: 'Любые марки авто',
	},
	'optika-i-korpus': {
		desc: 'Полировка, замена стёкол, устранение запотевания, ремонт корпуса и отражателей.',
		benefits: 'Восстановление прозрачности',
	},
	elektrika: {
		desc: 'Ремонт LED-модулей, блоков розжига, ДХО, проводки и электроники фар.',
		benefits: 'Диагностика и пайка',
	},
	mehanika: {
		desc: 'Ремонт корректоров, омывателей, ксеноновых узлов и механических элементов фар.',
		benefits: 'Точная регулировка',
	},
	'regulirovka-diagnostika': {
		desc: 'Регулировка света на стенде, ремонт AFS, кодирование блоков и компьютерная диагностика.',
		benefits: 'Оптический стенд',
	},
	ptf: {
		desc: 'Установка, активация и ремонт противотуманных фар, замена стёкол и ламп.',
		benefits: 'Штатная интеграция',
	},
	'dop-uslugi': {
		desc: 'Замена ламп на LED в салоне, габаритах и подсветке номера — дополнительные работы по оптике.',
		benefits: 'Быстро и аккуратно',
	},
};

export const CATEGORY_SLUGS = new Set(SERVICE_CATEGORIES.map((category) => category.slug));

export function categoryBySlug(slug) {
	return SERVICE_CATEGORIES.find((category) => category.slug === slug) ?? null;
}
