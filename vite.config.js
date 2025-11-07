import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/alpine-globals.js', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss({
            darkMode: 'class', // 👈 Force class-based dark mode
        }),    ],
    server: {
        cors: true,
    },
});
