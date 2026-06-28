export const json = () => {
	return app.gulp.src( app.path.src.json, { allowEmpty: true } )
		.pipe( app.gulp.dest( app.path.build.json ) )
		.pipe( app.plugins.browsersync.stream() );
};