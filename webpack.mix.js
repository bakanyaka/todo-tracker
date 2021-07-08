let mix = require('laravel-mix');
let webpack = require('webpack');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */


mix.js('resources/js/main.js', 'public/js')
  .vue()
  .extract()
  .sass('resources/sass/style.scss', 'public/css')
  .sass('resources/sass/vendor.scss', 'public/css')
  .sourceMaps()
  .webpackConfig(require('./webpack.config'));

if (mix.inProduction()) {
  mix.version();
}
//
// if (!mix.inProduction()) {
//     mix.browserSync('https://todo-tracker.test');
// }

