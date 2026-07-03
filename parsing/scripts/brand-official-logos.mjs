/**
 * Официальные логотипы марок — PNG URL для импорта.
 *
 * Источники:
 * - Wikimedia Commons — официальные векторные логотипы брендов (PNG 500px)
 * - carlogos.org — PNG высокого разрешения на основе официальных гайдлайнов
 */

const WIKIMEDIA_WIDTH = 500;

/** @param {string} commonsPath @param {string} filename */
export function wikiPng(commonsPath, filename) {
	const encoded = encodeURIComponent(filename);
	return `https://upload.wikimedia.org/wikipedia/commons/thumb/${commonsPath}/${WIKIMEDIA_WIDTH}px-${encoded}.png`;
}

/**
 * Проверенные логотипы с Wikimedia Commons (официальные brand assets).
 * Ключ — slug марки на новом сайте.
 */
export const WIKIMEDIA_LOGO_BY_SLUG = {
	acura: wikiPng('a/af/Acura_logo.svg', 'Acura_logo.svg'),
	audi: wikiPng('9/92/Audi-Logo_2016.svg', 'Audi-Logo_2016.svg'),
	bmw: wikiPng('4/44/BMW.svg', 'BMW.svg'),
	chevrolet:
		'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1e/Chevrolet-logo.png/500px-Chevrolet-logo.png',
	chrysler: wikiPng('0/0e/ChryPly_Blue_Pentastar.svg', 'ChryPly_Blue_Pentastar.svg'),
	dodge: wikiPng('6/6a/Dodge_logo.svg', 'Dodge_logo.svg'),
	ford: wikiPng('a/a0/Ford_Motor_Company_Logo.svg', 'Ford_Motor_Company_Logo.svg'),
	honda: wikiPng('3/38/Honda.svg', 'Honda.svg'),
	hummer: wikiPng('6/65/Hummer.svg', 'Hummer.svg'),
	hyundai: wikiPng('4/44/Hyundai_Motor_Company_logo.svg', 'Hyundai_Motor_Company_logo.svg'),
	jeep: wikiPng('0/0d/Jeep_logo.svg', 'Jeep_logo.svg'),
	kia: wikiPng('b/b6/KIA_logo3.svg', 'KIA_logo3.svg'),
	ktm: wikiPng('a/a9/KTM-Logo.svg', 'KTM-Logo.svg'),
	maybach: wikiPng('4/4d/Maybach-Logo.svg', 'Maybach-Logo.svg'),
	mitsubishi: wikiPng('5/5a/Mitsubishi_logo.svg', 'Mitsubishi_logo.svg'),
	nissan: wikiPng('0/0f/Nissan_logo.svg', 'Nissan_logo.svg'),
	opel: wikiPng('2/2b/Opel_logo_2023.svg', 'Opel_logo_2023.svg'),
	'rolls-royce': wikiPng('5/52/Rolls-Royce_Motor_Cars_logo.svg', 'Rolls-Royce_Motor_Cars_logo.svg'),
	toyota: wikiPng('e/e7/Toyota.svg', 'Toyota.svg'),
	volvo: wikiPng('5/54/Volvo_logo.svg', 'Volvo_logo.svg'),
	vw: wikiPng('6/6d/Volkswagen_logo_2019.svg', 'Volkswagen_logo_2019.svg'),
	/** Категория ДХО — иконка фары (не автобренд) */
	drl: wikiPng('6/62/Incandescent_light_bulb.svg', 'Incandescent_light_bulb.svg'),
	/** Категория HPL — иконка лампы (не автобренд) */
	hpl: wikiPng('6/62/Incandescent_light_bulb.svg', 'Incandescent_light_bulb.svg'),
};

/** Slug на сайте → slug на carlogos.org */
export const CARLOGOS_SLUG_ALIASES = {
	mercedes: 'mercedes-benz',
	vw: 'volkswagen',
};

const CARLOGOS_BASE = 'https://www.carlogos.org/car-logos';

/**
 * @param {string} slug
 * @returns {string}
 */
export function resolveOfficialLogoUrl(slug) {
	if (WIKIMEDIA_LOGO_BY_SLUG[slug]) {
		return WIKIMEDIA_LOGO_BY_SLUG[slug];
	}

	const carlogosSlug = CARLOGOS_SLUG_ALIASES[slug] ?? slug;
	return `${CARLOGOS_BASE}/${carlogosSlug}-logo.png`;
}
