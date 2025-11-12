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
        this.conversations = [];
        this.currentConversationId = null;
        this.adminChannel = null; // Channel để lắng nghe tất cả tin nhắn mới (cho admin)
        
        // DOM elements
        this.chatBody = document.getElementById('chatBody');
        this.messageInput = document.getElementById('messageInput');
        this.sendBtn = document.getElementById('sendBtn');
        this.openRoomBtn = document.getElementById('openRoomBtn');
        this.roomUserIdEl = document.getElementById('roomUserId');
        this.targetUserIdEl = document.getElementById('targetUserId');
        this.connectionStatus = document.getElementById('connectionStatus');
        
        // Admin-only elements
        this.conversationsList = document.getElementById('conversationsList');
        this.refreshConversationsBtn = document.getElementById('refreshConversationsBtn');
        this.conversationsSearch = document.getElementById('conversationsSearch');
        this.selectedConversationInfo = document.getElementById('selectedConversationInfo');
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.updateSendState();
        
        // Load conversations list for admin
        if (this.config.mode === 'admin') {
            this.loadConversations().then(() => {
                // Initialize admin channel to listen for all new messages
                this.initAdminChannel();
            });
        }
        
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

        // Open room (Admin only - old method)
        this.openRoomBtn?.addEventListener('click', () => this.openRoom());
        this.targetUserIdEl?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                this.openRoom();
            }
        });
        
        // Conversations list (Admin only)
        if (this.refreshConversationsBtn) {
            this.refreshConversationsBtn.addEventListener('click', () => this.loadConversations());
        }
        
        if (this.conversationsSearch) {
            this.conversationsSearch.addEventListener('input', (e) => this.filterConversations(e.target.value));
        }
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
            // Ưu tiên sử dụng config từ server (đã được truyền từ blade template)
            // Không fallback về env variables để tránh dùng localhost
            const mergedConfig = {
                ...this.config,
                pusher: {
                    ...this.config.pusher,
                    // Chỉ dùng env nếu config từ server không có
                    key: this.config.pusher?.key || '',
                    ws_host: this.config.pusher?.ws_host || '',
                    ws_port: this.config.pusher?.ws_port || 80,
                    wss_port: this.config.pusher?.wss_port || 443,
                    use_tls: this.config.pusher?.use_tls ?? (window.location.protocol === 'https:')
                }
            };
            
            // Validate config
            if (!mergedConfig.pusher.key || !mergedConfig.pusher.ws_host) {
                console.error('Missing Reverb configuration:', {
                    key: mergedConfig.pusher.key ? 'Present' : 'Missing',
                    ws_host: mergedConfig.pusher.ws_host || 'Missing'
                });
                this.updateConnectionStatus('error');
                this.showAlert('Cấu hình WebSocket chưa đúng. Vui lòng kiểm tra lại.', 'danger');
                return;
            }
            
            console.log('Initializing Echo with config from server:', {
                wsHost: mergedConfig.pusher.ws_host,
                wsPort: mergedConfig.pusher.ws_port,
                wssPort: mergedConfig.pusher.wss_port,
                useTLS: mergedConfig.pusher.use_tls
            });
            
            // Disconnect existing Echo instance if any
            if (this.echoInstance) {
                try {
                    this.disconnectEcho();
                } catch (e) {
                    console.warn('Error disconnecting existing Echo:', e);
                }
            }
            
            // Initialize Echo using utility function
            this.echoInstance = initializeEcho(mergedConfig, Pusher, Echo);
            
            if (!this.echoInstance) {
                this.updateConnectionStatus('error');
                this.showAlert('Không thể kết nối real-time. Vui lòng tải lại trang.', 'danger');
                return;
            }
            
            // Subscribe to private channel
            this.channel = this.echoInstance.private(`chat.user.${this.chatUserId}`);
            
            console.log(`Subscribed to channel: chat.user.${this.chatUserId}`);
            
            // Listen for new messages
            this.channel.listen('.NewChatMessage', (event) => {
                console.log('NewChatMessage event received:', event);
                if (event?.message) {
                    console.log('Rendering new message:', event.message);
                    this.renderMessage(event.message);
                    
                    // For admin: show notification if message is from customer
                    if (this.config.mode === 'admin' && event.message.sender_id !== this.config.currentUserId) {
                        const senderName = event.message.sender?.name || 'Khách hàng';
                        this.showNotification(event.message, this.chatUserId, senderName);
                        this.updateConversationUnread(this.chatUserId, true);
                    }
                } else {
                    console.warn('NewChatMessage event received but no message data:', event);
                }
            });
            
            console.log('Event listener registered for .NewChatMessage');
            
            // For admin: also listen to all chat channels to detect new messages from any customer
            if (this.config.mode === 'admin') {
                // Subscribe to a presence channel that includes all chat rooms
                // We'll update conversations list when any new message arrives
                // This is handled by listening to all individual channels
            }
            
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
        if (!this.echoInstance?.connector?.pusher?.connection) {
            console.warn('Cannot setup Echo events: connection not available');
            return;
        }
        
        const connection = this.echoInstance.connector.pusher.connection;
        
        connection.bind('connected', () => {
            console.log('WebSocket connected successfully');
            this.updateConnectionStatus('connected');
            this.reconnectAttempts = 0;
        });
        
        connection.bind('disconnected', () => {
            console.warn('WebSocket disconnected');
            this.updateConnectionStatus('disconnected');
            this.attemptReconnect();
        });
        
        connection.bind('error', (error) => {
            console.error('Pusher connection error:', error);
            this.updateConnectionStatus('error');
        });
        
        connection.bind('state_change', (states) => {
            console.log('WebSocket state changed:', states);
        });
        
        // Log subscription events
        if (this.channel) {
            this.channel.subscribed(() => {
                console.log('Channel subscribed successfully');
            });
            
            this.channel.error((error) => {
                console.error('Channel subscription error:', error);
            });
        }
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

    // ============================================================================
    // Admin-only methods: Conversations List
    // ============================================================================

    async loadConversations() {
        if (!this.conversationsList || this.config.mode !== 'admin') {
            console.log('loadConversations: Skipped - not admin mode or conversationsList not found');
            return;
        }
        
        this.conversationsList.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-muted"></i><p class="text-muted small mt-2">Đang tải...</p></div>';
        
        try {
            console.log('Loading conversations from:', `${this.config.apiBase}/chat/conversations`);
            console.log('API Token:', this.config.apiToken ? 'Present' : 'Missing');
            
            const response = await axios.get(
                `${this.config.apiBase}/chat/conversations`,
                {
                    headers: { 
                        'Authorization': `Bearer ${this.config.apiToken}`,
                        'Accept': 'application/json'
                    }
                }
            );
            
            console.log('Conversations response:', response.data);
            
            this.conversations = Array.isArray(response.data) ? response.data : [];
            console.log('Loaded conversations:', this.conversations.length);
            
            if (this.conversations.length === 0) {
                this.conversationsList.innerHTML = '<div class="text-center py-4"><i class="fas fa-comments text-muted"></i><p class="text-muted small mt-2">Chưa có cuộc hội thoại nào</p></div>';
            } else {
                this.renderConversations();
            }
        } catch (error) {
            console.error('Error loading conversations:', error);
            console.error('Error details:', {
                message: error.message,
                response: error.response?.data,
                status: error.response?.status
            });
            
            let errorMessage = 'Không thể tải danh sách cuộc hội thoại.';
            if (error.response?.status === 401) {
                errorMessage = 'Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang.';
            } else if (error.response?.status === 403) {
                errorMessage = 'Bạn không có quyền xem danh sách cuộc hội thoại.';
            } else if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }
            
            this.conversationsList.innerHTML = `<div class="alert alert-danger m-2">${errorMessage}</div>`;
        }
    }

    renderConversations(conversations = null) {
        if (!this.conversationsList) {
            console.warn('renderConversations: conversationsList element not found');
            return;
        }
        
        const convs = conversations || this.conversations;
        
        console.log('Rendering conversations:', convs.length);
        
        if (!Array.isArray(convs) || convs.length === 0) {
            this.conversationsList.innerHTML = '<div class="text-center py-4"><i class="fas fa-comments text-muted"></i><p class="text-muted small mt-2">Chưa có cuộc hội thoại nào</p></div>';
            return;
        }
        
        try {
            this.conversationsList.innerHTML = convs.map(conv => {
                const userId = conv.user_id || conv.userId;
                const user = conv.user || {};
                const lastMessage = conv.last_message || {};
                const unreadCount = conv.unread_count || conv.unreadCount || 0;
                const isActive = this.currentConversationId === userId;
                const isUnread = unreadCount > 0;
                
                // Avatar
                let avatarHtml = '';
                if (user.avatar) {
                    const avatarUrl = user.avatar.startsWith('http') 
                        ? user.avatar 
                        : `${this.config.appUrl || ''}/storage/${user.avatar}`;
                    avatarHtml = `<img src="${avatarUrl}" alt="${escapeHtml(user.name || '')}" class="conversation-item-avatar" onerror="this.parentElement.innerHTML='<div class=\\'conversation-item-avatar-placeholder\\'><i class=\\'fas fa-user\\'></i></div>'">`;
                } else {
                    avatarHtml = `<div class="conversation-item-avatar-placeholder"><i class="fas fa-user"></i></div>`;
                }
                
                // Time ago
                const timeAgo = lastMessage.created_at ? formatTime(lastMessage.created_at) : '';
                
                // Preview message
                let preview = 'Chưa có tin nhắn';
                if (lastMessage.message) {
                    preview = escapeHtml(lastMessage.message);
                    if (preview.length > 50) {
                        preview = preview.substring(0, 50) + '...';
                    }
                }
                
                // User name
                const userName = user.name || `User #${userId}`;
                
                return `
                    <div class="conversation-item ${isActive ? 'active' : ''} ${isUnread ? 'unread' : ''}" 
                         data-user-id="${userId}" 
                         onclick="window.chatManager.selectConversation(${userId})">
                        <div class="conversation-item-header">
                            <div class="conversation-item-user">
                                ${avatarHtml}
                                <div class="conversation-item-info">
                                    <div class="conversation-item-name">${escapeHtml(userName)}</div>
                                    ${timeAgo ? `<div class="conversation-item-time">${timeAgo}</div>` : ''}
                                </div>
                            </div>
                            ${unreadCount > 0 ? `<span class="unread-badge">${unreadCount > 99 ? '99+' : unreadCount}</span>` : ''}
                        </div>
                        <div class="conversation-item-preview">${preview}</div>
                    </div>
                `;
            }).join('');
        } catch (error) {
            console.error('Error rendering conversations:', error);
            this.conversationsList.innerHTML = '<div class="alert alert-danger m-2">Lỗi khi hiển thị danh sách cuộc hội thoại.</div>';
        }
    }

    selectConversation(userId) {
        if (!userId) return;
        
        this.currentConversationId = userId;
        this.chatUserId = userId;
        
        // Reset unread count for this conversation
        this.updateConversationUnread(userId, false);
        
        // Update UI
        this.renderConversations();
        this.updateSelectedConversationInfo(userId);
        
        // Disconnect old channel
        this.disconnectEcho();
        
        // Load history and reconnect
        this.loadHistory().then(() => {
            this.initEcho();
        });
        
        // Enable input
        if (this.messageInput) {
            this.messageInput.disabled = false;
            this.messageInput.placeholder = 'Nhập tin nhắn...';
        }
        if (this.sendBtn) {
            this.sendBtn.disabled = false;
        }
    }

    async updateSelectedConversationInfo(userId) {
        if (!this.selectedConversationInfo) return;
        
        const conversation = this.conversations.find(c => c.user_id === userId);
        if (!conversation || !conversation.user) return;
        
        const user = conversation.user;
        const avatarHtml = user.avatar 
            ? `<img src="${this.config.appUrl}/storage/${user.avatar}" alt="${escapeHtml(user.name)}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">`
            : `<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-user"></i></div>`;
        
        this.selectedConversationInfo.innerHTML = `
            <div class="user-avatar">${avatarHtml}</div>
            <div>
                <strong id="roomUserName">${escapeHtml(user.name)}</strong>
                <div class="text-muted small" id="roomUserEmail">${escapeHtml(user.email || '')}</div>
            </div>
        `;
        
        if (this.roomUserIdEl) {
            this.roomUserIdEl.textContent = userId;
        }
    }

    filterConversations(searchTerm) {
        if (!searchTerm) {
            this.renderConversations();
            return;
        }
        
        const filtered = this.conversations.filter(conv => {
            const user = conv.user || {};
            const name = (user.name || '').toLowerCase();
            const email = (user.email || '').toLowerCase();
            const term = searchTerm.toLowerCase();
            return name.includes(term) || email.includes(term);
        });
        
        this.renderConversations(filtered);
    }

    initAdminChannel() {
        // Admin channel initialization - không cần tạo Echo instance riêng
        // Echo instance sẽ được tạo khi mở conversation trong initEcho()
        // Chỉ cần đảm bảo config được set đúng
        if (this.config.mode !== 'admin') return;
        
        console.log('Admin channel initialized. Echo will be created when opening a conversation.');
    }

    showNotification(message, userId, userName) {
        // Only show notification if not currently viewing this conversation
        if (this.currentConversationId === userId) {
            // Just update the conversation list
            this.loadConversations();
            return;
        }
        
        const notification = document.createElement('div');
        notification.className = 'notification-toast alert alert-info alert-dismissible fade show';
        notification.innerHTML = `
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-bell text-primary"></i>
                <div class="flex-grow-1">
                    <strong>Tin nhắn mới từ ${escapeHtml(userName)}</strong>
                    <div class="small">${escapeHtml(message.message || message).substring(0, 100)}${(message.message || message).length > 100 ? '...' : ''}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
        
        // Update conversations list
        this.loadConversations();
    }

    updateConversationUnread(userId, increment = true) {
        const conv = this.conversations.find(c => c.user_id === userId);
        if (conv) {
            if (increment) {
                conv.unread_count = (conv.unread_count || 0) + 1;
            } else {
                conv.unread_count = 0;
            }
            this.renderConversations();
        }
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

