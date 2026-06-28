import { env } from '../config/env.js';

export const server = ( done ) => {
	const proxyTarget = env.WP_PROXY_URL;
	const proxyUrl = new URL( proxyTarget );
	const proxyHost = proxyUrl.host;
	const proxyHostPattern = proxyHost.replace( /\./g, '\\.' );

	app.plugins.browsersync.init(
		{
			proxy: {
				target: proxyTarget,
				proxyReq: [
					( proxyReq ) => {
						proxyReq.setHeader( 'host', proxyHost );
					},
				],
			},
			rewriteRules: [
				{
					match: new RegExp( `https?://${ proxyHostPattern }`, 'g' ),
					fn: () => 'http://localhost:3000',
				},
			],
			port: 3000,
			open: 'local',
			notify: true,
			ghostMode: false,
			reloadOnRestart: true,
		},
		done
	);
};
