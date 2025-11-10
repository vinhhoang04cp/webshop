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
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    // Phải là public để Echo đọc được
    public ChatMessage $message;

    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        // Phát sóng trên kênh riêng của customer
        return [
            new PrivateChannel('chat.user.' . $this->message->user_id),
        ];
    }
}
