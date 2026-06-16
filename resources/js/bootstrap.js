window._ = require('lodash');
// try {
//     window.Popper = require('popper.js').default;
//     window.$ = window.jQuery = require('jquery');

//     require('bootstrap');
// } catch (e) {}

window.Stepper = require('bs-stepper');
window.modal = require('bootstrap/js/dist/modal')
window.axios = require('axios');
window.axios.defaults.baseURL = window.location.origin;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');
let connWs = document.head.querySelector('meta[name="conn-ws"]');
let connWsEnabled = connWs && typeof connWs.content === 'string' ? connWs.content.trim() : '';

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

let api_token = document.head.querySelector('meta[name="api-token"]');

if (api_token) {
  window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + api_token.content;
}

import Echo from 'laravel-echo';

if(connWsEnabled){
    window.Pusher = require('pusher-js');
    
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: process.env.MIX_PUSHER_APP_KEY,
        cluster: process.env.MIX_PUSHER_APP_CLUSTER,
        encrypted: true,
        wsHost: window.location.hostname,
        wsPort: 6001,
        disableStats: true,
        enabledTransports: ['ws', 'wss'],
        forceTLS: false
    });
}