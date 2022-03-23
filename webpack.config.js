const autoprefixer = require("autoprefixer");
const MiniCSSExtractPlugin = require("mini-css-extract-plugin");
const CSSMinimizerPlugin = require("css-minimizer-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");

const path = require("path");
const settings = path.join(__dirname, "src", "settings");

module.exports = (env, argv) => {
    function isDevelopment() {
        return argv.mode === "development";
    }
    var config = {
        entry: {
            settings,
        },
        output: {
            path: path.resolve(__dirname, "build"),
            filename: "[name].js",
            clean: true,
        },
        optimization: {
            minimizer: [
                new CSSMinimizerPlugin(),
                new TerserPlugin({ terserOptions: { sourceMap: true } }),
            ],
        },
        plugins: [
            new MiniCSSExtractPlugin({
                chunkFilename: "[id].css",
                filename: (chunkData) => {
                    return "[name].css";
                },
            }),
        ],
        devtool: isDevelopment() ? "cheap-module-source-map" : "source-map",
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    use: [
                        {
                            loader: "babel-loader",
                            options: {
                                presets: ["@babel/preset-env"],
                            },
                        },
                    ],
                },
                {
                    test: /\.(sa|sc|c)ss$/,
                    use: [
                        MiniCSSExtractPlugin.loader,
                        "css-loader",
                        {
                            loader: "postcss-loader",
                            options: {
                                postcssOptions: {
                                    plugins: [autoprefixer()],
                                },
                            },
                        },
                        "sass-loader",
                    ],
                },
            ],
        },
    };
    return config;
};
