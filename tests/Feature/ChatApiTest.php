<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $customer;

    protected $admin;

    protected $manager;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Tạo roles nếu chưa tồn tại
        $customerRole = \App\Models\Role::firstOrCreate(
            ['role_name' => 'customer'],
            ['role_display_name' => 'Customer']
        );
        $adminRole = \App\Models\Role::firstOrCreate(
            ['role_name' => 'admin'],
            ['role_display_name' => 'Admin']
        );
        $managerRole = \App\Models\Role::firstOrCreate(
            ['role_name' => 'manager'],
            ['role_display_name' => 'Manager']
        );

        // Tạo customer
        $this->customer = User::factory()->create([
            'name' => 'Customer Test',
            'email' => 'customer@test.com',
        ]);
        $this->customer->roles()->attach($customerRole->role_id);

        // Tạo admin
        $this->admin = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
        ]);
        $this->admin->roles()->attach($adminRole->role_id);

        // Tạo manager
        $this->manager = User::factory()->create([
            'name' => 'Manager Test',
            'email' => 'manager@test.com',
        ]);
        $this->manager->roles()->attach($managerRole->role_id);
    }

    /**
     * Test customer gửi tin nhắn cho admin
     */
    public function test_customer_can_send_message()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/chat/user/{$this->customer->id}/message", [
                'message' => 'Xin chào, tôi cần hỗ trợ!',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'sender_id',
                'message',
                'created_at',
                'updated_at',
                'sender' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);

        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $this->customer->id,
            'sender_id' => $this->customer->id,
            'message' => 'Xin chào, tôi cần hỗ trợ!',
        ]);
    }

    /**
     * Test admin trả lời tin nhắn của customer
     */
    public function test_admin_can_reply_to_customer()
    {
        // Customer gửi tin nhắn trước
        ChatMessage::create([
            'user_id' => $this->customer->id,
            'sender_id' => $this->customer->id,
            'message' => 'Sản phẩm có bảo hành không?',
        ]);

        // Admin trả lời
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/chat/user/{$this->customer->id}/message", [
                'message' => 'Dạ có, sản phẩm được bảo hành 12 tháng.',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'user_id' => $this->customer->id,
                'sender_id' => $this->admin->id,
                'message' => 'Dạ có, sản phẩm được bảo hành 12 tháng.',
            ]);

        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $this->customer->id,
            'sender_id' => $this->admin->id,
            'message' => 'Dạ có, sản phẩm được bảo hành 12 tháng.',
        ]);
    }

    /**
     * Test customer lấy lịch sử chat của chính mình
     */
    public function test_customer_can_get_own_chat_history()
    {
        // Tạo một số tin nhắn
        ChatMessage::create([
            'user_id' => $this->customer->id,
            'sender_id' => $this->customer->id,
            'message' => 'Tin nhắn 1',
        ]);

        ChatMessage::create([
            'user_id' => $this->customer->id,
            'sender_id' => $this->admin->id,
            'message' => 'Tin nhắn 2',
        ]);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/chat/user/{$this->customer->id}/history");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'user_id',
                    'sender_id',
                    'message',
                    'created_at',
                    'sender',
                ],
            ]);
    }

    /**
     * Test customer không thể xem chat của người khác
     */
    public function test_customer_cannot_view_other_chat()
    {
        $customerRole = \App\Models\Role::where('role_name', 'customer')->first();
        $anotherCustomer = User::factory()->create();
        $anotherCustomer->roles()->attach($customerRole->role_id);

        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/chat/user/{$anotherCustomer->id}/history");

        $response->assertStatus(403);
    }

    /**
     * Test admin có thể xem chat của bất kỳ customer nào
     */
    public function test_admin_can_view_any_customer_chat()
    {
        ChatMessage::create([
            'user_id' => $this->customer->id,
            'sender_id' => $this->customer->id,
            'message' => 'Test message',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/chat/user/{$this->customer->id}/history");

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    /**
     * Test manager có thể xem chat của bất kỳ customer nào
     */
    public function test_manager_can_view_any_customer_chat()
    {
        ChatMessage::create([
            'user_id' => $this->customer->id,
            'sender_id' => $this->customer->id,
            'message' => 'Test message',
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson("/api/chat/user/{$this->customer->id}/history");

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    /**
     * Test phân trang lịch sử chat
     */
    public function test_chat_history_pagination()
    {
        // Tạo 10 tin nhắn
        for ($i = 1; $i <= 10; $i++) {
            ChatMessage::create([
                'user_id' => $this->customer->id,
                'sender_id' => $this->customer->id,
                'message' => "Message $i",
            ]);
        }

        // Lấy 5 tin nhắn đầu
        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/chat/user/{$this->customer->id}/history?limit=5&offset=0");

        $response->assertStatus(200)
            ->assertJsonCount(5);

        // Lấy 5 tin nhắn tiếp theo
        $response2 = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/chat/user/{$this->customer->id}/history?limit=5&offset=5");

        $response2->assertStatus(200)
            ->assertJsonCount(5);
    }

    /**
     * Test đếm tin nhắn chưa đọc
     */
    public function test_count_unread_messages()
    {
        // Tạo 3 tin nhắn
        $msg1 = ChatMessage::create([
            'user_id' => $this->customer->id,
            'sender_id' => $this->admin->id,
            'message' => 'Message 1',
        ]);

        $msg2 = ChatMessage::create([
            'user_id' => $this->customer->id,
            'sender_id' => $this->admin->id,
            'message' => 'Message 2',
        ]);

        $msg3 = ChatMessage::create([
            'user_id' => $this->customer->id,
            'sender_id' => $this->admin->id,
            'message' => 'Message 3',
        ]);

        // Đếm tin nhắn chưa đọc sau message 1
        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/chat/user/{$this->customer->id}/unread?last_read_message_id={$msg1->id}");

        $response->assertStatus(200)
            ->assertJson([
                'unread_count' => 2,
            ]);
    }

    /**
     * Test admin lấy danh sách cuộc hội thoại
     */
    public function test_admin_can_get_conversation_list()
    {
        // Tạo tin nhắn từ nhiều customer khác nhau
        $customerRole = \App\Models\Role::where('role_name', 'customer')->first();

        $customer1 = User::factory()->create();
        $customer1->roles()->attach($customerRole->role_id);

        $customer2 = User::factory()->create();
        $customer2->roles()->attach($customerRole->role_id);

        ChatMessage::create([
            'user_id' => $customer1->id,
            'sender_id' => $customer1->id,
            'message' => 'Customer 1 message',
        ]);

        ChatMessage::create([
            'user_id' => $customer2->id,
            'sender_id' => $customer2->id,
            'message' => 'Customer 2 message',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/chat/conversations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'user_id',
                    'last_message_at',
                    'message_count',
                    'user',
                ],
            ]);
    }

    /**
     * Test customer không thể lấy danh sách cuộc hội thoại
     */
    public function test_customer_cannot_get_conversation_list()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson('/api/chat/conversations');

        $response->assertStatus(403);
    }

    /**
     * Test validation khi gửi tin nhắn trống
     */
    public function test_cannot_send_empty_message()
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/chat/user/{$this->customer->id}/message", [
                'message' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    /**
     * Test validation khi gửi tin nhắn quá dài
     */
    public function test_cannot_send_too_long_message()
    {
        $longMessage = str_repeat('a', 5001); // Giả sử max là 5000 ký tự

        $response = $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/chat/user/{$this->customer->id}/message", [
                'message' => $longMessage,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    /**
     * Test unauthenticated user không thể truy cập chat
     */
    public function test_unauthenticated_cannot_access_chat()
    {
        $response = $this->getJson("/api/chat/user/{$this->customer->id}/history");
        $response->assertStatus(401);

        $response2 = $this->postJson("/api/chat/user/{$this->customer->id}/message", [
            'message' => 'Test',
        ]);
        $response2->assertStatus(401);
    }

    /**
     * Test flow chat đầy đủ giữa customer và admin
     */
    public function test_full_chat_flow_between_customer_and_admin()
    {
        // 1. Customer gửi tin nhắn
        $response1 = $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/chat/user/{$this->customer->id}/message", [
                'message' => 'Xin chào, tôi muốn hỏi về sản phẩm',
            ]);
        $response1->assertStatus(201);

        // 2. Admin xem danh sách cuộc hội thoại
        $response2 = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/chat/conversations');
        $response2->assertStatus(200)
            ->assertJsonCount(1);

        // 3. Admin xem lịch sử chat với customer
        $response3 = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/chat/user/{$this->customer->id}/history");
        $response3->assertStatus(200)
            ->assertJsonCount(1);

        // 4. Admin trả lời
        $response4 = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/chat/user/{$this->customer->id}/message", [
                'message' => 'Dạ, em có thể giúp gì cho anh?',
            ]);
        $response4->assertStatus(201);

        // 5. Customer xem tin nhắn chưa đọc
        $firstMessageId = $response1->json('id');
        $response5 = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/chat/user/{$this->customer->id}/unread?last_read_message_id={$firstMessageId}");
        $response5->assertStatus(200)
            ->assertJson(['unread_count' => 1]);

        // 6. Customer xem lịch sử chat
        $response6 = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/chat/user/{$this->customer->id}/history");
        $response6->assertStatus(200)
            ->assertJsonCount(2);

        // 7. Customer trả lời lại
        $response7 = $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/chat/user/{$this->customer->id}/message", [
                'message' => 'Sản phẩm A có màu đỏ không?',
            ]);
        $response7->assertStatus(201);

        // 8. Verify tổng số tin nhắn
        $finalHistory = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/chat/user/{$this->customer->id}/history");
        $finalHistory->assertStatus(200)
            ->assertJsonCount(3);
    }
}
