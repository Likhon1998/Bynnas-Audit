import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            // Only watch source files. Watching storage/framework/views caused a
            // full-page reload loop on every Livewire/Blade compile → UI froze.
            refresh: [
                'resources/views/**',
                'resources/js/**',
                'routes/**',
                'app/Livewire/**',
                'app/View/**',
            ],
        }),
    ],
    server: {
        watch: {
            ignored: [
                '**/storage/**',
                '**/vendor/**',
                '**/node_modules/**',
                '**/public/build/**',
            ],
        },
    },
});
