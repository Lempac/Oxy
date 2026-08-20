import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
if (typeof window !== 'undefined') {
    window.Pusher = Pusher;
}

const meta = (name: string) => typeof document !== 'undefined' ? document.querySelector(`meta[name="${name}"]`)?.getAttribute('content') : null;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY || meta('reverb-app-key') || 'oxy-reverb-key';

const echo = typeof window !== 'undefined' && reverbKey ? new Echo({
    broadcaster: 'reverb',
    key: reverbKey,
    wsHost: import.meta.env.VITE_REVERB_HOST || meta('reverb-host') || (typeof window !== 'undefined' ? window.location?.hostname : 'localhost'),
    wsPort: import.meta.env.VITE_REVERB_PORT || meta('reverb-port') || 80,
    wssPort: import.meta.env.VITE_REVERB_PORT || meta('reverb-port') || 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME || meta('reverb-scheme') || 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
}) : null;

if (typeof window !== 'undefined' && echo) {
    (window as any).Echo = echo;
}

export default echo;
