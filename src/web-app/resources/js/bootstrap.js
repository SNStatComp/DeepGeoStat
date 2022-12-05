window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: (process.env.MIX_PUSHER_APP_KEY == null || process.env.MIX_PUSHER_APP_KEY?.trim().length === 0) ? "deepgeostat" : process.env.MIX_PUSHER_APP_KEY,
    wsHost: (process.env.MIX_PUSHER_APP_HOST == null || process.env.MIX_PUSHER_APP_HOST?.trim().length === 0) ? window.location.hostname : process.env.MIX_PUSHER_APP_HOST,
    wsPort: (process.env.MIX_PUSHER_PORT == null || process.env.MIX_PUSHER_PORT?.trim().length === 0) ? 6001 : process.env.MIX_PUSHER_PORT,
    disableStats: true,
    forceTLS: false,
});
