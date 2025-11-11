@auth
@php
    $currentUser = Auth::user();
    $chatUserId = $currentUser->id;
    
    // Generate API token for frontend
    $apiToken = $currentUser->createToken('chat-widget')->plainTextToken;
    
    // Get Pusher/Reverb config
    $pusher = [
        'key' => env('VITE_REVERB_APP_KEY', env('PUSHER_APP_KEY', '')),
        'ws_host' => env('VITE_REVERB_HOST', env('PUSHER_HOST', request()->getHost())),
        'ws_port' => env('VITE_REVERB_PORT', env('PUSHER_PORT', 6001)),
        'wss_port' => env('VITE_REVERB_PORT', env('PUSHER_PORT', 6001)),
        'use_tls' => (bool) (env('VITE_REVERB_SCHEME', 'https') === 'https'),
    ];
@endphp

<!-- Chat Widget Floating Button -->
<button id="chatWidgetToggle" class="chat-widget-toggle" title="Chat hỗ trợ">
    <i class="fas fa-comments"></i>
    <span class="chat-widget-badge" id="chatUnreadBadge" style="display: none;">0</span>
</button>

<!-- Chat Widget Box -->
<div id="chatWidgetBox" class="chat-widget-box">
    <!-- Widget Header -->
    <div class="chat-widget-header">
        <div class="d-flex align-items-center gap-2">
            <div class="chat-widget-avatar">
                <i class="fas fa-headset"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-0">Chat hỗ trợ</h6>
                <small id="chatWidgetStatus" class="text-muted">
                    <span class="connection-status-dot"></span> Đang kết nối...
                </small>
            </div>
        </div>
        <button id="chatWidgetClose" class="chat-widget-btn-close" title="Đóng">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Widget Body -->
    <div id="chatWidgetBody" class="chat-widget-body">
        <div id="chatWidgetMessages" class="chat-widget-messages">
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin text-primary"></i>
                <p class="mt-2 text-muted small">Đang tải tin nhắn...</p>
            </div>
        </div>
    </div>

    <!-- Widget Footer -->
    <div class="chat-widget-footer">
        <div class="chat-widget-input-group">
            <input 
                id="chatWidgetInput" 
                type="text" 
                class="form-control" 
                placeholder="Nhập tin nhắn..."
                autocomplete="off"
            >
            <button id="chatWidgetSend" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<!-- Configuration Script -->
<script>
    window.__CHAT_WIDGET_CONFIG__ = {
        apiToken: @json($apiToken),
        currentUserId: @json($currentUser->id),
        chatUserId: @json($chatUserId),
        pusher: @json($pusher),
        apiBase: '/api',
        mode: 'user'
    };
</script>

<!-- Chat Widget CSS -->
<link rel="stylesheet" href="{{ asset('css/chat-widget.css') }}">

<!-- Chat Widget JS -->
<script src="https://cdn.jsdelivr.net/npm/axios@1.7.7/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.2.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script src="{{ asset('js/chat-widget.js') }}"></script>
@endauth

