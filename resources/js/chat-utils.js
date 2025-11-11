/**
 * Chat Utilities - Shared helper functions
 */

import axios from 'axios';

/**
 * Format timestamp to Vietnamese time format
 * @param {string|Date} timestamp - Timestamp to format
 * @returns {string} Formatted time string
 */
export function formatTime(timestamp) {
    if (!timestamp) return '';
    
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    
    if (minutes < 1) return 'Vừa xong';
    if (minutes < 60) return `${minutes} phút trước`;
    if (date.toDateString() === now.toDateString()) {
        return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    }
    return date.toLocaleString('vi-VN', { 
        day: '2-digit', 
        month: '2-digit', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

/**
 * Escape HTML to prevent XSS attacks
 * @param {string} text - Text to escape
 * @returns {string} Escaped HTML string
 */
export function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Get CSRF token from meta tag
 * @returns {string|null} CSRF token or null
 */
export function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || null;
}

/**
 * Setup axios defaults
 * @param {Object} axiosInstance - Axios instance (optional, defaults to imported axios)
 */
export function setupAxios(axiosInstance = null) {
    const axiosLib = axiosInstance || axios;
    const csrfToken = getCsrfToken();
    if (csrfToken) {
        axiosLib.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    }
    axiosLib.defaults.withCredentials = true;
    
    // Also set on window for compatibility with standalone scripts
    if (typeof window !== 'undefined' && !window.axios) {
        window.axios = axiosLib;
    }
}

/**
 * Create Reverb configuration object
 * @param {Object} config - Configuration object
 * @param {string} config.key - Reverb app key
 * @param {string} config.wsHost - WebSocket host
 * @param {number} config.wsPort - WebSocket port
 * @param {number} config.wssPort - WebSocket secure port
 * @param {boolean} config.useTLS - Use TLS
 * @returns {Object} Reverb configuration
 */
export function createReverbConfig({ key, wsHost, wsPort, wssPort, useTLS }) {
    return {
        broadcaster: 'reverb',
        key: key || '',
        wsHost: wsHost || window.location.hostname,
        wsPort: Number(wsPort || 80),
        wssPort: Number(wssPort || 443),
        forceTLS: useTLS ?? (window.location.protocol === 'https:'),
        enabledTransports: ['ws', 'wss'],
        authorizer: (channel, options) => {
            return {
                    authorize: (socketId, callback) => {
                    const csrfToken = getCsrfToken();
                    const axiosLib = typeof window !== 'undefined' && window.axios ? window.axios : axios;
                    axiosLib.post('/broadcasting/auth', {
                        socket_id: socketId,
                        channel_name: channel.name
                    }, {
                        headers: { 
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken || ''
                        },
                        withCredentials: true
                    }).then(response => {
                        callback(false, response.data);
                    }).catch(error => {
                        console.error('Echo authorization error:', error);
                        callback(true, error);
                    });
                }
            };
        }
    };
}

/**
 * Initialize Echo instance with Reverb
 * @param {Object} config - Pusher/Reverb configuration
 * @param {Function} PusherConstructor - Pusher constructor (optional, for module imports)
 * @param {Function} EchoConstructor - Echo constructor (optional, for module imports)
 * @returns {Object|null} Echo instance or null on error
 */
export function initializeEcho(config, PusherConstructor = null, EchoConstructor = null) {
    // Get constructors - prefer provided, fallback to global
    let PusherClass = PusherConstructor;
    let EchoClass = EchoConstructor;
    
    // For module system, use provided constructors
    // For standalone, check window
    if (typeof window !== 'undefined') {
        PusherClass = PusherClass || window.Pusher;
        EchoClass = EchoClass || (window.Echo && typeof window.Echo === 'function' ? window.Echo : null);
        
        // If Echo is already an instance, try to get constructor from it
        if (!EchoClass && window.Echo && window.Echo.constructor && window.Echo.constructor.name === 'Echo') {
            EchoClass = window.Echo.constructor;
        }
    }
    
    if (!PusherClass) {
        console.error('Pusher is not defined');
        return null;
    }
    
    if (!EchoClass) {
        console.error('Echo constructor not found');
        return null;
    }
    
    // Set Pusher to window if provided and window exists
    if (PusherConstructor && typeof window !== 'undefined') {
        window.Pusher = PusherConstructor;
    }
    
    const reverbConfig = createReverbConfig({
        key: config.pusher?.key || config.key || '',
        wsHost: config.pusher?.ws_host || config.wsHost || (typeof window !== 'undefined' ? window.location.hostname : 'localhost'),
        wsPort: config.pusher?.ws_port || config.wsPort || 80,
        wssPort: config.pusher?.wss_port || config.wssPort || 443,
        useTLS: config.pusher?.use_tls ?? config.useTLS ?? (typeof window !== 'undefined' ? window.location.protocol === 'https:' : false)
    });
    
    // Disconnect existing Echo instance if any
    if (typeof window !== 'undefined' && window.Echo && window.Echo.connector) {
        try {
            window.Echo.disconnect();
        } catch (e) {
            console.warn('Error disconnecting existing Echo:', e);
        }
    }
    
    try {
        const echoInstance = new EchoClass(reverbConfig);
        if (typeof window !== 'undefined') {
            window.Echo = echoInstance;
        }
        return echoInstance;
    } catch (error) {
        console.error('Error creating Echo instance:', error);
        return null;
    }
}

