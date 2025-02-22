import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/filament/app/theme.css', 'resources/css/filament/admin/theme.css'],
            refresh: true,
            publicDirectory: 'public_html',
        }),
    ],
    build: {
        outDir: 'public_html/build',
    },
});
