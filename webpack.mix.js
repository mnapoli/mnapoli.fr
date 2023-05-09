const mix = require('laravel-mix');

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

mix.js('resources/js/app.js', 'public/assets/js');

mix.postCss('resources/css/app.css', 'public/assets/css', [
    // Include the TailwindCSS plugin
    require('tailwindcss'),
]);

mix.disableSuccessNotifications();

if (mix.inProduction()) {
    // Enable asset versioning in production
    mix.version();
}
