import ftp from 'vinyl-ftp';
import { env } from './env.js';

export const assetsDeployGlobs = [ 'assets/**/*.*' ];

// Полный деплой (build / deploy:ftp). PHP и шаблоны — через .vscode/sftp.json в dev.
export const deployGlobs = [
	...assetsDeployGlobs,
	'inc/**/*.*',
	'template-parts/**/*.*',
	'*.php',
	'style.css',
	'screenshot.png',
];

export function isFtpConfigured() {
	return Boolean( env.FTP_HOST && env.FTP_USER && env.FTP_PASSWORD );
}

export function createFtpConnection() {
	if ( ! isFtpConfigured() ) {
		return null;
	}

	// Shared-хостинг часто рвёт PASV при нескольких data-соединениях —
	// держим одну сессию, иначе падает на крупных файлах (шрифты).
	return ftp.create( {
		host: env.FTP_HOST,
		user: env.FTP_USER,
		password: env.FTP_PASSWORD,
		port: env.FTP_PORT,
		parallel: 1,
		maxConnections: 1,
		idleTimeout: 60_000,
		pasvTimeout: 30_000,
		connTimeout: 30_000,
		log: ( ...args ) => console.log( '[FTP]', ...args ),
	} );
}

export { env as ftpEnv };
