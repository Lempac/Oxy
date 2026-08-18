import './bootstrap';
import '../css/app.css';

import {createApp, DefineComponent, h} from 'vue';
import {createInertiaApp, router} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import VueKonva from 'vue-konva';
import {Themes, ThemeType} from "@/types";
import {configureEcho} from '@laravel/echo-vue';

configureEcho({
    broadcaster: 'reverb',
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
    setup({el, App, props, plugin}) {
        createApp({render: () => h(App, props)})
            .use(VueKonva)
            .use(plugin)
            .mount(el);

        // Apply theme globally according to user preferences and browser dark/light mode
        type UserThemeProps = { light_theme?: ThemeType; dark_theme?: ThemeType } | null;
        let currentUserTheme: UserThemeProps = (props.initialPage?.props as { user?: UserThemeProps })?.user || null;

        const DARK_THEMES = new Set([
            'dark',
            'synthwave',
            'halloween',
            'forest',
            'black',
            'luxury',
            'dracula',
            'business',
            'night',
            'coffee',
            'dim',
            'sunset',
        ]);

        const updateMetaColorScheme = (isDark: boolean) => {
            let meta = document.querySelector('meta[name="color-scheme"]') as HTMLMetaElement | null;
            if (!meta) {
                meta = document.createElement('meta');
                meta.name = 'color-scheme';
                document.head.appendChild(meta);
            }
            meta.content = isDark ? 'dark' : 'light';
        };

        const applyTheme = () => {
            const isDarkSystem = !!(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
            const theme = isDarkSystem
                ? (currentUserTheme?.dark_theme || Themes.DARK)
                : (currentUserTheme?.light_theme || Themes.OXY);

            const isDarkTheme = DARK_THEMES.has(theme) || (isDarkSystem && theme === currentUserTheme?.dark_theme);

            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.style.colorScheme = isDarkTheme ? 'dark' : 'light';
            document.documentElement.classList.toggle('dark', isDarkTheme);
            document.documentElement.classList.toggle('light', !isDarkTheme);
            updateMetaColorScheme(isDarkTheme);
        };

        // Initial theme application
        applyTheme();

        // Listen for browser/system color scheme changes
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            if (mediaQuery.addEventListener) {
                mediaQuery.addEventListener('change', applyTheme);
            } else if ('addListener' in mediaQuery) {
                (mediaQuery as any).addListener(applyTheme);
            }
        }

        // Listen for updates (including theme changes via profile update)
        router.on('success', (event) => {
            currentUserTheme = (event.detail.page.props as { user?: UserThemeProps }).user || null;
            applyTheme();
        });
    },
    progress: {
        color: '#4B5563',
    },
});
