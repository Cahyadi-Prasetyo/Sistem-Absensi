import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Get config with fallback values
const reverbKey = import.meta.env.VITE_REVERB_APP_KEY || 'reverb-key';
const reverbHost = import.meta.env.VITE_REVERB_HOST || 'localhost';
const reverbPort = import.meta.env.VITE_REVERB_PORT || 8080;
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || 'http';

console.log('🔧 Echo Config:', { key: reverbKey, host: reverbHost, port: reverbPort, scheme: reverbScheme });

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: reverbKey,
    wsHost: reverbHost,
    wsPort: parseInt(reverbPort),
    wssPort: parseInt(reverbPort),
    forceTLS: reverbScheme === 'https',
    enabledTransports: ['ws', 'wss'],
});

console.log('✅ Echo initialized');
