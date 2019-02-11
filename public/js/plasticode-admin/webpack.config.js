'use strict';

const webpack = require('webpack');
const autoprefixer = require('autoprefixer');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = (env, argv) => ({
    output: {
        filename: argv.mode === 'production' ? 'plasticode-admin.min.js' : 'plasticode-admin.js'
    },
    module: {
        rules: [{
            test: /\.js$/,
            use: {
                loader: 'babel-loader',
                options: {
                    cacheDirectory: true,
                    presets: ['@babel/preset-env']
                }
            }
        },
            {
                test: /\.(sa|sc|c)ss$/,
                use: [
                    argv.mode === 'development' ? 'style-loader' : MiniCssExtractPlugin.loader,
                    {
                    loader: "css-loader", options: {minimize: argv.mode === 'production', sourceMap: true}
                }, {
                    loader: 'postcss-loader', options: {
                        sourceMap: true, plugins: [autoprefixer({
                            browsers: ['last 4 version']
                        })],
                    },
                }, {
                    loader: "sass-loader", options: {sourceMap: true, outputStyle: 'expanded'}
                }]
            }]
    },
    plugins: [
        new webpack.ProvidePlugin({'SimpleMDE': 'simplemde',}),
        new MiniCssExtractPlugin()
    ]
});
