<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetChatHistoryRequest;
use App\Http\Requests\SendChatMessageRequest;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected ChatService $chatService;

    /**
     * Constructor - Inject ChatService
     */
    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Lấy lịch sử tin nhắn cho một user
     */
    public function getHistory(GetChatHistoryRequest $request, int $userId): JsonResponse
    {
        // Authorization đã được xử lý trong GetChatHistoryRequest::authorize()

        // Lấy validated data với defaults
        $params = $request->validatedWithDefaults();

        try {
            // Gọi service để lấy lịch sử chat
            $messages = $this->chatService->getChatHistory(
                $userId,
                $params['limit'],
                $params['offset']
            );

            return response()->json($messages, 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => 'Bạn không có quyền xem lịch sử chat này.',
            ], 403);
        } catch (\Exception $e) {
            \Log::error('Chat history error', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => 'Không thể lấy lịch sử chat.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Gửi tin nhắn mới
     */
    public function sendMessage(SendChatMessageRequest $request, int $userId): JsonResponse
    {
        // Authorization đã được xử lý trong SendChatMessageRequest::authorize()
        // Validation đã được xử lý trong SendChatMessageRequest::rules()

        $user = Auth::user();

        try {
            // Gọi service để gửi tin nhắn
            $message = $this->chatService->sendMessage(
                $userId,
                $user->id,
                $request->input('message')
            );

            return response()->json($message, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không thể gửi tin nhắn.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Đếm tin nhắn chưa đọc (Bonus feature)
     */
    public function countUnread(int $userId): JsonResponse
    {
        $user = Auth::user();

        // Chỉ cho phép user xem số tin nhắn chưa đọc của chính mình
        if (! $user->canAccessDashboard() && $user->id != $userId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $lastReadMessageId = request()->input('last_read_message_id', 0);
            $unreadCount = $this->chatService->countUnreadMessages($userId, $lastReadMessageId);

            return response()->json([
                'unread_count' => $unreadCount,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không thể đếm tin nhắn chưa đọc.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Lấy danh sách các cuộc hội thoại (Admin only)
     */
    public function getConversationList(): JsonResponse
    {
        $user = Auth::user();

        // Chỉ admin/manager mới có thể xem danh sách cuộc hội thoại
        if (! $user->canAccessDashboard()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $conversations = $this->chatService->getConversationList();

            return response()->json($conversations, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không thể lấy danh sách cuộc hội thoại.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
