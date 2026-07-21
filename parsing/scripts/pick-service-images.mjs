import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * Подбор фото для услуг:
 * 1) ищем страницу/кейс с похожим названием (portfolio-excluded + portfolio cases)
 * 2) берём cover/hero фото оттуда
 * 3) полностью перезаписываем service-images-map.json
 */

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const BASE = 'https://ksenonspb.ru';
const BAD_IMAGE_PARTS = [
	'sT1Au9E5E4tZRh3x-yY-TtGRJck-1920',
	'8FeX41yZ3b2mVdd1jWi45NW5uoY-960',
	'esD-5MJQmzWE3pRTnr1UMgEKJiU-960',
	'QwisvkNREXiXiDRRslmWElJGht4-960',
	'how-to-choose-the-best-car-amplifier',
	'2013-dodge-ram-1500-quad-laramie-truck-angular-front',
	'heads.png',
	'harness.jpeg',
];

const pages = JSON.parse(await readFile(path.join(ROOT, 'data/old-pages.json'), 'utf8')).pages;
const cases = JSON.parse(await readFile(path.join(ROOT, 'data/portfolio-import.json'), 'utf8')).cases;
const excluded = JSON.parse(await readFile(path.join(ROOT, 'data/portfolio-excluded.json'), 'utf8')).items;
const services = JSON.parse(await readFile(path.join(ROOT, 'data/services-import.json'), 'utf8')).services;
const byPath = new Map(pages.map((p) => [p.path, p]));

/**
 * Услуга → источник фото по названию.
 * type: excluded — тематическая страница из portfolio-excluded
 * type: case — кейс портфолио (titleExact или titleIncludes)
 */
const SERVICE_SOURCE = {
	'Замена линз в фарах': { type: 'case', titleIncludes: 'замена линз' },
	'Установка лазерных (Laser) линз': {
		type: 'case',
		preferTitle: 'BMW X7 Lazer — ремонт выгоревшей ресницы.',
	},
	'Полировка и шлифовка фар': {
		type: 'excluded',
		title: 'Полировка, шлифовка и бронировка стекол фар',
	},
	'Ремонт фар': {
		type: 'case',
		preferTitle: 'BMW 3er E90 — ремонт фар',
	},
	'Замена стёкол фар': {
		type: 'excluded',
		title: 'Замена стекол и корпусов фар',
	},
	'Устранение запотевания фар': {
		type: 'excluded',
		title: 'Ремонт запотевающих фар',
	},
	'Установка Bi-LED (светодиодных) линз': {
		type: 'excluded',
		title: 'Установка Bi LED линзовых элементов',
	},
	'Установка ксенона': {
		type: 'excluded',
		title: 'Установка биксеноновых модулей',
	},
	'Замена штатных линз на оригинал или аналог': {
		type: 'case',
		preferTitle: 'Cadillac XT5 — меняем штатные LED линзы HELLA',
	},
	'Тюнинг подсветки — Ангельские и Дьявольские глазки': {
		type: 'case',
		preferTitle: 'BMW X5 E53 рестайлинг . Замена колец на LED DRL от TAU tech',
	},
	'Покраска масок фар': { type: 'excluded', title: 'Покраска фар' },
	'Изготовление дневных ходовых огней (ДХО)': {
		type: 'case',
		preferTitle: 'ACURA MDX',
	},
	'Регулировка света фар на оптическом стенде': {
		type: 'excluded',
		title: 'Регулировка фар',
	},
	'Замена штатных блоков розжига и ламп': {
		type: 'excluded',
		title: 'Блоки розжига TOYOTA и LEXUS',
	},
	'Ремонт штатных ДХО': { type: 'case', titleIncludes: 'ремонт штатных дхо' },
	'Установка динамических («бегущих») поворотников': {
		type: 'case',
		titleIncludes: 'бегущ',
	},
	'Ремонт штатных светодиодных фар (LED-ремонт)': {
		type: 'case',
		preferTitle: 'BMW 7 F01 — ремонт LED фары',
	},
	'Ремонт штатных светодиодных фонарей': {
		type: 'excluded',
		title: 'Ремонт светодиодных фонарей',
	},
	'Установка и программная активация ПТФ': {
		type: 'case',
		preferTitle: 'LED ПТФ TOYOTA original',
	},
	'Установка светодиодных противотуманных фар': {
		type: 'case',
		preferTitle: 'Dodge RAM — установка BI LED + ПТФ Morimoto / ремонт фонаря.',
	},
	'Замена стёкол противотуманных фар': {
		type: 'case',
		preferTitle: 'Hyundai Santa Fe — ремонт штатных ПТФ с ДХО',
	},
	'Замена ламп в ПТФ': {
		type: 'case',
		preferTitle: 'LED ПТФ TOYOTA original',
	},
	'Замена ламп на светодиодные — салон, габариты, подсветка номера': {
		type: 'case',
		preferTitle: 'BMW X5 E53 — установка светодиодов',
	},
	'Ремонт корректора фар — ручные и автоматические корректоры': {
		type: 'case',
		preferTitle: 'Toyota Sequoia — установка квадро-светодиодов',
	},
	'Ремонт омывателя фар': {
		type: 'case',
		preferTitle: 'TOYOTA TUNDRA',
	},
	'Ремонт отражателей фар — чистка или замена рефлекторов': {
		type: 'excluded',
		title: 'Восстановление отражателей',
	},
	'Ремонт корпуса фар': { type: 'case', titleIncludes: 'ремонт корпуса' },
	'Восстановление креплений («ушек») фары': {
		type: 'case',
		titleIncludes: 'подубит',
	},
	'Ремонт ксеноновых фар': {
		type: 'case',
		preferTitle: 'INFINITI M37 — ремонт ксеноновых фар',
	},
	'Комплексное улучшение света фар': {
		type: 'case',
		preferTitle: 'BMW E70. Несколько вариантов улучшения света от ксенона до LED линз.',
	},
	'Ремонт фар европейских производителей': {
		type: 'case',
		preferTitle: 'Bentley Continental GT — ремонт фар',
	},
	'Ремонт фар китайских производителей': {
		type: 'case',
		preferTitle: 'Kia Ceed II — проблема ДХО. Ремонт с гарантией',
	},
	'Ремонт фар американского рынка': {
		type: 'case',
		preferTitle: 'Cadillac Escalade III — BI LED I.LENS NEW и HPL Crossfire',
	},
	'Ремонт систем адаптивного освещения (AFS)': {
		type: 'case',
		preferTitle: 'AUDI Q7 AFS адаптив + BI-LED OPTIMA',
	},
	'Восстановление отражателей методом вакуумного напыления': {
		type: 'excluded',
		title: 'Восстановление отражателей',
	},
	'Ремонт и перепайка драйверов (плат управления) LED': {
		type: 'case',
		preferTitle: 'MAZDA CX5',
	},
	'Ремонт контроллеров управления фарой (ЭБУ)': {
		type: 'excluded',
		title: 'Ремонт электронных блоков управления',
	},
	'Химическая чистка фары изнутри': {
		type: 'excluded',
		title: 'Чистка фар изнутри',
	},
	'Программное отключение опроса ламп («обманки»)': {
		type: 'case',
		preferTitle: 'Chevrolet Tahoe 2014 — установка светодиодов',
	},
	'Замена внутренней проводки фары': {
		type: 'case',
		preferTitle: 'Opel Insignia — проблемы фар и как их не надо делать',
	},
	'Установка автокорректоров фар': {
		type: 'case',
		preferTitle: 'v70 Замена стёкол фар на новые и Bi led взамен галогена',
	},
	'Кодирование и программирование штатных блоков управления светом + компьютерная диагностика': {
		type: 'excluded',
		title: 'Ремонт электронных блоков управления',
	},
};

function normalizeUploadUrl(url) {
	return String(url || '')
		.trim()
		.replace(/^http:\/\//i, 'https://')
		.replace(/-\d+x\d+(\.(png|jpe?g|webp|svg|gif))$/i, '$1');
}

function resolveImageUrl(src) {
	const text = String(src ?? '')
		.trim()
		.replace(/\\/g, '/');
	if (!text) return '';
	if (/^https?:\/\//i.test(text)) return normalizeUploadUrl(text);
	const uploadsMatch =
		text.match(/(?:^|\/)(?:images\/)?uploads\/(.+)$/i) ||
		text.match(/wp-content\/uploads\/(.+)$/i);
	if (uploadsMatch) {
		return normalizeUploadUrl(`${BASE}/wp-content/uploads/${uploadsMatch[1]}`);
	}
	const externalMatch = text.match(/(?:^|\/)(?:images\/)?external\/(.+)$/i);
	if (externalMatch) {
		return `${BASE}/portfolio/${path.basename(externalMatch[1])}`;
	}
	return '';
}

function isBadImage(url) {
	if (!url) return true;
	const lower = url.toLowerCase();
	return BAD_IMAGE_PARTS.some((part) => lower.includes(part.toLowerCase()));
}

function scoreUrl(url) {
	if (!url || isBadImage(url)) return -100;
	let score = 0;
	if (url.includes('/wp-content/uploads/')) score += 50;
	if (/\.(jpe?g|png|webp)$/i.test(url)) score += 10;
	if (/-\d+x\d+\./i.test(url)) score -= 20;
	if (url.includes('/portfolio/') && !url.includes('/uploads/')) score -= 5;
	return score;
}

function resolveLocalDir(localDir) {
	if (!localDir) return '';
	if (path.isAbsolute(localDir)) return localDir;
	return path.join(ROOT, localDir);
}

function pathFromExcluded(item) {
	try {
		const pathname = new URL(item.old_url).pathname;
		return decodeURIComponent(pathname);
	} catch {
		return `/${item.slug}/`;
	}
}

async function imagesFromPagePath(pagePath, sourceLabel) {
	const page = byPath.get(pagePath);
	if (!page?.local_dir) return [];
	const dir = resolveLocalDir(page.local_dir);
	try {
		const meta = JSON.parse(await readFile(path.join(dir, 'meta.json'), 'utf8'));
		let html = '';
		try {
			html = await readFile(path.join(dir, 'content.html'), 'utf8');
		} catch {
			html = '';
		}
		const ordered = [];
		const cover = html.match(/entry-cover[\s\S]*?<img[^>]+src=["']([^"']+)["']/i);
		if (cover) {
			const url = resolveImageUrl(cover[1]);
			if (url && !isBadImage(url)) {
				ordered.push({ url, source: sourceLabel, via: 'excluded-cover' });
			}
		}
		const imgs = (meta.images || [])
			.map(resolveImageUrl)
			.filter((url) => url && !isBadImage(url))
			.sort((a, b) => scoreUrl(b) - scoreUrl(a));
		for (const url of imgs) {
			if (!ordered.some((item) => item.url === url)) {
				ordered.push({ url, source: sourceLabel, via: 'excluded-meta' });
			}
		}
		return ordered;
	} catch {
		return [];
	}
}

function imagesFromCase(item) {
	const candidates = [];
	for (const key of ['after_image', 'featured_image', 'hero_image', 'before_image']) {
		const url = resolveImageUrl(item[key]);
		if (url && !isBadImage(url)) {
			candidates.push({
				url,
				source: item.title,
				via: `case:${key}`,
				score: scoreUrl(url) + (key === 'after_image' ? 8 : key === 'featured_image' ? 5 : 3),
			});
		}
	}
	for (const photo of String(item.photos || '')
		.split('|')
		.map((p) => resolveImageUrl(p))
		.filter((url) => url && !isBadImage(url))
		.slice(0, 6)) {
		candidates.push({ url: photo, source: item.title, via: 'case:photos', score: scoreUrl(photo) });
	}
	candidates.sort((a, b) => b.score - a.score);
	return candidates;
}

function findExcluded(title) {
	return excluded.find((item) => item.title === title) || null;
}

function findCases(rule) {
	if (rule.preferTitle) {
		const preferred = cases.find((item) => item.title === rule.preferTitle);
		if (preferred) return [preferred];
	}
	const needle = String(rule.titleIncludes || '').toLowerCase();
	const also = String(rule.titleAlsoIncludes || '').toLowerCase();
	const brands = (rule.brandIncludes || []).map((b) => b.toLowerCase());

	return cases.filter((item) => {
		const title = item.title.toLowerCase().replace(/ё/g, 'е');
		if (needle && !title.includes(needle.replace(/ё/g, 'е'))) return false;
		if (also && !title.includes(also.replace(/ё/g, 'е'))) return false;
		if (brands.length && !brands.some((b) => title.includes(b))) return false;
		return true;
	});
}

const mapping = {};
const errors = [];

for (const service of services) {
	const title = service.title;
	const rule = SERVICE_SOURCE[title];
	let candidates = [];

	if (!rule) {
		errors.push(`no SERVICE_SOURCE for "${title}"`);
	} else if (rule.type === 'excluded') {
		const item = findExcluded(rule.title);
		if (!item) {
			errors.push(`excluded not found for "${title}": ${rule.title}`);
		} else {
			const pagePath = pathFromExcluded(item);
			candidates = await imagesFromPagePath(pagePath, item.title);
			if (!candidates.length) {
				errors.push(`no images on excluded "${item.title}" (${pagePath})`);
			}
		}
	} else if (rule.type === 'case') {
		const matched = findCases(rule);
		if (!matched.length) {
			errors.push(`no cases for "${title}" rule ${JSON.stringify(rule)}`);
		} else {
			for (const item of matched) {
				candidates.push(...imagesFromCase(item));
			}
			candidates.sort((a, b) => (b.score || 0) - (a.score || 0));
		}
	}

	// Берём лучшее фото по названию услуги (без «уникальности» между услугами —
	// иначе второй услуге с той же excluded-страницы уезжает чужая meta-картинка).
	const picked = candidates[0] || null;

	mapping[title] = picked?.url
		? { image: picked.url, source: picked.source || '', via: picked.via }
		: { image: '', source: '', via: 'none' };
}

const empty = Object.entries(mapping).filter(([, v]) => !v.image);
console.log('errors:', errors.length ? errors : 'none');
console.log('without image:', empty.length ? empty.map(([t]) => t) : 'none');
console.log('mapped:', Object.values(mapping).filter((v) => v.image).length, '/', services.length);

const flat = Object.fromEntries(
	Object.entries(mapping).map(([title, info]) => [title, info.image]),
);

await writeFile(
	path.join(ROOT, 'data/service-images-map.json'),
	`${JSON.stringify(
		{
			generated_at: new Date().toISOString(),
			note: 'title → image. Match service name to portfolio-excluded page or portfolio case title, take photo from there.',
			images: flat,
			details: mapping,
		},
		null,
		2,
	)}\n`,
	'utf8',
);

for (const [title, info] of Object.entries(mapping)) {
	console.log(`${info.image ? 'OK' : 'MISS'}\t${title}`);
	console.log(`  ${info.image}`);
	console.log(`  via ${info.via} | ${info.source}`);
}
