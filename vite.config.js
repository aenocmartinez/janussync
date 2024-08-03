import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/tooltip.css',
                'resources/css/breadcrumb.css',
                'resources/css/monitoring.css',
                'resources/css/main.css',
                'resources/css/profile.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'node_modules')
        }
    }
});
