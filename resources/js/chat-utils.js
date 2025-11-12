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
    // Xử lý host - không bao gồm port trong host
    let cleanHost = wsHost || window.location.hostname;
    // Loại bỏ port nếu có trong host (ví dụ: localhost:8080 -> localhost)
    if (cleanHost.includes(':')) {
        cleanHost = cleanHost.split(':')[0];
    }
    
    // Xử lý port - chỉ dùng port nếu không phải port mặc định
    const isHTTPS = useTLS ?? (window.location.protocol === 'https:');
    const defaultWSPort = 80;
    const defaultWSSPort = 443;
    
    // Nếu dùng HTTPS và port là 443 (mặc định), không cần chỉ định port
    // Nếu dùng HTTP và port là 80 (mặc định), không cần chỉ định port
    const finalWsPort = Number(wsPort || defaultWSPort);
    const finalWssPort = Number(wssPort || defaultWSSPort);
    
    return {
        broadcaster: 'reverb',
        key: key || '',
        wsHost: cleanHost,
        wsPort: finalWsPort,
        wssPort: finalWssPort,
        forceTLS: isHTTPS,
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
    
    // Ưu tiên sử dụng config từ server, không fallback về localhost
    const wsHost = config.pusher?.ws_host || config.wsHost;
    if (!wsHost) {
        console.warn('WebSocket host not configured. Using current hostname as fallback.');
    }
    
    const finalWsHost = wsHost || (typeof window !== 'undefined' ? window.location.hostname : 'localhost');
    const finalWsPort = config.pusher?.ws_port || config.wsPort || 80;
    const finalWssPort = config.pusher?.wss_port || config.wssPort || 443;
    const finalUseTLS = config.pusher?.use_tls ?? config.useTLS ?? (typeof window !== 'undefined' ? window.location.protocol === 'https:' : false);
    
    console.log('Echo config input:', {
        wsHost_from_config: wsHost,
        wsPort_from_config: finalWsPort,
        wssPort_from_config: finalWssPort,
        useTLS_from_config: finalUseTLS,
        current_hostname: typeof window !== 'undefined' ? window.location.hostname : 'N/A',
        current_protocol: typeof window !== 'undefined' ? window.location.protocol : 'N/A'
    });
    
    const reverbConfig = createReverbConfig({
        key: config.pusher?.key || config.key || '',
        wsHost: finalWsHost,
        wsPort: finalWsPort,
        wssPort: finalWssPort,
        useTLS: finalUseTLS
    });
    
    console.log('Creating Echo with config:', {
        wsHost: reverbConfig.wsHost,
        wsPort: reverbConfig.wsPort,
        wssPort: reverbConfig.wssPort,
        forceTLS: reverbConfig.forceTLS,
        key: reverbConfig.key ? '***' : 'MISSING',
        fullUrl: reverbConfig.forceTLS 
            ? `wss://${reverbConfig.wsHost}:${reverbConfig.wssPort}` 
            : `ws://${reverbConfig.wsHost}:${reverbConfig.wsPort}`
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

