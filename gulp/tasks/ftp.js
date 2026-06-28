import gulp from 'gulp';
import {
	assetsDeployGlobs,
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

const FTP_DEST_OPTIONS = { parallel: 1 };

function streamToPromise( stream ) {
	return new Promise( ( resolve, reject ) => {
		stream.once( 'error', reject );
		stream.once( 'finish', resolve );
	} );
}

function deployToFtp( globs, label ) {
	const conn = createFtpConnection();
	if ( ! conn ) {
		return Promise.resolve();
	}

	if ( label ) {
		log( label );
	}

	const stream = gulp
		.src( globs, { base: '.', allowEmpty: true } )
		.pipe( conn.dest( ftpEnv.FTP_REMOTE_PATH, FTP_DEST_OPTIONS ) );

	return streamToPromise( stream );
}

function reloadBrowserIfActive() {
	if ( app.plugins.browsersync.active ) {
		app.plugins.browsersync.reload();
	}
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

	const stream = gulp
		.src( uniquePaths, { base: '.', allowEmpty: true } )
		.pipe( conn.dest( ftpEnv.FTP_REMOTE_PATH, FTP_DEST_OPTIONS ) );

	return streamToPromise( stream ).then( () => {
		reloadBrowserIfActive();
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

	log( `Full deploy to ${ ftpEnv.FTP_REMOTE_PATH }` );

	return deployToFtp( deployGlobs ).then( () => {
		log( 'Full deploy complete' );
	} );
}

export function ftpDeployAssets() {
	if ( ! isFtpConfigured() ) {
		log( 'Credentials not configured, skipping assets deploy' );
		return Promise.resolve();
	}

	log( `Assets deploy to ${ ftpEnv.FTP_REMOTE_PATH }` );

	return deployToFtp( assetsDeployGlobs ).then( () => {
		log( 'Assets deploy complete' );
	} );
}

export function ftpDeployChanged( filePath ) {
	return queueFtpDeploy( [ filePath ] );
}

export function ftpDeployGlobs( globs, { reload = false } = {} ) {
	if ( ! app.isDev || ! isFtpConfigured() ) {
		return ( done ) => done();
	}

	return () =>
		deployToFtp( globs ).then( () => {
			if ( reload ) {
				reloadBrowserIfActive();
			}
		} );
}

export function withFtpDeploy( task, globs, options = {} ) {
	return gulp.series( task, ftpDeployGlobs( globs, options ) );
}
