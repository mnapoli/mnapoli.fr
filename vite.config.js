import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    define: {
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: false,
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/editor.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
