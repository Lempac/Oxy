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
            "@": path.resolve(import.meta.dirname, "./src"),
            "vue": "vue/dist/vue.esm-bundler.js"
        }
    },
    optimizeDeps: {
        exclude: []
    },
    test: {
        environment: 'jsdom',
        include: ['src/**/*.spec.ts', 'src/**/*.test.ts'],
        globals: true,
    }
});
