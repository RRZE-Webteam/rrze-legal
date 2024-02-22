const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const webpack = require("webpack");
const { basename, dirname, resolve } = require("path");
const srcDir = "src";

const settings = resolve(process.cwd(), "src", "settings");
const tos = resolve(process.cwd(), "src", "tos");
const consent = resolve(process.cwd(), "src", "consent");
const banner = resolve(process.cwd(), "src", "banner");
const prioritize = resolve(process.cwd(), "src", "prioritize");

module.exports = {
    ...defaultConfig,
    entry: {
        settings,
        tos,
        consent,
        banner,
        prioritize,
    },
    output: {
        path: resolve(process.cwd(), "build"),
        filename: "[name].js",
        clean: true,
    },
    optimization: {
        ...defaultConfig.optimization,
        splitChunks: {
            cacheGroups: {
                style: {
                    type: "css/mini-extract",
                    test: /[\\/]style(\.module)?\.(pc|sc|sa|c)ss$/,
                    chunks: "all",
                    enforce: true,
                    name(_, chunks, cacheGroupKey) {
                        const chunkName = chunks[0].name;
                        return `${dirname(chunkName)}/${basename(
                            chunkName
                        )}.${cacheGroupKey}`;
                    },
                },
                default: false,
            },
        },
    },
    plugins: [
        ...defaultConfig.plugins,
        new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery",
        }),
    ],
};
