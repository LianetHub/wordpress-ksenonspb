import gulp from 'gulp';
import {
	createFtpConnection,
	deployGlobs,
	ftpEnv,
	isFtpConfigured,
} from '../config/ftp.js';

const DEBOUNCE_MS = 400;
let pendingPaths = new Set();
let debounceTimer = null;
let deployQueue = Promise.resolve();

function log( message ) {
	console.log( `[FTP] ${ message }` );
}

function uploadPaths( paths ) {
	const conn = createFtpConnection();
	if ( ! conn ) {
		log( 'Credentials not configured, skipping deploy' );
		return Promise.resolve();
	}

	const uniquePaths = [ ...new Set( paths ) ].filter( Boolean );
	if ( ! uniquePaths.length ) {
		return Promise.resolve();
	}

	log( `Uploading ${ uniquePaths.length } file(s)...` );

	return new Promise( ( resolve, reject ) => {
		const stream = gulp
			.src( uniquePaths, { base: '.', buffer: false, allowEmpty: true } )
			.pipe( conn.dest( ftpEnv.FTP_REMOTE_PATH ) );

		stream.on( 'end', resolve );
		stream.on( 'error', reject );
		stream.on( 'finish', resolve );
	} );
}

function flushPendingUploads() {
	const paths = [ ...pendingPaths ];
	pendingPaths.clear();
	debounceTimer = null;

	if ( ! paths.length ) {
		return Promise.resolve();
	}

	deployQueue = deployQueue.then( () => uploadPaths( paths ) );
	return deployQueue;
}

export function queueFtpDeploy( paths ) {
	if ( ! app.isDev || ! isFtpConfigured() ) {
		return Promise.resolve();
	}

	const normalized = ( Array.isArray( paths ) ? paths : [ paths ] ).map( ( filePath ) =>
		filePath.replace( /\\/g, '/' )
	);

	normalized.forEach( ( filePath ) => pendingPaths.add( filePath ) );

	if ( debounceTimer ) {
		clearTimeout( debounceTimer );
	}

	return new Promise( ( resolve ) => {
		debounceTimer = setTimeout( () => {
			flushPendingUploads().then( resolve );
		}, DEBOUNCE_MS );
	} );
}

export function ftpDeployAll() {
	if ( ! isFtpConfigured() ) {
		log( 'Credentials not configured, skipping full deploy' );
		return Promise.resolve();
	}

	const conn = createFtpConnection();
	log( `Full deploy to ${ ftpEnv.FTP_REMOTE_PATH }` );

	return gulp
		.src( deployGlobs, { base: '.', buffer: false, allowEmpty: true } )
		.pipe( conn.dest( ftpEnv.FTP_REMOTE_PATH ) );
}

export function ftpDeployChanged( filePath ) {
	return queueFtpDeploy( [ filePath ] );
}

export function ftpDeployGlobs( globs ) {
	if ( ! app.isDev || ! isFtpConfigured() ) {
		return ( done ) => done();
	}

	return () => {
		const conn = createFtpConnection();
		return gulp
			.src( globs, { base: '.', buffer: false, allowEmpty: true } )
			.pipe( conn.dest( ftpEnv.FTP_REMOTE_PATH ) );
	};
}

export function withFtpDeploy( task, globs ) {
	return gulp.series( task, ftpDeployGlobs( globs ) );
}
