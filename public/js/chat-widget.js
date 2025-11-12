/**
 * Chat Widget - Floating Box
 * Standalone version (không cần Vite)
 */

(function() {
    'use strict';
    
    if (!window.__CHAT_WIDGET_CONFIG__) {
        console.warn('Chat Widget: Config not found');
        return;
    }
    
    const config = window.__CHAT_WIDGET_CONFIG__;
    
    // ============================================================================
    // Utility Functions
    // ============================================================================
    
    /**
     * Get CSRF token from meta tag
     */
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || null;
    }
    
    /**
     * Setup axios defaults
     */
    function setupAxios() {
        const csrfToken = getCsrfToken();
        if (csrfToken) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        }
        axios.defaults.withCredentials = true;
    }
    
    /**
     * Format timestamp to Vietnamese time format
     */
    function formatTime(timestamp) {
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
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Create Reverb configuration object
     */
    function createReverbConfig(config) {
        const reverbKey = config.pusher?.key || '';
        // Ưu tiên sử dụng config từ server, không fallback về localhost
        const reverbHost = config.pusher?.ws_host || window.location.hostname;
        const reverbPort = Number(config.pusher?.ws_port || 80);
        const reverbWssPort = Number(config.pusher?.wss_port || 443);
        const useTLS = config.pusher?.use_tls ?? (window.location.protocol === 'https:');
        
        // Debug log
        console.log('Reverb Config:', {
            key: reverbKey ? '***' : 'MISSING',
            wsHost: reverbHost,
            wsPort: reverbPort,
            wssPort: reverbWssPort,
            useTLS: useTLS
        });
        
        return {
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: reverbHost,
            wsPort: reverbPort,
            wssPort: reverbWssPort,
            forceTLS: useTLS,
            enabledTransports: ['ws', 'wss'],
            authorizer: (channel, options) => {
                return {
                    authorize: (socketId, callback) => {
                        const csrfToken = getCsrfToken();
                        axios.post('/broadcasting/auth', {
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
     */
    function initializeEcho(config) {
        if (typeof Pusher === 'undefined') {
            console.error('Pusher is not defined');
            return null;
        }
        
        if (typeof Echo === 'undefined') {
            console.error('Echo constructor not found');
            return null;
        }
        
        window.Pusher = Pusher;
        
        const reverbConfig = createReverbConfig(config);
        
        // Disconnect existing Echo instance if any
        if (window.Echo && window.Echo.connector) {
            try {
                window.Echo.disconnect();
            } catch (e) {
                console.warn('Error disconnecting existing Echo:', e);
            }
        }
        
        try {
            const echoInstance = new Echo(reverbConfig);
            window.Echo = echoInstance;
            return echoInstance;
        } catch (error) {
            console.error('Error creating Echo instance:', error);
            return null;
        }
    }
    
    // ============================================================================
    // ChatWidget Class
    // ============================================================================
    
    class ChatWidget {
        constructor() {
            this.config = config;
            this.chatUserId = Number(config.chatUserId);
            this.echoInstance = null;
            this.channel = null;
            this.isOpen = false;
            this.unreadCount = 0;
            
            // DOM elements
            this.toggleBtn = document.getElementById('chatWidgetToggle');
            this.widgetBox = document.getElementById('chatWidgetBox');
            this.widgetBody = document.getElementById('chatWidgetBody');
            this.messagesContainer = document.getElementById('chatWidgetMessages');
            this.input = document.getElementById('chatWidgetInput');
            this.sendBtn = document.getElementById('chatWidgetSend');
            this.closeBtn = document.getElementById('chatWidgetClose');
            this.statusEl = document.getElementById('chatWidgetStatus');
            this.statusDot = this.statusEl?.querySelector('.connection-status-dot');
            this.unreadBadge = document.getElementById('chatUnreadBadge');
            
            this.init();
        }
        
        init() {
            this.setupEventListeners();
            this.loadHistory().then(() => this.initEcho());
        }
        
        setupEventListeners() {
            this.toggleBtn?.addEventListener('click', () => this.toggle());
            this.closeBtn?.addEventListener('click', () => this.close());
            this.sendBtn?.addEventListener('click', () => this.sendMessage());
            this.input?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
        }
        
        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        }
        
        open() {
            if (!this.widgetBox) return;
            this.widgetBox.classList.add('active');
            this.isOpen = true;
            this.unreadCount = 0;
            this.updateUnreadBadge();
            this.input?.focus();
            this.scrollToBottom();
        }
        
        close() {
            if (!this.widgetBox) return;
            this.widgetBox.classList.remove('active');
            this.isOpen = false;
        }
        
        scrollToBottom() {
            if (this.messagesContainer) {
                this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
            }
        }
        
        renderMessage(message) {
            if (!this.messagesContainer) return;
            
            const isYou = Number(message.sender_id) === Number(this.config.currentUserId);
            const div = document.createElement('div');
            div.className = `chat-widget-message ${isYou ? 'chat-widget-message-sent' : 'chat-widget-message-received'}`;
            
            const senderName = message.sender?.name || (isYou ? 'Bạn' : 'Hỗ trợ');
            const time = formatTime(message.created_at);
            
            div.innerHTML = `
                <div>${escapeHtml(message.message)}</div>
                <div class="chat-widget-message-meta">
                    <span>${escapeHtml(senderName)}</span>
                    <span>•</span>
                    <span>${time}</span>
                </div>
            `;
            
            this.messagesContainer.appendChild(div);
            this.scrollToBottom();
            
            // Update unread count if message is not from you and widget is closed
            if (!isYou && !this.isOpen) {
                this.unreadCount++;
                this.updateUnreadBadge();
            }
        }
        
        updateUnreadBadge() {
            if (!this.unreadBadge) return;
            if (this.unreadCount > 0) {
                this.unreadBadge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                this.unreadBadge.style.display = 'flex';
            } else {
                this.unreadBadge.style.display = 'none';
            }
        }
        
        updateConnectionStatus(status) {
            if (!this.statusDot) return;
            
            const statusMap = {
                connecting: { class: '', text: 'Đang kết nối...' },
                connected: { class: 'connected', text: 'Đã kết nối' },
                disconnected: { class: 'disconnected', text: 'Mất kết nối' }
            };
            
            const info = statusMap[status] || statusMap.disconnected;
            this.statusDot.className = `connection-status-dot ${info.class}`;
            if (this.statusEl) {
                this.statusEl.innerHTML = `<span class="connection-status-dot ${info.class}"></span> ${info.text}`;
            }
        }
        
        async loadHistory() {
            if (!this.chatUserId || !this.messagesContainer) return;
            
            this.messagesContainer.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-primary"></i><p class="mt-2 text-muted small">Đang tải tin nhắn...</p></div>';
            
            try {
                const res = await axios.get(`${this.config.apiBase}/chat/user/${this.chatUserId}/history`, {
                    headers: { Authorization: `Bearer ${this.config.apiToken}` }
                });
                
                this.messagesContainer.innerHTML = '';
                const messages = res.data || [];
                
                if (messages.length === 0) {
                    this.messagesContainer.innerHTML = '<div class="text-center py-4"><i class="fas fa-comment-dots"></i><p class="mt-2 text-muted small">Chưa có tin nhắn nào. Hãy bắt đầu cuộc trò chuyện!</p></div>';
                } else {
                    messages.forEach(msg => this.renderMessage(msg));
                }
            } catch (e) {
                console.error('Error loading chat history:', e);
                this.messagesContainer.innerHTML = '<div class="alert alert-danger small m-2">Không thể tải lịch sử chat. Vui lòng thử lại.</div>';
            }
        }
        
        async sendMessage() {
            if (!this.chatUserId) return;
            
            const text = (this.input?.value || '').trim();
            if (!text) return;
            
            const originalValue = this.input.value;
            this.input.disabled = true;
            this.sendBtn.disabled = true;
            
            try {
                const res = await axios.post(
                    `${this.config.apiBase}/chat/user/${this.chatUserId}/message`, 
                    { message: text }, 
                    {
                        headers: { Authorization: `Bearer ${this.config.apiToken}` }
                    }
                );
                
                this.renderMessage(res.data);
                this.input.value = '';
            } catch (e) {
                console.error('Error sending message:', e);
                alert('Không thể gửi tin nhắn. Vui lòng thử lại.');
                this.input.value = originalValue;
            } finally {
                this.input.disabled = false;
                this.sendBtn.disabled = false;
                this.input.focus();
            }
        }
        
        initEcho() {
            if (!this.chatUserId) return;
            
            try {
                this.echoInstance = initializeEcho(this.config);
                
                if (!this.echoInstance) {
                    this.updateConnectionStatus('disconnected');
                    return;
                }
                
                // Subscribe to private channel
                this.channel = this.echoInstance.private(`chat.user.${this.chatUserId}`);
                
                // Listen for new messages
                this.channel.listen('.NewChatMessage', (event) => {
                    if (event?.message) {
                        this.renderMessage(event.message);
                    }
                });
                
                // Setup connection event handlers
                this.setupEchoEvents();
                
                this.updateConnectionStatus('connecting');
            } catch (error) {
                console.error('Error initializing Echo:', error);
                this.updateConnectionStatus('disconnected');
            }
        }
        
        setupEchoEvents() {
            if (!this.echoInstance?.connector?.pusher?.connection) return;
            
            const connection = this.echoInstance.connector.pusher.connection;
            
            connection.bind('connected', () => {
                this.updateConnectionStatus('connected');
            });
            
            connection.bind('disconnected', () => {
                this.updateConnectionStatus('disconnected');
            });
            
            connection.bind('error', (error) => {
                console.error('Pusher connection error:', error);
                this.updateConnectionStatus('disconnected');
            });
        }
    }
    
    // ============================================================================
    // Initialization
    // ============================================================================
    
    function checkDependencies() {
        return typeof axios !== 'undefined' && 
               typeof Echo !== 'undefined' && 
               typeof Pusher !== 'undefined';
    }
    
    function initChatWidget() {
        if (!checkDependencies()) {
            console.error('Chat Widget: Dependencies not loaded (axios, Echo, Pusher)');
            return;
        }
        
        setupAxios();
        
        if (window.__CHAT_WIDGET_CONFIG__) {
            window.chatWidget = new ChatWidget();
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChatWidget);
    } else {
        initChatWidget();
    }
    
    // Export for global access
    window.ChatWidget = ChatWidget;
})();
