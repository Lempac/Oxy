import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';

/** @type {import('vite').UserConfig} */
export default defineConfig({
    plugins: [
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
    resolve: {
        alias: {
            "@/types": path.resolve(__dirname, './resources/js/types/index.d.ts')
        }
    },
    server:{
        watch: {
            ignored: [ "**/.devenv/**" ],
        }
    }
});
