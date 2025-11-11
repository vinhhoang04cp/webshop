/**
 * Chat Real-time Module
 * Sử dụng Laravel Echo với Reverb
 */

import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { formatTime, escapeHtml, setupAxios, initializeEcho } from './chat-utils.js';

// Setup axios defaults
setupAxios(axios);

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
        // Send message
        this.sendBtn?.addEventListener('click', () => this.sendMessage());
        this.messageInput?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        // Open room (Admin only)
        this.openRoomBtn?.addEventListener('click', () => this.openRoom());
        this.targetUserIdEl?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                this.openRoom();
            }
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
        const messageDiv = document.createElement('div');
        messageDiv.className = `message-bubble ${isYou ? 'message-sent' : 'message-received'}`;
        
        const senderName = message.sender?.name || (isYou ? 'Bạn' : 'Khách');
        const time = formatTime(message.created_at);
        
        messageDiv.innerHTML = `
            <div class="message-content">${escapeHtml(message.message)}</div>
            <div class="message-meta">
                <span class="message-sender">${escapeHtml(senderName)}</span>
                <span class="message-time">${time}</span>
            </div>
        `;
        
        this.chatBody.appendChild(messageDiv);
        this.scrollToBottom();
    }

    async loadHistory() {
        if (!this.chatUserId || !this.chatBody) return;
        
        this.chatBody.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';
        
        try {
            const response = await axios.get(
                `${this.config.apiBase}/chat/user/${this.chatUserId}/history`,
                {
                    headers: { Authorization: `Bearer ${this.config.apiToken}` }
                }
            );
            
            this.chatBody.innerHTML = '';
            const messages = response.data || [];
            
            if (messages.length === 0) {
                this.chatBody.innerHTML = '<div class="text-center text-muted py-4">Chưa có tin nhắn nào. Hãy bắt đầu cuộc trò chuyện!</div>';
            } else {
                messages.forEach(msg => this.renderMessage(msg));
            }
        } catch (error) {
            console.error('Error loading chat history:', error);
            this.chatBody.innerHTML = '<div class="alert alert-danger">Không thể tải lịch sử chat. Vui lòng thử lại.</div>';
        }
    }

    async sendMessage() {
        if (!this.chatUserId) {
            this.showAlert('Vui lòng chọn phòng chat (Customer ID) và bấm Open room trước khi gửi.', 'warning');
            return;
        }
        
        const text = (this.messageInput?.value || '').trim();
        if (!text) return;
        
        // Disable input while sending
        const originalValue = this.messageInput.value;
        this.messageInput.disabled = true;
        this.sendBtn.disabled = true;
        
        try {
            const response = await axios.post(
                `${this.config.apiBase}/chat/user/${this.chatUserId}/message`,
                { message: text },
                {
                    headers: { Authorization: `Bearer ${this.config.apiToken}` }
                }
            );
            
            // Message will be rendered via Echo event, but we can render immediately for better UX
            this.renderMessage(response.data);
            this.messageInput.value = '';
        } catch (error) {
            console.error('Error sending message:', error);
            this.showAlert('Không thể gửi tin nhắn. Vui lòng thử lại.', 'danger');
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
            this.showAlert('Vui lòng nhập Customer ID hợp lệ.', 'warning');
            return;
        }
        
        this.chatUserId = userId;
        if (this.roomUserIdEl) {
            this.roomUserIdEl.textContent = String(this.chatUserId);
        }
        
        // Cleanup old connection
        this.disconnectEcho();
        
        // Load history and reconnect
        this.loadHistory().then(() => {
            this.initEcho();
        });
    }

    initEcho() {
        if (!this.chatUserId) return;
        
        // Cleanup existing connection
        this.disconnectEcho();
        
        try {
            // Merge config with env variables for Vite
            const mergedConfig = {
                ...this.config,
                pusher: {
                    ...this.config.pusher,
                    key: this.config.pusher?.key || import.meta.env.VITE_REVERB_APP_KEY,
                    ws_host: this.config.pusher?.ws_host || import.meta.env.VITE_REVERB_HOST,
                    ws_port: this.config.pusher?.ws_port || import.meta.env.VITE_REVERB_PORT || 80,
                    wss_port: this.config.pusher?.wss_port || import.meta.env.VITE_REVERB_PORT || 443,
                    use_tls: this.config.pusher?.use_tls ?? (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https'
                }
            };
            
            // Initialize Echo using utility function
            this.echoInstance = initializeEcho(mergedConfig, Pusher, Echo);
            
            if (!this.echoInstance) {
                this.updateConnectionStatus('error');
                this.showAlert('Không thể kết nối real-time. Vui lòng tải lại trang.', 'danger');
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
            this.showAlert('Không thể kết nối real-time. Vui lòng tải lại trang.', 'danger');
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
            this.showAlert('Mất kết nối. Vui lòng tải lại trang.', 'danger');
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
        
        const statusInfo = statusMap[status] || statusMap.disconnected;
        this.connectionStatus.textContent = statusInfo.text;
        this.connectionStatus.className = `connection-status ${statusInfo.class}`;
    }

    showAlert(message, type = 'info') {
        // Simple alert - can be enhanced with Bootstrap toast
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '300px';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (window.__CHAT_CONFIG__) {
            window.chatManager = new ChatManager(window.__CHAT_CONFIG__);
        }
    });
} else {
    if (window.__CHAT_CONFIG__) {
        window.chatManager = new ChatManager(window.__CHAT_CONFIG__);
    }
}

export default ChatManager;

