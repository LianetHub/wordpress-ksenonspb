import ftp from 'vinyl-ftp';
import { env } from './env.js';

export const deployGlobs = [
	'assets/**/*.*',
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

	return ftp.create( {
		host: env.FTP_HOST,
		user: env.FTP_USER,
		password: env.FTP_PASSWORD,
		port: env.FTP_PORT,
		parallel: 3,
		maxConnections: 3,
		idleTimeout: 60_000,
		log: ( ...args ) => console.log( '[FTP]', ...args ),
	} );
}

export { env as ftpEnv };
