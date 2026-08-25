import './bootstrap';
import './css/app.css';

import { createApp, h } from 'vue';
import { RouterView } from 'vue-router';
import VueKonva from 'vue-konva';
import router from '@/router';

const app = createApp({
    render: () => h(RouterView),
});

app.use(VueKonva);
app.use(router);

app.mount('#app');

// Apply theme globally according to user preferences and browser dark/light mode
const applyTheme = () => {
    const isDarkSystem = !!(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
    const theme = isDarkSystem ? 'dark' : 'oxy';
    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.style.colorScheme = isDarkSystem ? 'dark' : 'light';
    document.documentElement.classList.toggle('dark', isDarkSystem);
    document.documentElement.classList.toggle('light', !isDarkSystem);
};

applyTheme();

if (typeof window !== 'undefined' && window.matchMedia) {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    if (mediaQuery.addEventListener) {
        mediaQuery.addEventListener('change', applyTheme);
    }
}
