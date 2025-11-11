import axios from 'axios';
import Pusher from 'pusher-js';

// Setup axios
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Setup Pusher
window.Pusher = Pusher;

/**
 * Echo is initialized in echo.js to avoid duplication.
 * This allows other modules to use Echo without creating multiple instances.
 */
import './echo';
