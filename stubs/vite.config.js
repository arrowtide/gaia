import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        sourcemap: true
    },
    plugins: [
        laravel({
            input: [
                'resources/css/site.css',
                'resources/js/site.js',
                'resources/js/swiper.js',
                'resources/js/alpine.js',
            ],
            refresh: true,
        }),
    ],
});
