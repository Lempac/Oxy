import './bootstrap';
import '../css/app.css';

import {createApp, DefineComponent, h} from 'vue';
import {createInertiaApp, router} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {OhVueIcon} from 'oh-vue-icons';
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
            .component("v-icon", OhVueIcon)
            .use(plugin)
            .mount(el);
        // Apply theme globally and listen for updates
        const updateTheme = (theme: ThemeType | null) => {
            document.documentElement.setAttribute('data-theme', theme || Themes.OXY_DARK);
        };

        // Initial application
        updateTheme(props.initialPage.props.user?.theme ?? Themes.OXY_DARK);

        // Listen for updates (including theme changes via profile update)
        router.on('success', (event) => {
            updateTheme(event.detail.page.props.user?.theme ?? Themes.OXY_DARK);
        });
    },
    progress: {
        color: '#4B5563',
    },
});
