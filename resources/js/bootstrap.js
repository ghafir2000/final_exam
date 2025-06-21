// In resources/js/bootstrap.js

import jquery from 'jquery';
window.$ = jquery;      // Make $ global
window.jQuery = jquery; // Make jQuery global

// Import Popper.js (required by Bootstrap 4 JS)
import Popper from 'popper.js/dist/umd/popper.js'; // Often need to specify UMD for global assignment
window.Popper = Popper;

// Import Bootstrap's JavaScript components
import 'bootstrap/dist/js/bootstrap.bundle.min.js'; // Or just 'bootstrap' if you want to import individual components

import 'simplebar/dist/simplebar.min.css'; // Or simplebar.css if you prefer non-minified

// --- Axios and Echo setup (as before) ---
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;
window.Echo = new Echo({
    // ... your Echo config ...
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST || `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT || 6001,
    wssPort: import.meta.env.VITE_PUSHER_PORT || 6001,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: `${window.APP_URL}/broadcasting/auth`

});