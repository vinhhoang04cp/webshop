<?php

namespace App\Events;

use App\Models\ChatMessage; // Import model
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // Quan trọng
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewChatMessage implements ShouldBroadcast // Quan trọng
{
    use Dispatchable; // Dispatchable trait dung để phát sự kiện
    use InteractsWithSockets; // InteractsWithSockets trait để tương tác với socket
    use SerializesModels; // SerializesModels trait để tuần tự hóa model khi sự kiện được hàng đợi

    // Phải là public để Echo đọc được
    public ChatMessage $message; // Tin nhắn mới

    public function __construct(ChatMessage $message) // Nhận một đối tượng ChatMessage
    {
        $this->message = $message; // Gán tin nhắn mới cho thuộc tính
    }

    /**
     * Đặt tên event khi broadcast để client lắng nghe đơn giản với '.NewChatMessage'
     */
    public function broadcastAs(): string
    {
        return 'NewChatMessage';
    }

    public function broadcastOn(): array
    {
        // Phát sóng trên kênh riêng của customer
        return [
            new PrivateChannel('chat.user.'.$this->message->user_id),
        ];
    }
}
