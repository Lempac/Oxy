import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import {wayfinder} from "@laravel/vite-plugin-wayfinder";

// /** @type {import('vite').UserConfig} */
export default defineConfig({
    plugins: [
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
    resolve: {
        alias: {
            "@/types": path.resolve(__dirname, './resources/js/types/index.d.ts')
        }
    },
    optimizeDeps: {
        exclude: ["oh-vue-icons/icons"]
    },
    ssr: {
        noExternal: ["oh-vue-icons"]
    }
});
