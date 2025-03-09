import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/css/app.css', 
                'resources/css/payment.css', 
                'resources/css/status.css', 
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
