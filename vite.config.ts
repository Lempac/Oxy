import {defineConfig} from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        cors: true,
        host: '0.0.0.0',
        hmr: {
            host: 'localhost',
        },
    },
    resolve: {
        alias: {
            "@": path.resolve(import.meta.dirname, "./resources/js"),
            "@inertiajs/vue3": path.resolve(import.meta.dirname, "./resources/js/inertia-compat.ts"),
            "@inertiajs/core": path.resolve(import.meta.dirname, "./resources/js/inertia-compat.ts")
        }
    },
    optimizeDeps: {
        exclude: []
    },
    test: {
        environment: 'jsdom',
        include: ['resources/js/**/*.spec.ts', 'resources/js/**/*.test.ts'],
        globals: true,
    }
});
