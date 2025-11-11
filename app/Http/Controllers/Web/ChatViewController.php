<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatViewController extends Controller
{
    /**
     * Hiển thị trang chat giữa Customer và Admin/Manager.
     * - Customer: luôn chat trong phòng của chính mình (user_id = auth()->id()).
     * - Admin/Manager: có thể mở chat với một customer cụ thể bằng {userId}.
     */
    public function show(Request $request, ?int $userId = null)
    {
        $currentUser = Auth::user();

        // Xác định phòng chat (theo customer user_id)
        if ($currentUser->canAccessDashboard()) {
            // Admin/Manager: yêu cầu userId (customer) qua path hoặc query (?user_id=)
            $chatUserId = $userId ?? (int) $request->query('user_id', 0);
            $chatUser = $chatUserId > 0 ? User::find($chatUserId) : null;
            $view = 'chat.admin';
        } else {
            // Customer: luôn là phòng của chính mình
            $chatUserId = (int) $currentUser->id;
            $chatUser = $currentUser;
            $view = 'chat.user';
        }

        // Tạo API token tạm thời để gọi các API /api/chat/*
        // Lưu ý: token này chỉ phục vụ cho giao diện chat và có thể thu hồi khi cần.
        $apiToken = $currentUser->createToken('chat_ui')->plainTextToken;

        return view($view, [
            'currentUser' => $currentUser,
            'chatUser' => $chatUser,
            'chatUserId' => $chatUserId,
            'apiToken' => $apiToken,
            'mode' => $currentUser->canAccessDashboard() ? 'admin' : 'user',
            // Cấu hình Reverb/Pusher lấy từ env (render sang client)
            'pusher' => [
                'key' => env('VITE_REVERB_APP_KEY', env('PUSHER_APP_KEY', '')),
                'cluster' => env('VITE_REVERB_APP_CLUSTER', env('PUSHER_APP_CLUSTER', 'mt1')),
                'ws_host' => env('VITE_REVERB_HOST', env('PUSHER_HOST', request()->getHost())),
                'ws_port' => env('VITE_REVERB_PORT', env('PUSHER_PORT', 6001)),
                'wss_port' => env('VITE_REVERB_PORT', env('PUSHER_PORT', 6001)),
                'encrypted' => (bool) env('PUSHER_ENCRYPTED', true),
                'use_tls' => (bool) (env('VITE_REVERB_SCHEME', 'https') === 'https'),
            ],
        ]);
    }
}
