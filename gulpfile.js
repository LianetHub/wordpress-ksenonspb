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
	ttfToWoff2,
	copyWoff,
	fontsStyle,
} from './gulp/tasks/fonts.js';
import { zip } from './gulp/tasks/zip.js';
import { json } from './gulp/tasks/json.js';
import {
	ftpDeployAll,
	ftpDeployAssets,
	withFtpDeploy,
} from './gulp/tasks/ftp.js';

const devScssTask = gulp.parallel( scss, scssEntries );

const devCssDeployGlobs = `${ path.build.css }**/*`;

const noReload = { reload: false };

function watcher() {
	// PHP, inc, template-parts — через .vscode/sftp.json
	gulp.watch(
		path.watch.scss,
		withFtpDeploy( devScssTask, devCssDeployGlobs, noReload )
	);
	gulp.watch(
		path.watch.normalize,
		withFtpDeploy(
			normalize,
			[
				`${ path.build.normalize }reset.css`,
				`${ path.build.normalize }reset.min.css`,
			],
			noReload
		)
	);
	gulp.watch(
		path.watch.js,
		withFtpDeploy( js, `${ path.build.js }**/*`, noReload )
	);
	gulp.watch(
		path.watch.json,
		withFtpDeploy( json, `${ path.build.json }**/*`, noReload )
	);
	gulp.watch(
		path.watch.images,
		withFtpDeploy( images, `${ path.build.images }**/*`, { reload: true } )
	);
	gulp.watch(
		path.watch.fonts,
		withFtpDeploy( fonts, `${ path.build.fonts }**/*`, { reload: true } )
	);
}

const fonts = gulp.series(
	otf2ttf,
	gulp.parallel( ttfToWoff, ttfToWoff2 ),
	copyWoff,
	fontsStyle
);

const devAssetsTasks = gulp.parallel(
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

const buildAssetsTasks = gulp.parallel(
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

const assetsTasks = app.isDev ? devAssetsTasks : buildAssetsTasks;

const mainTasks = gulp.series( fonts, assetsTasks );

const dev = gulp.series(
	reset,
	mainTasks,
	ftpDeployAssets,
	gulp.parallel( watcher, server )
);
const build = gulp.series( reset, mainTasks );
const deployZIP = gulp.series( reset, mainTasks, zip );

export { dev };
export { build };
export { deployZIP };
export { ftpDeployAll };

gulp.task( 'default', dev );
gulp.task( 'build', build );
gulp.task( 'deployZIP', deployZIP );
gulp.task( 'ftpDeployAll', ftpDeployAll );
