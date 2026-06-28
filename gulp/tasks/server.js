import { env } from '../config/env.js';

export const server = ( done ) => {
	const proxyTarget = env.WP_PROXY_URL;
	const proxyHost = new URL( proxyTarget ).host;

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
			port: 3000,
			open: 'local',
			notify: true,
			ghostMode: false,
			reloadOnRestart: true,
		},
		done
	);
};
