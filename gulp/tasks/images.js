export const images = () => {
	return app.gulp
		.src( app.path.src.images, { allowEmpty: true, encoding: false } )
		.pipe(
			app.plugins.plumber(
				app.plugins.notify.onError( {
					title: 'Images',
					message: 'Error: <%= error.message %>',
				} )
			)
		)
		.pipe( app.gulp.dest( app.path.build.images ) )
		.pipe( app.plugins.browsersync.stream() );
};

export const favicon = () => {
	return app.gulp.src( app.path.src.favicon ).pipe( app.gulp.dest( app.path.build.favicon ) );
};
