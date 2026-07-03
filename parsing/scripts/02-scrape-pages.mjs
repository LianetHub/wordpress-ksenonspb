import { mkdir, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { JSDOM } from 'jsdom';
import {
	BASE_URL,
	DATA_DIR,
	IMAGES_DIR,
	LOGS_DIR,
	PAGES_DIR,
	PARSING_ROOT,
	ensureDirs,
	fetchWithRetry,
	localDirForEntry,
	readJson,
	sleep,
	urlToPath,
	writeJson,
	IMAGE_EXT,
} from './utils.mjs';

const DELAY_MS = 600;
const CONTENT_SELECTORS = [
	'main#main-content',
	'main.content',
	'.entry-content',
	'#page-body .content-inner',
	'article',
];

function extractTitle(document) {
	const h1 = document.querySelector('h1');
	if (h1?.textContent?.trim()) return h1.textContent.trim();
	const title = document.querySelector('title');
	return title?.textContent?.trim()?.replace(/\s*[\|–-].*$/, '').trim() || '';
}

function pickContentElement(document) {
	for (const selector of CONTENT_SELECTORS) {
		const el = document.querySelector(selector);
		if (el && el.innerHTML.trim().length > 50) {
			return el;
		}
	}
	for (const selector of CONTENT_SELECTORS) {
		const el = document.querySelector(selector);
		if (el) return el;
	}
	return null;
}

function cleanContent(document, contentEl) {
	contentEl.querySelectorAll('script, style, noscript').forEach((n) => n.remove());
	const clone = contentEl.cloneNode(true);
	const walker = clone.ownerDocument.createTreeWalker(clone, 4);
	const comments = [];
	while (walker.nextNode()) {
		if (walker.currentNode.nodeType === 8) comments.push(walker.currentNode);
	}
	comments.forEach((c) => c.remove());
	return clone;
}

function resolveUrl(href, pageUrl) {
	if (!href || href.startsWith('data:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
		return null;
	}
	try {
		return new URL(href, pageUrl).href;
	} catch {
		return null;
	}
}

function imageLocalPath(imageUrl) {
	try {
		const u = new URL(imageUrl);
		const uploadsMatch = u.pathname.match(/\/wp-content\/uploads\/(.+)/i);
		if (uploadsMatch) {
			return path.join('uploads', decodeURIComponent(uploadsMatch[1]));
		}
		const basename = path.basename(u.pathname);
		if (basename && IMAGE_EXT.test(basename)) {
			return path.join('external', basename);
		}
	} catch {
		/* ignore */
	}
	return null;
}

function collectImageUrls(contentEl, pageUrl) {
	const urls = new Set();
	contentEl.querySelectorAll('img').forEach((img) => {
		const src = resolveUrl(img.getAttribute('src'), pageUrl);
		if (src && IMAGE_EXT.test(src)) urls.add(src);
		const srcset = img.getAttribute('srcset');
		if (srcset) {
			srcset.split(',').forEach((part) => {
				const url = part.trim().split(/\s+/)[0];
				const resolved = resolveUrl(url, pageUrl);
				if (resolved && IMAGE_EXT.test(resolved)) urls.add(resolved);
			});
		}
	});
	contentEl.querySelectorAll('a[href]').forEach((a) => {
		const href = resolveUrl(a.getAttribute('href'), pageUrl);
		if (href && IMAGE_EXT.test(href)) urls.add(href);
	});
	return [...urls];
}

async function downloadImage(imageUrl, localRelPath) {
	const dest = path.join(IMAGES_DIR, localRelPath);
	await mkdir(path.dirname(dest), { recursive: true });
	try {
		const response = await fetchWithRetry(imageUrl);
		if (!response.ok) return false;
		const buffer = Buffer.from(await response.arrayBuffer());
		await writeFile(dest, buffer);
		return true;
	} catch {
		return false;
	}
}

function relativeImagePathFromPage(localRelPath, pageLocalDir) {
	const fromPage = path.join(PARSING_ROOT, pageLocalDir);
	const toImage = path.join(PARSING_ROOT, 'images', localRelPath);
	return path.relative(fromPage, toImage).split(path.sep).join('/');
}

async function processImages(contentEl, pageUrl, pageLocalDir) {
	const imageUrls = collectImageUrls(contentEl, pageUrl);
	const urlToLocal = new Map();
	const savedImages = [];

	for (const imageUrl of imageUrls) {
		const localRel = imageLocalPath(imageUrl);
		if (!localRel) continue;
		if (!urlToLocal.has(imageUrl)) {
			const ok = await downloadImage(imageUrl, localRel);
			if (ok) {
				urlToLocal.set(imageUrl, localRel);
				savedImages.push(`images/${localRel.replace(/\\/g, '/')}`);
			}
		}
	}

	const rewriteAttr = (el, attr) => {
		const val = el.getAttribute(attr);
		if (!val) return;
		if (attr === 'srcset') {
			const parts = val.split(',').map((part) => {
				const bits = part.trim().split(/\s+/);
				const resolved = resolveUrl(bits[0], pageUrl);
				const local = resolved ? urlToLocal.get(resolved) : null;
				if (local) {
					const rel = relativeImagePathFromPage(local, pageLocalDir);
					return bits.length > 1 ? `${rel} ${bits.slice(1).join(' ')}` : rel;
				}
				return part.trim();
			});
			el.setAttribute('srcset', parts.join(', '));
			return;
		}
		const resolved = resolveUrl(val, pageUrl);
		const local = resolved ? urlToLocal.get(resolved) : null;
		if (local) {
			el.setAttribute(attr, relativeImagePathFromPage(local, pageLocalDir));
		}
	};

	contentEl.querySelectorAll('img').forEach((img) => {
		rewriteAttr(img, 'src');
		rewriteAttr(img, 'srcset');
	});
	contentEl.querySelectorAll('a[href]').forEach((a) => {
		const href = resolveUrl(a.getAttribute('href'), pageUrl);
		if (href && urlToLocal.has(href)) {
			a.setAttribute('href', relativeImagePathFromPage(urlToLocal.get(href), pageLocalDir));
		}
	});

	return savedImages;
}

async function scrapePage(entry) {
	const response = await fetchWithRetry(entry.url);
	const html = await response.text();
	const dom = new JSDOM(html);
	const { document } = dom.window;

	const title = extractTitle(document);
	const contentEl = pickContentElement(document);
	if (!contentEl) {
		throw new Error('Content block not found');
	}

	const cleaned = cleanContent(document, contentEl);
	const pageLocalDir = localDirForEntry(entry);
	const pageDir = path.join(PARSING_ROOT, pageLocalDir);
	await mkdir(pageDir, { recursive: true });

	const images = await processImages(cleaned, entry.url, pageLocalDir);
	const contentHtml = cleaned.innerHTML.trim();

	await writeFile(path.join(pageDir, 'content.html'), contentHtml, 'utf8');

	const meta = {
		url: entry.url,
		path: entry.path || urlToPath(entry.url),
		type: entry.type,
		lastmod: entry.lastmod,
		title,
		scraped_at: new Date().toISOString(),
		images,
	};

	await writeFile(path.join(pageDir, 'meta.json'), `${JSON.stringify(meta, null, 2)}\n`, 'utf8');

	return { title, images_count: images.length, local_dir: pageLocalDir.replace(/\\/g, '/') };
}

async function main() {
	await ensureDirs();

	const raw = await readJson(path.join(DATA_DIR, 'urls-raw.json'));
	if (!raw?.entries?.length) {
		console.error('Run parse:sitemap first — urls-raw.json not found or empty');
		process.exit(1);
	}

	const progressPath = path.join(LOGS_DIR, 'scrape-progress.json');
	const failedPath = path.join(LOGS_DIR, 'failed.json');
	const progress = (await readJson(progressPath)) || { completed: {}, failed: {} };
	const failed = { ...(progress.failed || {}) };

	const entries = raw.entries;
	const total = entries.length;
	let done = 0;
	let skipped = 0;
	let errors = 0;

	console.log(`Scraping ${total} pages (delay ${DELAY_MS}ms)...`);

	for (const entry of entries) {
		if (progress.completed[entry.url]) {
			skipped++;
			continue;
		}

		done++;
		const pct = Math.round(((skipped + done) / total) * 100);
		process.stdout.write(`[${pct}%] ${entry.type} ${entry.path}... `);

		try {
			const result = await scrapePage(entry);
			progress.completed[entry.url] = {
				scraped_at: new Date().toISOString(),
				title: result.title,
				local_dir: result.local_dir,
				images_count: result.images_count,
			};
			console.log(`OK (${result.images_count} img)`);
		} catch (error) {
			errors++;
			failed[entry.url] = { error: error.message, at: new Date().toISOString() };
			console.log(`FAIL: ${error.message}`);
		}

		progress.failed = failed;
		await writeJson(progressPath, progress);

		if (done + skipped < total) {
			await sleep(DELAY_MS);
		}
	}

	await writeJson(failedPath, {
		generated_at: new Date().toISOString(),
		total_failed: Object.keys(failed).length,
		failed: Object.entries(failed).map(([url, data]) => ({ url, ...data })),
	});

	console.log(`\nDone. Scraped: ${Object.keys(progress.completed).length}, skipped: ${skipped}, errors: ${errors}`);
}

main().catch((error) => {
	console.error(error);
	process.exit(1);
});
