const path = require('path');

module.exports = (env, argv) => {
    const isProd = argv.mode === 'production';

    return {
        entry: {
            main: path.resolve(__dirname, 'assets/js/src/index.js'),
        },
        output: {
            path: path.resolve(__dirname, 'assets/js/dist'),
            filename: 'main.min.js',
            clean: true,
        },
        mode: isProd ? 'production' : 'development',
        devtool: isProd ? false : 'source-map',
        module: {
            rules: [],
        },
        resolve: {
            extensions: ['.js'],
        },
        optimization: {
            minimize: isProd,
        },
    };
};
