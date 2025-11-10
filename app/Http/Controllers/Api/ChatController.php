<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ChatMessage;
use App\Events\NewChatMessage;

class ChatController extends Controller
{
    /**
     * Lấy lịch sử tin nhắn cho một user
     */
    public function getHistory(Request $request, $userId)
    {
        $user = Auth::user();

        // SỬA DÒNG NÀY:
        // if (!$user->isAdminOrManager() && $user->id != $userId) { // <--- DÒNG CŨ
        if (!$user->canAccessDashboard() && $user->id != $userId) { // <--- DÒNG MỚI

            return response()->json(['message' => 'Forbidden'], 403);
        }

        // ... code còn lại
    }

    /**
     * Gửi tin nhắn mới
     */
    public function sendMessage(Request $request, $userId)
    {
        $user = Auth::user(); // $user là người đang gửi

        // SỬA DÒNG NÀY:
        // if (!$user->isAdminOrManager() && $user->id != $userId) { // <--- DÒNG CŨ
        if (!$user->canAccessDashboard() && $user->id != $userId) { // <--- DÒNG MỚI

            return response()->json(['message' => 'Forbidden'], 403);
        }
        $request->validate([
            'message' => 'required|string',
        ]);

        $message = ChatMessage::create([
            'user_id' => $userId, // ID của customer (chủ phòng)
            'sender_id' => $user->id, // ID của người gửi (admin hoặc customer)
            'message' => $request->input('message'),
        ]);

        // Load thông tin người gửi để gửi qua event
        $message->load('sender');

        // Phát sóng event cho người khác
        broadcast(new NewChatMessage($message))->toOthers();

        // Trả về tin nhắn đã tạo (cho người gửi)
        return response()->json($message, 201);
    }
}
