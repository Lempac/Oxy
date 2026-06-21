import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const meta = (name: string) => document.querySelector(`meta[name="${name}"]`)?.getAttribute('content');

const echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || meta('reverb-app-key'),
    wsHost: import.meta.env.VITE_REVERB_HOST || meta('reverb-host'),
    wsPort: import.meta.env.VITE_REVERB_PORT || meta('reverb-port') || 80,
    wssPort: import.meta.env.VITE_REVERB_PORT || meta('reverb-port') || 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME || meta('reverb-scheme') || 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

export default echo;
