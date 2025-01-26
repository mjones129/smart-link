const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { PurgeCSSPlugin } = require('purgecss-webpack-plugin');
const glob = require('glob');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = {
    entry: {
        main: './js/copyPrivateLink.js',
        vendor: './node_modules/toastify-js/src/toastify.js',
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'dist'),
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env'],
                    },
                },
            },
            {
                test: /\.css$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader'],
            },
        ],
    },
    plugins: [
        new CleanWebpackPlugin(),
        new MiniCssExtractPlugin({
            filename: '[name].css',
        }),
        new PurgeCSSPlugin({
            paths: glob.sync(`${path.join(__dirname, '.')}/**/*`, { nodir: true }),
            safelist: {
                standard: [/^bg-/, /^text-/], // Adjust this according to your needs
            },
        }),
    ],
    optimization: {
        minimize: true,
    },
    mode: 'production',
};