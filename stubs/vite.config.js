import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    build: {
        sourcemap: true
    },
    plugins: [
        tailwindcss(),
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
    server: {
        watch: {
            ignored: ['**/users/**'],
        },
    },
});
