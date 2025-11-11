<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id(); // ID của tin nhắn

            // Đây là ID của Customer (người sở hữu cuộc trò chuyện)
            // Kể cả khi Admin gửi, nó vẫn trỏ tới Customer
            $table->foreignId('user_id') // ID của Customer (người sở hữu cuộc trò chuyện)
                ->constrained('users') // Liên kết với bảng users
                ->cascadeOnDelete(); // Xóa tin nhắn nếu user bị xóa

            // Đây là ID của người GỬI (có thể là Customer hoặc Admin)
            $table->foreignId('sender_id') // ID của người GỬI (có thể là Customer hoặc Admin)
                ->constrained('users') // Liên kết với bảng users
                ->cascadeOnDelete(); // Xóa tin nhắn nếu user bị xóa

            $table->text('message'); // Nội dung tin nhắn
            $table->timestamps(); // created_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
