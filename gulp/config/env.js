import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, '../..');
const envPath = path.join(projectRoot, '.env');

function loadEnvFile() {
	const vars = {};
	if (!fs.existsSync(envPath)) {
		return vars;
	}
	const content = fs.readFileSync(envPath, 'utf8');
	content.split('\n').forEach((line) => {
		const trimmed = line.trim();
		if (!trimmed || trimmed.startsWith('#')) return;
		const eq = trimmed.indexOf('=');
		if (eq === -1) return;
		const key = trimmed.slice(0, eq).trim();
		let value = trimmed.slice(eq + 1).trim();
		if (
			(value.startsWith('"') && value.endsWith('"')) ||
			(value.startsWith("'") && value.endsWith("'"))
		) {
			value = value.slice(1, -1);
		}
		vars[key] = value;
	});
	return vars;
}

const fileEnv = loadEnvFile();

export const env = {
	WP_PROXY_URL:
		process.env.WP_PROXY_URL ||
		fileEnv.WP_PROXY_URL ||
		'http://wordpress-ksenonspb.local',
};
