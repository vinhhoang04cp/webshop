<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.user.{userId}', function ($user, $userId) {

    // SỬA DÒNG NÀY:
    // if ($user->isAdminOrManager()) { // <--- DÒNG CŨ
    if ($user->canAccessDashboard()) { // <--- DÒNG MỚI (Dùng hàm có sẵn của bạn)

        return true;
    }

    // 2. Nếu là khách hàng, họ chỉ được truy cập kênh của chính mình
    return (int) $user->id === (int) $userId;
});
