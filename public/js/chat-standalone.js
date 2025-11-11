/**
 * Chat Real-time - Standalone Version (không cần Vite)
 * Sử dụng CDN libraries
 */

(function() {
    'use strict';
    
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
        const reverbHost = config.pusher?.ws_host || window.location.hostname;
        const reverbPort = Number(config.pusher?.ws_port || 80);
        const reverbWssPort = Number(config.pusher?.wss_port || 443);
        const useTLS = config.pusher?.use_tls ?? (window.location.protocol === 'https:');
        
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
    // ChatManager Class
    // ============================================================================
    
    class ChatManager {
        constructor(config) {
            this.config = config;
            this.chatUserId = Number(config.chatUserId || (config.mode === 'user' ? config.currentUserId : 0));
            this.echoInstance = null;
            this.channel = null;
            this.roomOpened = config.mode === 'user' ? true : !!this.chatUserId;
            this.reconnectAttempts = 0;
            this.maxReconnectAttempts = 5;
            this.reconnectDelay = 3000;
            
            // DOM elements
            this.chatBody = document.getElementById('chatBody');
            this.messageInput = document.getElementById('messageInput');
            this.sendBtn = document.getElementById('sendBtn');
            this.openRoomBtn = document.getElementById('openRoomBtn');
            this.roomUserIdEl = document.getElementById('roomUserId');
            this.targetUserIdEl = document.getElementById('targetUserId');
            this.connectionStatus = document.getElementById('connectionStatus');
            
            this.init();
        }
        
        init() {
            this.setupEventListeners();
            this.updateSendState();
            
            if (this.chatUserId) {
                this.loadHistory().then(() => this.initEcho());
            }
        }
        
        setupEventListeners() {
            this.sendBtn?.addEventListener('click', () => this.sendMessage());
            this.messageInput?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
            this.openRoomBtn?.addEventListener('click', () => this.openRoom());
            this.targetUserIdEl?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') this.openRoom();
            });
        }
        
        updateSendState() {
            const disabled = !this.roomOpened || !this.chatUserId;
            if (this.sendBtn) {
                this.sendBtn.disabled = disabled;
                this.sendBtn.classList.toggle('disabled', disabled);
            }
            if (this.messageInput) {
                this.messageInput.disabled = disabled;
                this.messageInput.placeholder = disabled 
                    ? 'Chọn phòng chat (Customer ID) trước khi gửi...' 
                    : 'Nhập tin nhắn...';
            }
        }
        
        scrollToBottom() {
            if (this.chatBody) {
                this.chatBody.scrollTop = this.chatBody.scrollHeight;
            }
        }
        
        renderMessage(message) {
            if (!this.chatBody) return;
            
            const isYou = Number(message.sender_id) === Number(this.config.currentUserId);
            const div = document.createElement('div');
            div.className = `message-bubble ${isYou ? 'message-sent' : 'message-received'}`;
            const senderName = message.sender?.name || (isYou ? 'Bạn' : 'Khách');
            const time = formatTime(message.created_at);
            
            div.innerHTML = `
                <div class="message-content">${escapeHtml(message.message)}</div>
                <div class="message-meta">
                    <span class="message-sender">${escapeHtml(senderName)}</span>
                    <span class="message-time">${time}</span>
                </div>
            `;
            
            this.chatBody.appendChild(div);
            this.scrollToBottom();
        }
        
        async loadHistory() {
            if (!this.chatUserId || !this.chatBody) return;
            
            this.chatBody.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';
            
            try {
                const res = await axios.get(`${this.config.apiBase}/chat/user/${this.chatUserId}/history`, {
                    headers: { Authorization: `Bearer ${this.config.apiToken}` }
                });
                
                this.chatBody.innerHTML = '';
                const messages = res.data || [];
                
                if (messages.length === 0) {
                    this.chatBody.innerHTML = '<div class="text-center text-muted py-4">Chưa có tin nhắn nào. Hãy bắt đầu cuộc trò chuyện!</div>';
                } else {
                    messages.forEach(msg => this.renderMessage(msg));
                }
            } catch (e) {
                console.error('Error loading chat history:', e);
                this.chatBody.innerHTML = '<div class="alert alert-danger">Không thể tải lịch sử chat. Vui lòng thử lại.</div>';
            }
        }
        
        async sendMessage() {
            if (!this.chatUserId) {
                alert('Vui lòng chọn phòng chat (Customer ID) và bấm Open room trước khi gửi.');
                return;
            }
            
            const text = (this.messageInput?.value || '').trim();
            if (!text) return;
            
            const originalValue = this.messageInput.value;
            this.messageInput.disabled = true;
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
                this.messageInput.value = '';
            } catch (e) {
                console.error('Error sending message:', e);
                alert('Không thể gửi tin nhắn. Vui lòng thử lại.');
                this.messageInput.value = originalValue;
            } finally {
                this.messageInput.disabled = false;
                this.sendBtn.disabled = false;
                this.messageInput.focus();
            }
        }
        
        openRoom() {
            const userId = Number((this.targetUserIdEl?.value || '').trim());
            if (!userId || userId < 1) {
                alert('Vui lòng nhập Customer ID hợp lệ.');
                return;
            }
            
            this.chatUserId = userId;
            if (this.roomUserIdEl) {
                this.roomUserIdEl.textContent = String(this.chatUserId);
            }
            this.disconnectEcho();
            this.loadHistory().then(() => this.initEcho());
        }
        
        initEcho() {
            if (!this.chatUserId) return;
            
            this.disconnectEcho();
            
            try {
                this.echoInstance = initializeEcho(this.config);
                
                if (!this.echoInstance) {
                    this.updateConnectionStatus('error');
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
                
                this.roomOpened = true;
                this.updateSendState();
                this.updateConnectionStatus('connecting');
                
            } catch (error) {
                console.error('Error initializing Echo:', error);
                this.updateConnectionStatus('error');
                alert('Không thể kết nối real-time. Vui lòng tải lại trang.');
            }
        }
        
        setupEchoEvents() {
            if (!this.echoInstance?.connector?.pusher?.connection) return;
            
            const connection = this.echoInstance.connector.pusher.connection;
            
            connection.bind('connected', () => {
                this.updateConnectionStatus('connected');
                this.reconnectAttempts = 0;
            });
            
            connection.bind('disconnected', () => {
                this.updateConnectionStatus('disconnected');
                this.attemptReconnect();
            });
            
            connection.bind('error', (error) => {
                console.error('Pusher connection error:', error);
                this.updateConnectionStatus('error');
            });
        }
        
        disconnectEcho() {
            if (this.channel) {
                try {
                    this.channel.stopListening('.NewChatMessage');
                    this.echoInstance?.leave(`private-chat.user.${this.chatUserId}`);
                } catch (e) {
                    console.warn('Error disconnecting channel:', e);
                }
                this.channel = null;
            }
        }
        
        attemptReconnect() {
            if (this.reconnectAttempts >= this.maxReconnectAttempts) {
                this.updateConnectionStatus('failed');
                return;
            }
            
            this.reconnectAttempts++;
            this.updateConnectionStatus('reconnecting');
            
            setTimeout(() => {
                if (this.chatUserId) {
                    this.initEcho();
                }
            }, this.reconnectDelay);
        }
        
        updateConnectionStatus(status) {
            if (!this.connectionStatus) return;
            
            const statusMap = {
                connecting: { text: 'Đang kết nối...', class: 'text-info' },
                connected: { text: 'Đã kết nối', class: 'text-success' },
                disconnected: { text: 'Mất kết nối', class: 'text-warning' },
                reconnecting: { text: 'Đang kết nối lại...', class: 'text-warning' },
                error: { text: 'Lỗi kết nối', class: 'text-danger' },
                failed: { text: 'Kết nối thất bại', class: 'text-danger' }
            };
            
            const info = statusMap[status] || statusMap.disconnected;
            this.connectionStatus.textContent = info.text;
            this.connectionStatus.className = `connection-status ${info.class}`;
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
    
    function initChat() {
        if (!checkDependencies()) {
            console.error('Chat: Dependencies not loaded (axios, Echo, Pusher)');
            return;
        }
        
        setupAxios();
        
        if (window.__CHAT_CONFIG__) {
            window.chatManager = new ChatManager(window.__CHAT_CONFIG__);
        } else {
            console.warn('Chat: Config not found');
        }
    }
    
    function waitForDependencies() {
        if (checkDependencies()) {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initChat);
            } else {
                initChat();
            }
        } else {
            setTimeout(waitForDependencies, 100);
        }
    }
    
    // Start initialization
    waitForDependencies();
    
    // Export for global access
    window.ChatManager = ChatManager;
})();
