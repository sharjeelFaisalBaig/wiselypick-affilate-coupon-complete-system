import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/drag-sort.js',
                'resources/js/offer-form.js',
                'resources/js/script-injection-form.js',
                'resources/js/blog-editor.js',
                'resources/js/faq-builder.js',
                'resources/js/homepage-section-form.js',
                'resources/js/homepage-section-picker.js',
                'resources/js/ajax-filters.js',
                'resources/js/slug-preview.js',
                'resources/js/image-dimension-check.js',
                'resources/js/blog-form.js',
                'resources/js/badge-form.js',
                'resources/js/table-row-filter.js',
                'resources/js/select2-init.js',
                'resources/js/autosuggest.js',
                'resources/js/scroll-reveal.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
                bunny('Plus Jakarta Sans', {
                    weights: [600, 700, 800],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
