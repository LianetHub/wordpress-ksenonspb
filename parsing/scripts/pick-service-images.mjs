import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const BASE = 'https://ksenonspb.ru';

const pages = JSON.parse(await readFile(path.join(ROOT, 'data/old-pages.json'), 'utf8')).pages;
const cases = JSON.parse(await readFile(path.join(ROOT, 'data/portfolio-import.json'), 'utf8')).cases;
const services = JSON.parse(await readFile(path.join(ROOT, 'data/services-import.json'), 'utf8')).services;
const byPath = new Map(pages.map((p) => [p.path, p]));

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

function scoreUrl(url) {
	if (!url) return -1;
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

async function imagesFromPage(pagePath) {
	const page = byPath.get(pagePath);
	if (!page?.local_dir) return { error: null, images: [] };
	const dir = resolveLocalDir(page.local_dir);
	const metaPath = path.join(dir, 'meta.json');
	const htmlPath = path.join(dir, 'content.html');
	try {
		const meta = JSON.parse(await readFile(metaPath, 'utf8'));
		let html = '';
		try {
			html = await readFile(htmlPath, 'utf8');
		} catch {
			html = '';
		}
		const ordered = [];
		const cover = html.match(/entry-cover[\s\S]*?<img[^>]+src=["']([^"']+)["']/i);
		if (cover) {
			const url = resolveImageUrl(cover[1]);
			if (url) ordered.push({ url, source: pagePath, via: 'cover' });
		}
		const imgs = (meta.images || []).map(resolveImageUrl).filter(Boolean);
		imgs.sort((a, b) => scoreUrl(b) - scoreUrl(a));
		for (const url of imgs) {
			if (!ordered.some((item) => item.url === url)) {
				ordered.push({ url, source: pagePath, via: 'meta' });
			}
		}
		return { error: null, images: ordered };
	} catch (error) {
		return { error: error.message, images: [] };
	}
}

/** Услуга → старая страница со смысловым фото. */
const OLD_POST_BY_SERVICE = {
	'Замена линз в фарах': '/установка-светодиодных-линз/',
	'Установка лазерных (Laser) линз': '/установка-линз-bi-led-optima-professional/',
	'Установка Bi-LED (светодиодных) линз': '/установка-светодиодных-линз/',
	'Установка ксенона': '/установка-биксеноновых-линз/',
	'Замена штатных линз на оригинал или аналог': '/установка-светодиодных-линз-bmw-e87/',
	'Полировка и шлифовка фар': '/полировка-стекол-фар/',
	'Замена стёкол фар': '/замена-стекол-фар/',
	'Устранение запотевания фар': '/ремонт-запотевающих-фар/',
	'Покраска масок фар': '/покраска-фар/',
	'Изготовление дневных ходовых огней (ДХО)': '/светодиодный-тюнинг-light-label/',
	'Регулировка света фар на оптическом стенде': '/регулировка-фар/',
	'Ремонт штатных светодиодных фонарей': '/ремонт-задних-фонарей/',
	'Ремонт штатных светодиодных фар (LED-ремонт)': '/ремонт-светодиодной-фары-audi/',
	'Установка светодиодных противотуманных фар': '/установка-противотуманок/',
	'Замена стёкол противотуманных фар': '/замена-стекол-фар/',
	'Замена ламп в ПТФ': '/chevrolet-tahoe-iii-установка-светодиодных-линз/',
	'Установка и программная активация ПТФ': '/установка-противотуманок/',
	'Ремонт отражателей фар — чистка или замена рефлекторов': '/восстановление-отражателей/',
	'Восстановление отражателей методом вакуумного напыления':
		'/toyota-avensis-ii-замена-отражателей/',
	'Ремонт фар': '/ремонт-фар/',
	'Ремонт корпуса фар': '/bmw-x4-ремонт-корпуса-фары-с-разбором-по-ш/',
	'Восстановление креплений («ушек») фары':
		'/bentley-continental-gt-i-восстановление-подубитых-фар/',
	'Химическая чистка фары изнутри': '/volvo-s80-xc70-чистка-изнутри-и-замена-отражате/',
	'Комплексное улучшение света фар': '/bmw-e70-несколько-вариантов-улучшения-свет/',
	'Тюнинг подсветки — Ангельские и Дьявольские глазки': '/мотоцикл-ktm-990-smt-тюнинг-оптики/',
	'Установка динамических («бегущих») поворотников':
		'/land-rover-range-rover-sport-2010-ремонт-фонарей-glohh-и-бегущий-пов/',
	'Замена ламп на светодиодные — салон, габариты, подсветка номера':
		'/chevrolet-tahoe-2007-2014-установка-светодиодов/',
	'Ремонт ксеноновых фар': '/audi-q5-замена-штатного-ксенона-и-установка/',
	'Замена штатных блоков розжига и ламп': '/поддельные-блоки-розжига-toyota-и-lexus/',
	'Ремонт штатных ДХО': '/hyundai-i40-ремонт-штатных-дхо/',
	'Ремонт корректора фар — ручные и автоматические корректоры':
		'/комлпексный-ремонт-автоэлектрики/',
	'Ремонт омывателя фар': '/автоэлектрика/',
	'Ремонт и перепайка драйверов (плат управления) LED': '/ремонт-светодиодных-фонарей/',
	'Ремонт контроллеров управления фарой (ЭБУ)': '/ремонт-электронных-блоков-управлени/',
	'Программное отключение опроса ламп («обманки»)': '/установка-биксеноновых-линз/',
	'Замена внутренней проводки фары': '/ремонт-светодиодной-фары-audi/',
	'Установка автокорректоров фар': '/audi-a8-iii-d4-проблема-плохого-света-фар/',
	'Кодирование и программирование штатных блоков управления светом + компьютерная диагностика':
		'/инсталляция-штатных-головных-устрой/',
	'Ремонт систем адаптивного освещения (AFS)': '/skoda-octavia-a7-замена-би-ксенон-afl-на-bi-led-aozoom-a6-orion/',
	'Ремонт фар европейских производителей': '/ремонт-фар-bentley/',
	'Ремонт фар китайских производителей': '/установка-линз-bi-led-optima-professional/',
	'Ремонт фар американского рынка': '/chevrolet-tahoe-2014-установка-светодиодных-линз/',
};

function portfolioCandidates(serviceTitle) {
	const candidates = [];
	for (const item of cases) {
		const related = String(item.related_services || '')
			.split('|')
			.map((s) => s.trim())
			.filter(Boolean);
		if (!related.includes(serviceTitle)) continue;
		for (const key of ['featured_image', 'hero_image', 'after_image', 'before_image']) {
			const url = resolveImageUrl(item[key]);
			if (url) {
				candidates.push({
					url,
					source: item.title,
					via: `portfolio:${key}`,
				});
			}
		}
		for (const photo of String(item.photos || '')
			.split('|')
			.map((p) => resolveImageUrl(p))
			.filter(Boolean)
			.slice(0, 5)) {
			candidates.push({ url: photo, source: item.title, via: 'portfolio:photos' });
		}
	}
	candidates.sort((a, b) => scoreUrl(b.url) - scoreUrl(a.url));
	return candidates;
}

const used = new Set();
const mapping = {};
const errors = [];

for (const service of services) {
	const title = service.title;
	const oldPath = OLD_POST_BY_SERVICE[title];
	if (oldPath && !byPath.has(oldPath)) {
		errors.push(`missing path for "${title}": ${oldPath}`);
	}

	const fromOld = oldPath ? await imagesFromPage(oldPath) : { error: null, images: [] };
	if (fromOld.error) errors.push(`${title}: ${fromOld.error} (${oldPath})`);

	const fromPortfolio = portfolioCandidates(title);
	const ordered = [...fromOld.images, ...fromPortfolio];

	let picked = null;
	for (const option of ordered) {
		if (!used.has(option.url)) {
			picked = option;
			break;
		}
	}
	if (!picked && ordered[0]) picked = ordered[0];

	if (picked?.url) used.add(picked.url);
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
			note: 'title → absolute image URL from old site (ksenonspb.ru). Prefer old article hero, else portfolio case.',
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
