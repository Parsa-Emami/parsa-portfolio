import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ command }) => ({
    // این خط بسیار مهم است: تنظیم مسیر پایه برای فایل‌های خروجی در گیت‌هاب پیجز
    base: command === 'build' ? '/parsa-portfolio/build/' : '',
    
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
                // تزریق خودکار فایل‌های متغیر و میکسین به تمام فایل‌های SCSS
                additionalData: `
                    @use "resources/css/portfolio/variables-scss/_breakpoints" as *;
                    @use "resources/css/portfolio/helpers/_mixins" as *;
                `
            }
        }
    }
}));