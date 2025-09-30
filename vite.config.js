import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/css/app.css',
                'resources/css/auth.css',
                'resources/css/profile.css',
            ],
            refresh: true,
        }),
    ],
});
