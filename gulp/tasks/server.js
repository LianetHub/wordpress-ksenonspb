import { env } from '../config/env.js';

export const server = (done) => {
	app.plugins.browsersync.init({
		proxy: env.WP_PROXY_URL,
		notify: false,
		port: 3000,
	});
	done();
};
