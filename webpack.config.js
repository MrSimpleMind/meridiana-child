const path = require('path');

module.exports = (env, argv) => {
    const isProd = argv.mode === 'production';

    return {
        entry: {
            main: path.resolve(__dirname, 'assets/js/src/index.js'),
            analitiche: path.resolve(__dirname, 'assets/js/src/analitiche.js'),
        },
        output: {
            path: path.resolve(__dirname, 'assets/js/dist'),
            filename: '[name].min.js',
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
        plugins: [ // Aggiungi questa sezione
            // OLD: new webpack.ProvidePlugin({ Alpine: 'alpinejs' }), // Rimuovi questa riga
        ],
    };
};
