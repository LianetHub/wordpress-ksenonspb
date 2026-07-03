import { writeFile } from 'node:fs/promises';
import path from 'node:path';
import {
	DATA_DIR,
	SITEMAP_DIR,
	SITEMAP_URL,
	ensureDirs,
	fetchWithRetry,
	isDemoPage,
	isLegacyServicePage,
	makePageId,
	parseSitemapUrls,
	typeFromSitemapFile,
	urlToPath,
	writeJson,
} from './utils.mjs';

async function downloadSitemapIndex() {
	const response = await fetchWithRetry(SITEMAP_URL);
	const xml = await response.text();
	const indexPath = path.join(SITEMAP_DIR, 'sitemap.xml');
	await writeFile(indexPath, xml, 'utf8');
	return xml;
}

function extractSubSitemapUrls(indexXml) {
	const urls = [];
	const re = /<loc>([^<]+)<\/loc>/g;
	let match;
	while ((match = re.exec(indexXml)) !== null) {
		const loc = match[1].trim();
		if (loc.endsWith('.xml') && loc.includes('sitemap')) {
			urls.push(loc);
		}
	}
	return urls;
}

async function downloadSubSitemap(sitemapUrl) {
	const filename = path.basename(new URL(sitemapUrl).pathname);
	const filePath = path.join(SITEMAP_DIR, filename);

	try {
		const response = await fetchWithRetry(sitemapUrl);
		const xml = await response.text();
		await writeFile(filePath, xml, 'utf8');
		return { filename, xml, ok: true };
	} catch (error) {
		console.error(`  FAIL ${filename}: ${error.message}`);
		return { filename, xml: '', ok: false, error: error.message };
	}
}

function enrichEntry(raw, type) {
	const pathname = urlToPath(raw.url);
	const slug = pathname.replace(/^\/+|\/+$/g, '') || 'home';

	return {
		id: makePageId(type, pathname),
		url: raw.url,
		path: pathname,
		slug,
		type,
		lastmod: raw.lastmod,
		priority: raw.priority,
		sitemap_file: raw.sitemap_file,
		is_demo: isDemoPage(pathname),
		is_legacy_service: isLegacyServicePage(pathname),
	};
}

async function main() {
	console.log('Fetching sitemap index...');
	await ensureDirs();

	const indexXml = await downloadSitemapIndex();
	const subUrls = extractSubSitemapUrls(indexXml);
	console.log(`Found ${subUrls.length} sub-sitemaps`);

	const allRaw = [];
	const failedSitemaps = [];

	for (const subUrl of subUrls) {
		const filename = path.basename(new URL(subUrl).pathname);
		process.stdout.write(`  ${filename}... `);
		const result = await downloadSubSitemap(subUrl);
		if (!result.ok) {
			failedSitemaps.push({ url: subUrl, filename, error: result.error });
			console.log('FAILED');
			continue;
		}
		const type = typeFromSitemapFile(filename);
		const urls = parseSitemapUrls(result.xml, filename);
		console.log(`${urls.length} URLs`);
		for (const u of urls) {
			allRaw.push(enrichEntry(u, type));
		}
	}

	const byUrl = new Map();
	for (const entry of allRaw) {
		byUrl.set(entry.url, entry);
	}
	const entries = [...byUrl.values()].sort((a, b) => a.url.localeCompare(b.url));

	const byType = {};
	for (const entry of entries) {
		byType[entry.type] = (byType[entry.type] || 0) + 1;
	}

	const output = {
		generated_at: new Date().toISOString(),
		source: SITEMAP_URL,
		total: entries.length,
		by_type: byType,
		failed_sitemaps: failedSitemaps,
		entries,
	};

	await writeJson(path.join(DATA_DIR, 'urls-raw.json'), output);

	console.log('\nDone.');
	console.log(`Total unique URLs: ${entries.length}`);
	console.log('By type:', byType);
	if (failedSitemaps.length) {
		console.log(`Failed sitemaps: ${failedSitemaps.length}`);
	}
}

main().catch((error) => {
	console.error(error);
	process.exit(1);
});
