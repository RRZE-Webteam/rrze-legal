const defaults = require("@wordpress/scripts/config/webpack.config");

/**
 * WP-Scripts Webpack config.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-scripts/#provide-your-own-webpack-config
 */
module.exports = {
    ...defaults,
    entry: {
        settings: "./src/settings/index.js",
        tos: "./src/tos/index.js",
        consent: "./src/consent/index.js",
        banner: "./src/banner/index.js",
        prioritize: "./src/prioritize/index.js",
    },
};
