import fs from 'fs';
import path from 'path';
import through2 from 'through2';
import fonter from 'gulp-fonter';
import ttf2woff from 'ttf2woff';
import ttf2woff2 from 'ttf2woff2';

const fontPlumber = () =>
	app.plugins.plumber(
		app.plugins.notify.onError( {
			title: 'FONTS',
			message: 'Error: <%= error.message %>',
		} )
	);

function srcFontsByExt( ...extensions ) {
	const allowed = new Set( extensions.map( ( ext ) => ext.toLowerCase() ) );

	return app.gulp
		.src( `${ app.path.srcFolder }/fonts/*`, { allowEmpty: true, read: false } )
		.pipe(
			through2.obj( function ( file, _, cb ) {
				if ( allowed.has( path.extname( file.path ).toLowerCase() ) ) {
					this.push( file );
				}
				cb();
			} )
		);
}

function convertFont( converter, outputExt ) {
	return through2.obj( function ( file, _, cb ) {
		try {
			const input = fs.readFileSync( file.path );
			const output = converter( input );
			file.contents = Buffer.isBuffer( output ) ? output : Buffer.from( output.buffer );
			file.path = file.path.replace( path.extname( file.path ), outputExt );
			this.push( file );
			cb();
		} catch ( error ) {
			cb( error );
		}
	} );
}

export const otf2ttf = () => {
	return app.gulp
		.src( `${ app.path.srcFolder }/fonts/*.otf`, { allowEmpty: true } )
		.pipe( fontPlumber() )
		.pipe(
			fonter( {
				formats: [ 'ttf' ],
			} )
		)
		.pipe( app.gulp.dest( `${ app.path.srcFolder }/fonts/` ) );
};

export const ttfToWoff = () => {
	return srcFontsByExt( '.ttf' )
		.pipe( fontPlumber() )
		.pipe( convertFont( ttf2woff, '.woff' ) )
		.pipe( app.gulp.dest( `${ app.path.build.fonts }` ) );
};

export const ttfToWoff2 = () => {
	return srcFontsByExt( '.ttf' )
		.pipe( fontPlumber() )
		.pipe( convertFont( ttf2woff2, '.woff2' ) )
		.pipe( app.gulp.dest( `${ app.path.build.fonts }` ) );
};

export const copyWoff = () => {
	return srcFontsByExt( '.woff', '.woff2' ).pipe(
		app.gulp.dest( `${ app.path.build.fonts }` )
	);
};

export const fontsStyle = () => {
	const fontsFile = `${ app.path.srcFolder }/scss/fonts.scss`;

	if ( fs.existsSync( fontsFile ) ) {
		const content = fs.readFileSync( fontsFile, 'utf8' );
		if ( content.includes( '// Inter — локальные' ) ) {
			return app.gulp.src( `${ app.path.srcFolder }` );
		}
	}

	fs.readdir( app.path.build.fonts, function ( err, fontsFiles ) {
		if ( ! fontsFiles ) {
			return;
		}

		if ( fs.existsSync( fontsFile ) ) {
			fs.unlinkSync( fontsFile );
			console.log( 'Файл scss/fonts.scss актуализирован' );
		} else {
			console.log( 'Файл scss/fonts.scss создан' );
		}

		fs.writeFile( fontsFile, '', cb );
		let newFileOnly;

		for ( let i = 0; i < fontsFiles.length; i++ ) {
			const ext = fontsFiles[ i ].slice( fontsFiles[ i ].lastIndexOf( '.' ) );
			if ( ext !== '.woff' && ext !== '.woff2' ) {
				continue;
			}

			const fontFileNameWithExtension = fontsFiles[ i ].replace( ext, '' );
			let fontFileName = fontFileNameWithExtension;

			const isVariableFont = fontFileName.toLowerCase().includes( 'variablefont_' );

			if ( isVariableFont ) {
				fontFileName = fontFileName
					.replace( /-VariableFont_wght/i, '' )
					.replace( /-VariableFont_opsz,wght/i, '' );
			}

			if ( newFileOnly !== fontFileName ) {
				let fontName = fontFileName.split( '-' )[ 0 ] ? fontFileName.split( '-' )[ 0 ] : fontFileName;
				let fontWeight = fontFileName.split( '-' )[ 1 ] || '';
				let fontStyle = 'normal';

				if ( ! isVariableFont ) {
					if ( fontWeight.toLowerCase().includes( 'italic' ) ) {
						fontStyle = 'italic';
						fontWeight = fontWeight.replace( /italic/i, '' ).trim();
					}

					switch ( fontWeight.toLowerCase() ) {
						case 'thin':
							fontWeight = 100;
							break;
						case 'extralight':
							fontWeight = 200;
							break;
						case 'light':
							fontWeight = 300;
							break;
						case 'book':
							fontWeight = 450;
							break;
						case 'medium':
							fontWeight = 500;
							break;
						case 'semibold':
						case 'demi':
							fontWeight = 600;
							break;
						case 'bold':
							fontWeight = 700;
							break;
						case 'extrabold':
						case 'heavy':
							fontWeight = 800;
							break;
						case 'black':
							fontWeight = 900;
							break;
						default:
							fontWeight = 400;
							break;
					}
				}

				if ( isVariableFont ) {
					fs.appendFile(
						fontsFile,
						`@font-face {
								font-family: '${ fontName }';
								src: url("../fonts/${ fontFileNameWithExtension }.woff2") format("woff2 supports variations"),
									url("../fonts/${ fontFileNameWithExtension }.woff2") format("woff2-variations"),
									url("../fonts/${ fontFileNameWithExtension }.woff") format("woff");
								font-weight: 100 900;
								font-stretch: 75% 125%;
								font-style: normal;
								font-display: swap;
							}\r\n`,
						cb
					);
				} else {
					fs.appendFile(
						fontsFile,
						`@font-face {
								font-family: '${ fontName }';
								font-display: swap;
								src: url("../fonts/${ fontFileNameWithExtension }.woff2") format("woff2"), url("../fonts/${ fontFileNameWithExtension }.woff") format("woff");
								font-weight: ${ fontWeight };
								font-style: ${ fontStyle };
							}\r\n`,
						cb
					);
				}

				newFileOnly = fontFileName;
			}
		}
	} );

	return app.gulp.src( `${ app.path.srcFolder }` );
	function cb() {}
};
