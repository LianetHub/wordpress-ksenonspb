import gulp from 'gulp';

import { path } from './gulp/config/path.js';
import { plugins } from './gulp/config/plugins.js';

global.app = {
	isBuild: process.argv.includes( '--build' ),
	isDev: ! process.argv.includes( '--build' ),
	path,
	gulp,
	plugins,
};

import { copy } from './gulp/tasks/copy.js';
import { reset } from './gulp/tasks/reset.js';
import { server } from './gulp/tasks/server.js';
import { scss, scssEntries, copyCssLibs, normalize } from './gulp/tasks/scss.js';
import { js, copyJsLibs, jsChunks } from './gulp/tasks/js.js';
import { images, favicon } from './gulp/tasks/images.js';
import {
	otf2ttf,
	ttfToWoff,
	copyWoff,
	fontsStyle,
} from './gulp/tasks/fonts.js';
import { zip } from './gulp/tasks/zip.js';
import { json } from './gulp/tasks/json.js';
import {
	ftpDeployAll,
	ftpDeployChanged,
	withFtpDeploy,
} from './gulp/tasks/ftp.js';

function watcher() {
	gulp.watch(
		path.watch.files,
		withFtpDeploy( copy, `${ path.build.files }**/*` )
	);
	gulp.watch(
		path.watch.scss,
		withFtpDeploy( gulp.parallel( scss, scssEntries ), `${ path.build.css }**/*` )
	);
	gulp.watch(
		path.watch.normalize,
		withFtpDeploy( normalize, `${ path.build.normalize }**/*` )
	);
	gulp.watch(
		path.watch.js,
		withFtpDeploy( js, `${ path.build.js }**/*` )
	);
	gulp.watch(
		path.watch.json,
		withFtpDeploy( json, `${ path.build.json }**/*` )
	);
	gulp.watch(
		path.watch.images,
		withFtpDeploy( images, `${ path.build.images }**/*` )
	);
	gulp.watch(
		path.watch.fonts,
		withFtpDeploy( fonts, `${ path.build.fonts }**/*` )
	);
	gulp.watch( path.watch.php ).on( 'change', ( filePath ) => {
		return ftpDeployChanged( filePath ).then( () => {
			app.plugins.browsersync.reload();
		} );
	} );
}

const fonts = gulp.series( otf2ttf, ttfToWoff, copyWoff, fontsStyle );

const assetsTasks = gulp.parallel(
	copy,
	normalize,
	scss,
	scssEntries,
	copyCssLibs,
	favicon,
	js,
	copyJsLibs,
	jsChunks,
	json,
	images
);

const mainTasks = gulp.series( fonts, assetsTasks );

const dev       = gulp.series(
	reset,
	mainTasks,
	ftpDeployAll,
	gulp.parallel( watcher, server )
);
const build     = gulp.series( reset, mainTasks );
const deployZIP = gulp.series( reset, mainTasks, zip );

export { dev };
export { build };
export { deployZIP };
export { ftpDeployAll };

gulp.task( 'default', dev );
gulp.task( 'build', build );
gulp.task( 'deployZIP', deployZIP );
gulp.task( 'ftpDeployAll', ftpDeployAll );
