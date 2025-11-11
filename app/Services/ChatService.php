<?php

namespace App\Services;

use App\Events\NewChatMessage;
use App\Models\ChatMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatService
{
    /**
     * Lấy lịch sử chat của một user
     *
     * @param  int  $userId  ID của user (customer)
     * @param  int  $limit  Số lượng tin nhắn tối đa
     * @param  int  $offset  Vị trí bắt đầu (phân trang)
     */
    public function getChatHistory(int $userId, int $limit = 50, int $offset = 0): Collection
    {
        try {
            $messages = ChatMessage::where('user_id', $userId)
                ->with('sender:id,name,email,avatar') // Eager load sender info
                ->orderBy('created_at', 'asc')
                ->skip($offset)
                ->take($limit)
                ->get();

            Log::info('Chat history retrieved', [
                'user_id' => $userId,
                'count' => $messages->count(),
                'limit' => $limit,
                'offset' => $offset,
            ]);

            return $messages;
        } catch (\Exception $e) {
            Log::error('Error retrieving chat history', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Gửi tin nhắn mới
     *
     * @param  int  $userId  ID của user (customer - chủ phòng)
     * @param  int  $senderId  ID của người gửi (admin hoặc customer)
     * @param  string  $messageContent  Nội dung tin nhắn
     */
    public function sendMessage(int $userId, int $senderId, string $messageContent): ChatMessage
    {
        DB::beginTransaction();

        try {
            // Tạo tin nhắn mới
            $message = ChatMessage::create([
                'user_id' => $userId,      // Chủ phòng chat
                'sender_id' => $senderId,  // Người gửi
                'message' => $messageContent,
            ]);

            // Load thông tin người gửi
            $message->load('sender:id,name,email,avatar');

            // Broadcast event cho real-time chat
            broadcast(new NewChatMessage($message))->toOthers();

            DB::commit();

            Log::info('Chat message sent', [
                'message_id' => $message->id,
                'user_id' => $userId,
                'sender_id' => $senderId,
            ]);

            return $message;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error sending chat message', [
                'user_id' => $userId,
                'sender_id' => $senderId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Đếm số tin nhắn chưa đọc của một user
     *
     * @param  int  $userId  ID của user
     * @param  int  $lastReadMessageId  ID của tin nhắn cuối cùng đã đọc
     */
    public function countUnreadMessages(int $userId, int $lastReadMessageId = 0): int
    {
        return ChatMessage::where('user_id', $userId)
            ->where('id', '>', $lastReadMessageId)
            ->where('sender_id', '!=', $userId) // Không tính tin nhắn của chính mình
            ->count();
    }

    /**
     * Xóa lịch sử chat của một user
     *
     * @param  int  $userId  ID của user
     */
    public function clearChatHistory(int $userId): bool
    {
        try {
            $deleted = ChatMessage::where('user_id', $userId)->delete();

            Log::info('Chat history cleared', [
                'user_id' => $userId,
                'deleted_count' => $deleted,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error clearing chat history', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Lấy danh sách các cuộc hội thoại (cho admin)
     */
    public function getConversationList(): Collection
    {
        try {
            // Lấy user_id duy nhất và tin nhắn cuối cùng
            $conversations = ChatMessage::select('user_id')
                ->selectRaw('MAX(created_at) as last_message_at')
                ->selectRaw('COUNT(*) as message_count')
                ->with([
                    'user:id,name,email,avatar',
                    'sender:id,name,email,avatar',
                ])
                ->groupBy('user_id')
                ->orderBy('last_message_at', 'desc')
                ->get();

            return $conversations;
        } catch (\Exception $e) {
            Log::error('Error getting conversation list', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Tìm kiếm tin nhắn
     *
     * @param  int  $userId  ID của user
     * @param  string  $keyword  Từ khóa tìm kiếm
     */
    public function searchMessages(int $userId, string $keyword): Collection
    {
        return ChatMessage::where('user_id', $userId)
            ->where('message', 'like', '%'.$keyword.'%')
            ->with('sender:id,name,email,avatar')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
