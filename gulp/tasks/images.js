// Gulp 5 по умолчанию читает файлы как UTF-8 — без encoding: false webp/png ломаются.
const binarySrcOptions = {
	allowEmpty: true,
	encoding: false,
};

export const images = () => {
	return app.gulp
		.src(app.path.src.images, binarySrcOptions)
		.pipe(
			app.plugins.plumber(
				app.plugins.notify.onError({
					title: 'Images',
					message: 'Error: <%= error.message %>',
				}),
			),
		)
		.pipe(app.gulp.dest(app.path.build.images));
};

export const favicon = () => {
	return app.gulp
		.src(app.path.src.favicon, binarySrcOptions)
		.pipe(app.gulp.dest(app.path.build.favicon));
};
