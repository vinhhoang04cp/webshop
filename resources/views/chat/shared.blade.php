@php
$isAdmin = ($mode ?? '') === 'admin';
@endphp

@if($isAdmin)
    {{-- Admin/Manager: Sử dụng layout app với sidebar --}}
    @extends('layouts.app')
    
    @section('title', 'Chat Support - Admin/Manager')
    
    @section('styles')
    @php
        $viteManifestExists = file_exists(public_path('build/manifest.json'));
    @endphp
    @if($viteManifestExists)
        @vite(['resources/css/chat.css'])
    @else
        <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    @endif
    <style>
        /* Chat trong dashboard layout - Override styles */
        .dashboard-content {
            display: flex;
            flex-direction: column;
            padding: 25px !important;
            overflow: hidden;
        }
        
        .dashboard-content .dashboard-header {
            margin-bottom: 20px;
            flex-shrink: 0;
        }
        
        .dashboard-content .chat-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            height: 100%;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .dashboard-content .chat-panel-body {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            min-height: 0;
            -webkit-overflow-scrolling: touch;
        }
        
        .dashboard-content .chat-panel-header {
            flex-shrink: 0;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .dashboard-content .chat-panel-footer {
            flex-shrink: 0;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        /* Đảm bảo connection status hiển thị đúng */
        .dashboard-content .connection-status {
            font-size: 0.85rem;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            background: rgba(59, 130, 246, 0.1);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .dashboard-content .connection-status::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            background-color: #3b82f6;
            box-shadow: 0 0 4px #3b82f6;
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        .dashboard-content .connection-status.text-success::before {
            background-color: #22c55e;
            box-shadow: 0 0 4px #22c55e;
            animation: none;
        }
        
        .dashboard-content .connection-status.text-danger::before {
            background-color: #ef4444;
            box-shadow: 0 0 4px #ef4444;
            animation: none;
        }
        
        .dashboard-content .connection-status.text-warning::before {
            background-color: #f59e0b;
            box-shadow: 0 0 4px #f59e0b;
            animation: none;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* Đảm bảo card không bị overflow */
        .dashboard-content .card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        /* Responsive trong dashboard */
        @media (max-width: 768px) {
            .dashboard-content {
                padding: 15px !important;
            }
            
            .dashboard-content .dashboard-header h2 {
                font-size: 1.3rem;
            }
            
            .dashboard-content .chat-panel-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
            
            .dashboard-content .chat-panel-header .toolbar {
                width: 100%;
            }
            
            .dashboard-content .chat-panel-header .toolbar input {
                max-width: 100% !important;
                width: 100%;
            }
        }
    </style>
    @endsection
    
    @section('content')
    <div class="container-fluid p-0">
        <div class="row g-0">
            @include('components.sidebar')
            
            <div class="col-md-9 col-lg-10 dashboard-content">
                <div class="dashboard-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h2><i class="fas fa-comments me-2"></i>Chat Hỗ trợ khách hàng</h2>
                            <p class="text-muted mb-0">Quản lý và hỗ trợ khách hàng qua chat</p>
                        </div>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <span id="connectionStatus" class="connection-status text-info">
                                Đang kết nối...
                            </span>
                            <span class="badge bg-primary">
                                <i class="fas fa-user me-1"></i> {{ $currentUser->name }}
                            </span>
                        </div>
                    </div>
                </div>
                
                @include('components.alerts')
                
                <!-- Chat Panel -->
                <div class="card chat-panel">
                    <!-- Panel Header -->
                    <div class="chat-panel-header">
                        <div class="room-info">
                            <span class="text-muted">Phòng chat của Customer ID:</span>
                            <strong id="roomUserId" class="text-primary">{{ $chatUserId ?: 'Chưa chọn' }}</strong>
                            @if(!$chatUserId)
                                <span class="badge bg-warning text-dark ms-2">
                                    <i class="fas fa-info-circle"></i> Nhập Customer ID để mở phòng
                                </span>
                            @endif
                        </div>
                        <div class="toolbar">
                            <input 
                                id="targetUserId" 
                                type="number" 
                                min="1" 
                                class="form-control form-control-sm" 
                                placeholder="Customer ID" 
                                value="{{ $chatUserId ?: '' }}"
                                style="max-width: 150px; display: inline-block;"
                            >
                            <button id="openRoomBtn" class="btn btn-primary btn-sm">
                                <i class="fas fa-door-open"></i> Open room
                            </button>
                        </div>
                    </div>

                    <!-- Chat Messages Body -->
                    <div id="chatBody" class="chat-panel-body">
                        <div class="text-center py-5">
                            <i class="fas fa-comment-dots text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-3 text-muted">Đang tải tin nhắn...</p>
                        </div>
                    </div>

                    <!-- Chat Input Footer -->
                    <div class="chat-panel-footer">
                        <div class="chat-input-group">
                            <input 
                                id="messageInput" 
                                type="text" 
                                class="form-control" 
                                placeholder="Chọn phòng chat (Customer ID) trước khi gửi..."
                                autocomplete="off"
                            >
                            <button id="sendBtn" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Gửi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
    
    @section('scripts')
    <!-- Configuration Script -->
    <script>
        window.__CHAT_CONFIG__ = {
            apiToken: @json($apiToken),
            currentUserId: @json($currentUser->id),
            chatUserId: @json($chatUserId),
            pusher: @json($pusher),
            appUrl: @json(config('app.url')),
            apiBase: '/api',
            mode: 'admin'
        };
    </script>
    
    <!-- Chat JS - Vite với fallback CDN -->
    @php
        $viteManifestExists = file_exists(public_path('build/manifest.json'));
    @endphp
    @if($viteManifestExists)
        @vite(['resources/js/chat.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/axios@1.7.7/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.2.0/dist/web/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
        <script src="{{ asset('js/chat-standalone.js') }}"></script>
    @endif
    @endsection

@else
    {{-- Customer: Giữ nguyên layout riêng --}}
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Chat Support - Customer</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <!-- Website CSS để đồng bộ theme -->
        <link rel="stylesheet" href="{{ asset('css/customer.css') }}">
        
        <!-- Chat CSS - Vite với fallback -->
        @php
            $viteManifestExists = file_exists(public_path('build/manifest.json'));
        @endphp
        @if($viteManifestExists)
            @vite(['resources/css/chat.css'])
        @else
            <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
        @endif
        
        @yield('styles')
    </head>
    <body class="chat-page">
        <div class="chat-container">
            <!-- Header Card -->
            <div class="card chat-header-card">
                <div class="card-header">
                    <h5>
                        <i class="fas fa-comments"></i>
                        Chat Hỗ trợ Khách hàng
                    </h5>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span id="connectionStatus" class="connection-status text-info">
                            <i class="fas fa-circle"></i> Đang kết nối...
                        </span>
                        <span class="badge">
                            <i class="fas fa-user me-1"></i> {{ $currentUser->name }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Chat Panel -->
            <div class="card chat-panel">
                <!-- Panel Header -->
                <div class="chat-panel-header">
                    <div class="room-info">
                        <span class="text-muted">Phòng chat của Customer ID:</span>
                        <strong id="roomUserId">{{ $currentUser->id }}</strong>
                    </div>
                </div>

                <!-- Chat Messages Body -->
                <div id="chatBody" class="chat-panel-body">
                    <div class="text-center py-5">
                        <i class="fas fa-comment-dots"></i>
                        <p class="mt-3">Đang tải tin nhắn...</p>
                    </div>
                </div>

                <!-- Chat Input Footer -->
                <div class="chat-panel-footer">
                    <div class="chat-input-group">
                        <input 
                            id="messageInput" 
                            type="text" 
                            class="form-control" 
                            placeholder="Nhập tin nhắn..."
                            autocomplete="off"
                        >
                        <button id="sendBtn" class="btn btn-send">
                            <i class="fas fa-paper-plane"></i> Gửi
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Script -->
        <script>
            window.__CHAT_CONFIG__ = {
                apiToken: @json($apiToken),
                currentUserId: @json($currentUser->id),
                chatUserId: @json($chatUserId),
                pusher: @json($pusher),
                appUrl: @json(config('app.url')),
                apiBase: '/api',
                mode: 'user'
            };
        </script>

        <!-- Bootstrap 5 JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- Chat JS - Vite với fallback CDN -->
        @if($viteManifestExists)
            @vite(['resources/js/chat.js'])
        @else
            <script src="https://cdn.jsdelivr.net/npm/axios@1.7.7/dist/axios.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.2.0/dist/web/pusher.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
            <script src="{{ asset('js/chat-standalone.js') }}"></script>
        @endif
        
        @yield('scripts')
    </body>
    </html>
@endif
