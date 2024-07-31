import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/breadcrumb.css',
                'resources/css/main.css',
                'resources/css/profile.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
