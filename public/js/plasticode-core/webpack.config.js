'use strict';

module.exports = (env, argv) => ({
    output: {
        filename: argv.mode === 'production' ? 'plasticode-core.min.js' : 'plasticode-core.js'
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
        }]
    }
});
