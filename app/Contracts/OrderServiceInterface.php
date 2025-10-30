<?php

namespace App\Contracts;

use App\Models\Order;

interface OrderServiceInterface
{
    /**
     * Lấy danh sách đơn hàng cho admin với filter
     *
     * @param  array  $filters
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getOrdersForAdmin(array $filters = [], int $perPage = 15);

    /**
     * Lấy chi tiết đơn hàng với items
     *
     * @param  int  $orderId
     * @return Order
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getOrderDetail($orderId);

    /**
     * Lấy order với items cho edit
     *
     * @param  int  $orderId
     * @return Order
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getOrderForEdit($orderId);

    /**
     * Cập nhật trạng thái đơn hàng
     *
     * @param  int  $orderId
     * @param  string  $newStatus
     * @return Order
     *
     * @throws \Exception
     */
    public function updateOrderStatus($orderId, $newStatus);

    /**
     * Xóa đơn hàng
     *
     * @param  int  $orderId
     * @return bool
     *
     * @throws \Exception
     */
    public function deleteOrder($orderId);

    /**
     * Lấy danh sách trạng thái có thể chuyển đổi
     *
     * @param  string  $currentStatus
     * @return array
     */
    public function getAvailableStatuses($currentStatus);

    /**
     * Lấy tất cả trạng thái
     *
     * @return array
     */
    public function getAllStatuses();

    /**
     * Lấy nhãn trạng thái tiếng Việt
     *
     * @param  string  $status
     * @return string
     */
    public function getStatusLabel($status);

    /**
     * Get order items with filters (for API and Web)
     *
     * @param  array  $filters
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getOrderItems(array $filters = [], int $perPage = 10);

    /**
     * Find order item by ID with optional relationships
     *
     * @param  int  $orderItemId
     * @param  bool  $withRelations
     * @return \App\Models\OrderItem|null
     */
    public function findOrderItem($orderItemId, $withRelations = true);

    /**
     * Create order item
     *
     * @param  array  $data
     * @return \App\Models\OrderItem
     */
    public function createOrderItem(array $data);

    /**
     * Update order item
     *
     * @param  int  $orderItemId
     * @param  array  $data
     * @return \App\Models\OrderItem
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateOrderItem($orderItemId, array $data);

    /**
     * Delete order item
     *
     * @param  int  $orderItemId
     * @return bool
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteOrderItem($orderItemId);

    /**
     * Get orders with filters (for API and Web)
     *
     * @param  int  $userId
     * @param  bool  $isAdmin
     * @param  array  $filters
     * @param  int  $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getOrders($userId, $isAdmin, array $filters = [], int $perPage = 10);

    /**
     * Find order by ID with optional relationships
     *
     * @param  int  $orderId
     * @param  bool  $withRelations
     * @return Order|null
     */
    public function findOrder($orderId, $withRelations = true);

    /**
     * Create order with items
     *
     * @param  array  $data
     * @return Order
     *
     * @throws \Exception
     */
    public function createOrder(array $data);

    /**
     * Update order
     *
     * @param  int  $orderId
     * @param  array  $data
     * @param  Order|null  $order
     * @return Order
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateOrder($orderId, array $data, $order = null);

    /**
     * Delete order by ID
     *
     * @param  int  $orderId
     * @return bool
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteOrderById($orderId);

    /**
     * Check if user owns order
     *
     * @param  Order  $order
     * @param  int  $userId
     * @return bool
     */
    public function userOwnsOrder($order, $userId);

    /**
     * Check if order can transition to status
     *
     * @param  Order  $order
     * @param  string  $newStatus
     * @return bool
     */
    public function canTransitionToStatus($order, $newStatus);

    /**
     * Lấy đơn hàng để xử lý thanh toán
     * Kiểm tra quyền sở hữu đơn hàng
     *
     * @param  int  $orderId
     * @param  int  $userId
     * @return Order
     *
     * @throws \Exception
     */
    public function getOrderForPayment($orderId, $userId);

    /**
     * Lấy đơn hàng với items để hiển thị
     *
     * @param  int  $orderId
     * @return Order
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getOrderWithItemsForDisplay($orderId);

    /**
     * Lấy đơn hàng cơ bản theo ID
     *
     * @param  int  $orderId
     * @return Order
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getOrderById($orderId);

    /**
     * Get order statistics (for API)
     *
     * @param  int|null  $userId
     * @param  bool  $isAdmin
     * @return array
     */
    public function getOrderStats($userId = null, $isAdmin = false);
}
