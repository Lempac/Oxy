import {defineConfig} from 'vitest/config';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import {wayfinder} from "@laravel/vite-plugin-wayfinder";
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
        wayfinder(),
        laravel({
            input: 'resources/js/app.ts',
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
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
            "@/types": path.resolve(import.meta.dirname, './resources/js/types/index.d.ts')
        }
    },
    optimizeDeps: {
        exclude: []
    },
    ssr: {},
    test: {
        environment: 'jsdom',
        include: ['resources/js/**/*.spec.ts', 'resources/js/**/*.test.ts'],
        globals: true,
    }
});
