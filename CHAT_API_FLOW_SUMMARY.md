# 📱 TÓM TẮT LUỒNG HOẠT ĐỘNG CHAT API

## 🏗️ Kiến trúc tổng quan

```
┌─────────────┐      ┌──────────────┐      ┌─────────────┐      ┌──────────┐
│   Client    │─────▶│ ChatController│─────▶│ ChatService │─────▶│ Database │
│ (Admin/User)│◀─────│   (API)      │◀─────│  (Logic)    │◀─────│  (MySQL) │
└─────────────┘      └──────────────┘      └─────────────┘      └──────────┘
                            │
                            ▼
                     ┌──────────────┐
                     │ Broadcasting │
                     │  (Real-time) │
                     └──────────────┘
```

---

## 📊 Cấu trúc Database

### Bảng `chat_messages`

| Field | Type | Mô tả |
|-------|------|-------|
| `id` | bigint | ID tin nhắn (Primary Key) |
| `user_id` | bigint | **ID của Customer** (chủ phòng chat) |
| `sender_id` | bigint | **ID người gửi** (có thể là Admin/Manager/Customer) |
| `message` | text | Nội dung tin nhắn |
| `created_at` | timestamp | Thời gian gửi |
| `updated_at` | timestamp | Thời gian cập nhật |

**Quan trọng:**
- `user_id`: Luôn là ID của **Customer** - người sở hữu phòng chat
- `sender_id`: ID của người **thực sự gửi** tin nhắn (có thể khác `user_id`)

**Ví dụ:**
```
Customer (ID: 22) gửi tin nhắn:
  user_id = 22, sender_id = 22

Admin (ID: 21) trả lời Customer (ID: 22):
  user_id = 22, sender_id = 21
```

---

## 🔐 Phân quyền (Authorization)

### Quy tắc phân quyền

| Role | Xem conversations | Xem history | Gửi tin nhắn | Xem unread |
|------|------------------|-------------|--------------|------------|
| **Admin** | ✅ Tất cả | ✅ Tất cả users | ✅ Cho bất kỳ user | ✅ Tất cả |
| **Manager** | ✅ Tất cả | ✅ Tất cả users | ✅ Cho bất kỳ user | ✅ Tất cả |
| **Customer** | ❌ Không | ✅ Chỉ của mình | ✅ Chỉ vào phòng mình | ✅ Chỉ của mình |

### Cách kiểm tra quyền

```php
// Trong Request classes
public function authorize(): bool
{
    $user = Auth::user();
    $userId = $this->route('userId');
    
    // Admin/Manager có full quyền
    if ($user->canAccessDashboard()) {
        return true;
    }
    
    // Customer chỉ truy cập phòng của mình
    return $user->id == $userId;
}
```

---

## 🔄 Các luồng chính

### 1️⃣ Luồng Customer gửi tin nhắn

```
Customer
   │
   ├─ POST /api/chat/user/{userId}/message
   │  Headers: Authorization: Bearer {token}
   │  Body: { "message": "Xin chào..." }
   │
   ▼
ChatController::sendMessage()
   │
   ├─ Kiểm tra authorization (SendChatMessageRequest)
   │  └─ Customer chỉ gửi vào phòng của mình (userId == Auth::id())
   │
   ├─ Validate message (max 5000 ký tự)
   │
   ▼
ChatService::sendMessage()
   │
   ├─ BEGIN TRANSACTION
   │
   ├─ Tạo ChatMessage mới
   │  └─ user_id = userId (Customer)
   │  └─ sender_id = Auth::id() (Customer)
   │  └─ message = nội dung
   │
   ├─ Load thông tin sender (eager loading)
   │
   ├─ Broadcast event NewChatMessage (real-time)
   │  └─ Gửi đến các clients khác qua WebSocket
   │
   ├─ COMMIT TRANSACTION
   │
   └─ Log thông tin
   │
   ▼
Response: 201 Created
{
  "id": 8,
  "user_id": 22,
  "sender_id": 22,
  "message": "Xin chào...",
  "created_at": "2025-11-11T08:06:00.000000Z",
  "sender": {
    "id": 22,
    "name": "Customer User",
    "email": "customer@webshop.com",
    "avatar": null
  }
}
```

### 2️⃣ Luồng Admin/Manager xem danh sách conversations

```
Admin/Manager
   │
   ├─ GET /api/chat/conversations
   │  Headers: Authorization: Bearer {token}
   │
   ▼
ChatController::getConversationList()
   │
   ├─ Kiểm tra quyền
   │  └─ Chỉ Admin/Manager mới truy cập được
   │  └─ if (!$user->canAccessDashboard()) → 403 Forbidden
   │
   ▼
ChatService::getConversationList()
   │
   ├─ Query database:
   │  └─ SELECT user_id, 
   │           MAX(created_at) as last_message_at,
   │           COUNT(*) as message_count
   │     FROM chat_messages
   │     GROUP BY user_id
   │     ORDER BY last_message_at DESC
   │
   ├─ Eager load user info và sender info
   │
   └─ Log thông tin
   │
   ▼
Response: 200 OK
[
  {
    "user_id": 22,
    "last_message_at": "2025-11-11 08:06:00",
    "message_count": 7,
    "user": {
      "id": 22,
      "name": "Customer User",
      "email": "customer@webshop.com",
      "avatar": null
    }
  },
  ...
]
```

### 3️⃣ Luồng Admin trả lời Customer

```
Admin
   │
   ├─ POST /api/chat/user/22/message
   │  Headers: Authorization: Bearer {admin_token}
   │  Body: { "message": "Dạ, em có thể giúp gì..." }
   │
   ▼
ChatController::sendMessage()
   │
   ├─ Kiểm tra authorization
   │  └─ Admin có quyền gửi cho bất kỳ user nào ✅
   │
   ▼
ChatService::sendMessage()
   │
   ├─ Tạo ChatMessage
   │  └─ user_id = 22 (Customer - chủ phòng)
   │  └─ sender_id = 21 (Admin - người gửi)
   │  └─ message = nội dung
   │
   ├─ Broadcast event (Customer sẽ nhận real-time)
   │
   └─ Return message
   │
   ▼
Response: 201 Created
{
  "id": 9,
  "user_id": 22,
  "sender_id": 21,  ← Admin gửi
  "message": "Dạ, em có thể giúp gì...",
  "sender": {
    "id": 21,
    "name": "Administrator",
    ...
  }
}
```

### 4️⃣ Luồng xem lịch sử chat

```
User (Admin/Manager/Customer)
   │
   ├─ GET /api/chat/user/{userId}/history?limit=50&offset=0
   │  Headers: Authorization: Bearer {token}
   │
   ▼
ChatController::getHistory()
   │
   ├─ Kiểm tra authorization (GetChatHistoryRequest)
   │  ├─ Admin/Manager: ✅ Xem tất cả
   │  └─ Customer: ✅ Chỉ xem của mình
   │
   ├─ Validate & set defaults
   │  └─ limit: default 50, max 100
   │  └─ offset: default 0
   │
   ▼
ChatService::getChatHistory()
   │
   ├─ Query database:
   │  └─ WHERE user_id = {userId}
   │     ORDER BY created_at ASC
   │     SKIP {offset}
   │     TAKE {limit}
   │
   ├─ Eager load sender info
   │
   └─ Log thông tin
   │
   ▼
Response: 200 OK
[
  {
    "id": 8,
    "user_id": 22,
    "sender_id": 22,
    "message": "Xin chào...",
    "created_at": "2025-11-11T08:06:00.000000Z",
    "sender": { ... }
  },
  {
    "id": 9,
    "user_id": 22,
    "sender_id": 21,  ← Admin trả lời
    "message": "Dạ, em có thể...",
    "created_at": "2025-11-11T08:06:01.000000Z",
    "sender": { ... }
  }
]
```

### 5️⃣ Luồng đếm tin nhắn chưa đọc

```
Customer
   │
   ├─ GET /api/chat/user/{userId}/unread?last_read_message_id=8
   │  Headers: Authorization: Bearer {token}
   │
   ▼
ChatController::countUnread()
   │
   ├─ Kiểm tra quyền
   │  └─ Customer chỉ xem của mình
   │  └─ Admin/Manager xem tất cả
   │
   ▼
ChatService::countUnreadMessages()
   │
   ├─ Query database:
   │  └─ WHERE user_id = {userId}
   │     AND id > {lastReadMessageId}
   │     AND sender_id != {userId}  ← Không tính tin nhắn của mình
   │     COUNT(*)
   │
   ▼
Response: 200 OK
{
  "unread_count": 1
}
```

---

## 🎯 Các tính năng đặc biệt

### 1. Real-time Broadcasting

```php
// Trong ChatService::sendMessage()
broadcast(new NewChatMessage($message))->toOthers();
```

**Hoạt động:**
- Khi có tin nhắn mới, event được broadcast qua WebSocket
- Các clients đang online sẽ nhận được tin nhắn ngay lập tức
- Không cần refresh hoặc polling

### 2. Transaction Safety

```php
DB::beginTransaction();
try {
    // Tạo message
    // Broadcast event
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

**Lợi ích:**
- Đảm bảo data integrity
- Rollback nếu có lỗi
- Không có tin nhắn bị mất

### 3. Eager Loading

```php
->with('sender:id,name,email,avatar')
```

**Lợi ích:**
- Giảm N+1 query problem
- Load thông tin sender cùng lúc với message
- Tăng performance đáng kể

### 4. Logging

```php
Log::info('Chat message sent', [
    'message_id' => $message->id,
    'user_id' => $userId,
    'sender_id' => $senderId,
]);
```

**Lợi ích:**
- Theo dõi hoạt động chat
- Debug khi có vấn đề
- Audit trail

---

## 🔍 Các validation quan trọng

### SendChatMessageRequest

```php
public function rules(): array
{
    return [
        'message' => [
            'required',
            'string',
            'max:5000',  // Giới hạn 5000 ký tự
            'min:1',
        ],
    ];
}
```

### GetChatHistoryRequest

```php
public function rules(): array
{
    return [
        'limit' => 'integer|min:1|max:100',   // Tối đa 100 tin
        'offset' => 'integer|min:0',
    ];
}
```

---

## 📈 Flow diagram tổng hợp

```
┌─────────────────────────────────────────────────────────────┐
│                    CUSTOMER CHAT FLOW                        │
└─────────────────────────────────────────────────────────────┘

Customer gửi tin nhắn
        │
        ▼
    Validate
        │
        ├─ Authorization: userId == Auth::id() ✅
        ├─ Message: required, max 5000 chars
        │
        ▼
   Save to DB
        │
        ├─ user_id = Customer ID
        ├─ sender_id = Customer ID
        │
        ▼
   Broadcast
        │
        └─ Admin/Manager nhận real-time
        
        
┌─────────────────────────────────────────────────────────────┐
│                  ADMIN/MANAGER CHAT FLOW                     │
└─────────────────────────────────────────────────────────────┘

1. Xem conversations
        │
        ▼
   Get all user_id
        │
        ├─ Group by user_id
        ├─ Count messages
        ├─ Last message time
        │
        ▼
   Display list

2. Chọn customer
        │
        ▼
   Get chat history
        │
        ├─ WHERE user_id = selected_customer
        ├─ ORDER BY created_at ASC
        │
        ▼
   Display messages

3. Trả lời customer
        │
        ▼
   Send message
        │
        ├─ user_id = Customer ID
        ├─ sender_id = Admin/Manager ID
        │
        ▼
   Broadcast
        │
        └─ Customer nhận real-time
```

---

## 🛡️ Error Handling

### Các trường hợp lỗi được xử lý

1. **Unauthorized (401)**
   - Token không hợp lệ hoặc hết hạn
   - Middleware `auth:sanctum` xử lý

2. **Forbidden (403)**
   - Customer cố xem chat của người khác
   - Customer cố xem conversations list
   - Request authorization xử lý

3. **Validation Error (422)**
   - Message quá dài (>5000 chars)
   - Message rỗng
   - Limit/offset không hợp lệ

4. **Server Error (500)**
   - Database connection failed
   - Transaction rollback
   - Try-catch blocks xử lý

---

## 💡 Best Practices được áp dụng

1. ✅ **Service Layer Pattern**: Logic tách riêng khỏi Controller
2. ✅ **Form Request Validation**: Validation và authorization tách riêng
3. ✅ **Eager Loading**: Tránh N+1 query problem
4. ✅ **Database Transactions**: Đảm bảo data integrity
5. ✅ **Logging**: Theo dõi và debug dễ dàng
6. ✅ **Broadcasting**: Real-time updates
7. ✅ **RESTful API**: Tuân thủ chuẩn REST
8. ✅ **Error Handling**: Xử lý lỗi đầy đủ

---

## 📝 Tóm tắt

**Chat API này hoạt động theo mô hình:**

1. **Mỗi Customer có một "phòng chat" riêng** (được xác định bởi `user_id`)
2. **Admin/Manager có thể truy cập tất cả phòng chat** để hỗ trợ
3. **Customer chỉ truy cập phòng của mình**
4. **Tin nhắn được lưu với 2 thông tin quan trọng:**
   - `user_id`: Chủ phòng (Customer)
   - `sender_id`: Người gửi (có thể là Customer hoặc Admin/Manager)
5. **Real-time updates** qua Broadcasting/WebSocket
6. **Phân quyền chặt chẽ** theo role
7. **Performance tốt** nhờ eager loading và pagination

**Kết quả:** Một hệ thống chat đơn giản nhưng hiệu quả, phù hợp cho customer support!

