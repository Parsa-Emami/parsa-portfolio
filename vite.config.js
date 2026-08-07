import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/admin.css',
                'resources/js/admin.js',
                'resources/css/portfolio/global.scss',
                'resources/js/portfolio/app.js',
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `
                    @use "resources/css/portfolio/variables-scss/_breakpoints" as *;
                    @use "resources/css/portfolio/helpers/_mixins" as *;
                `
            }
        }
    }
});