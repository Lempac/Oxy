import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import inertia from '@inertiajs/vite'
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
        inertia(),
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
