import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/sass/app.scss', 
                'resources/sass/payment.scss', 
                'resources/sass/status.scss', 
                'resources/js/app.js',
                'resources/js/status.js'
            ],
            refresh: true,
        }),
    ],
});
