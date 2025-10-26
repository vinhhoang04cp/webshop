<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Lấy danh sách đơn hàng cho admin với filter
     */
    public function getOrdersForAdmin(array $filters = [], int $perPage = 15)
    {
        $query = Order::with(['user', 'items.product']);

        // Tìm kiếm theo order_id hoặc user info
        if (! empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where('order_id', 'LIKE', "%{$searchTerm}%")
                ->orWhereHas('user', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                });
        }

        // Filter theo trạng thái
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Sắp xếp theo ngày mới nhất
        $query->orderBy('order_date', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Lấy chi tiết đơn hàng với items
     */
    public function getOrderDetail($orderId)
    {
        return Order::with(['user', 'items.product', 'items.productDetail'])
            ->findOrFail($orderId);
    }

    /**
     * Lấy order với items cho edit
     */
    public function getOrderForEdit($orderId)
    {
        return Order::with(['user', 'items.product'])->findOrFail($orderId);
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateOrderStatus($orderId, $newStatus)
    {
        $order = Order::findOrFail($orderId);
        $oldStatus = $order->status;

        // Kiểm tra có thể chuyển đổi trạng thái không
        if (! $order->canTransitionTo($newStatus)) {
            throw new \Exception("Không thể chuyển đổi trạng thái đơn hàng từ \"{$this->getStatusLabel($oldStatus)}\" sang \"{$this->getStatusLabel($newStatus)}\"");
        }

        // Sử dụng transaction
        DB::transaction(function () use ($order, $newStatus, $oldStatus) {
            // Cập nhật trạng thái
            $order->update(['status' => $newStatus]);

            // Xử lý logic theo trạng thái
            if ($newStatus === Order::STATUS_DELIVERED && $oldStatus !== Order::STATUS_DELIVERED) {
                $this->handleOrderDelivered($order);
            }

            if ($newStatus === Order::STATUS_CANCELLED && $oldStatus !== Order::STATUS_CANCELLED) {
                $this->handleOrderCancelled($order);
            }
        });

        return $order->fresh();
    }

    /**
     * Xóa đơn hàng
     */
    public function deleteOrder($orderId)
    {
        $order = Order::findOrFail($orderId);

        // Chỉ cho phép xóa đơn đã hủy hoặc đã giao
        if (! in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_DELIVERED])) {
            throw new \Exception('Chỉ có thể xóa đơn hàng đã hủy hoặc đã giao!');
        }

        $order->delete();

        return true;
    }

    /**
     * Lấy danh sách trạng thái có thể chuyển đổi
     */
    public function getAvailableStatuses($currentStatus)
    {
        $allStatuses = [
            Order::STATUS_PENDING => 'Chờ xử lý',
            Order::STATUS_PROCESSING => 'Đang xử lý',
            Order::STATUS_SHIPPED => 'Đã gửi hàng',
            Order::STATUS_DELIVERED => 'Đã giao hàng',
            Order::STATUS_CANCELLED => 'Đã hủy',
        ];

        $availableTransitions = Order::STATUS_TRANSITIONS[$currentStatus] ?? [];

        $result = [];
        foreach ($availableTransitions as $status) {
            $result[$status] = $allStatuses[$status];
        }

        return $result;
    }

    /**
     * Lấy tất cả trạng thái
     */
    public function getAllStatuses()
    {
        return [
            Order::STATUS_PENDING => 'Chờ xử lý',
            Order::STATUS_PROCESSING => 'Đang xử lý',
            Order::STATUS_SHIPPED => 'Đã gửi hàng',
            Order::STATUS_DELIVERED => 'Đã giao hàng',
            Order::STATUS_CANCELLED => 'Đã hủy',
        ];
    }

    /**
     * Lấy nhãn trạng thái tiếng Việt
     */
    public function getStatusLabel($status)
    {
        $labels = [
            Order::STATUS_PENDING => 'Chờ xử lý',
            Order::STATUS_PROCESSING => 'Đang xử lý',
            Order::STATUS_SHIPPED => 'Đã gửi hàng',
            Order::STATUS_DELIVERED => 'Đã giao hàng',
            Order::STATUS_CANCELLED => 'Đã hủy',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Xử lý khi đơn hàng được giao
     */
    protected function handleOrderDelivered(Order $order)
    {
        // Tồn kho đã được giảm khi checkout
        // Chỉ cần log hoặc tracking
        \Log::info("Order #{$order->order_id} delivered successfully. Stock was already deducted at checkout.");
    }

    /**
     * Xử lý khi đơn hàng bị hủy - hoàn trả tồn kho
     */
    protected function handleOrderCancelled(Order $order)
    {
        $orderItems = $order->items()->with('product')->get();

        foreach ($orderItems as $item) {
            if (! $item->product) {
                continue;
            }

            $product = $item->product;
            $quantity = $item->quantity;

            // Tăng lại stock_quantity
            $product->increment('stock_quantity', $quantity);

            // Cập nhật inventory
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $product->product_id],
                [
                    'stock_in' => 0,
                    'stock_out' => 0,
                    'current_stock' => 0,
                ]
            );

            // Giảm stock_out và tăng current_stock
            if ($inventory->stock_out >= $quantity) {
                $inventory->decrement('stock_out', $quantity);
            }
            $inventory->increment('current_stock', $quantity);
        }
    }

    /**
     * Get order items with filters (for API and Web)
     */
    public function getOrderItems(array $filters = [], int $perPage = 10)
    {
        $query = OrderItem::with(['product.category', 'order.user']);

        if (isset($filters['order_id'])) {
            $query->where('order_id', $filters['order_id']);
        }
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        if (isset($filters['min_quantity'])) {
            $query->where('quantity', '>=', $filters['min_quantity']);
        }
        if (isset($filters['max_quantity'])) {
            $query->where('quantity', '<=', $filters['max_quantity']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Find order item by ID with optional relationships
     */
    public function findOrderItem($orderItemId, $withRelations = true)
    {
        $query = OrderItem::query();

        if ($withRelations) {
            $query->with(['product.category', 'order']);
        }

        return $query->find($orderItemId);
    }

    /**
     * Create order item
     */
    public function createOrderItem(array $data)
    {
        $orderItem = OrderItem::create($data);

        try {
            OrderItem::reorderIds();
        } catch (\Exception $e) {
            \Log::warning('Failed to reorder OrderItem IDs: '.$e->getMessage());
        }

        return $orderItem->fresh();
    }

    /**
     * Update order item
     */
    public function updateOrderItem($orderItemId, array $data)
    {
        $orderItem = OrderItem::findOrFail($orderItemId);
        $orderItem->update($data);

        try {
            OrderItem::reorderIds();
        } catch (\Exception $e) {
            \Log::warning('Failed to reorder OrderItem IDs: '.$e->getMessage());
        }

        return $orderItem->fresh();
    }

    /**
     * Delete order item
     */
    public function deleteOrderItem($orderItemId)
    {
        $orderItem = OrderItem::findOrFail($orderItemId);
        $orderItem->delete();

        try {
            OrderItem::reorderIds();
        } catch (\Exception $e) {
            \Log::warning('Failed to reorder OrderItem IDs: '.$e->getMessage());
        }

        return true;
    }

    /**
     * Get orders with filters (for API and Web)
     */
    public function getOrders($userId, $isAdmin, array $filters = [], int $perPage = 10)
    {
        $query = Order::with(['user', 'items.product.category']);

        if (! $isAdmin) {
            $query->where('user_id', $userId);
        } else {
            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }
            if (isset($filters['min_date'])) {
                $query->where('order_date', '>=', $filters['min_date']);
            }
            if (isset($filters['max_date'])) {
                $query->where('order_date', '<=', $filters['max_date']);
            }
            if (isset($filters['min_total'])) {
                $query->where('total_amount', '>=', $filters['min_total']);
            }
            if (isset($filters['max_total'])) {
                $query->where('total_amount', '<=', $filters['max_total']);
            }
        }

        // Status filter
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Search filter (order_id, user name, user email)
        if (isset($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('order_id', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                    });
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'order_date';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Find order by ID with optional relationships
     */
    public function findOrder($orderId, $withRelations = true)
    {
        $query = Order::query();

        if ($withRelations) {
            $query->with(['user', 'items.product.category']);
        }

        return $query->where('order_id', $orderId)->first();
    }

    /**
     * Create order with items
     */
    public function createOrder(array $data)
    {
        $items = $data['items'];
        unset($data['items']);

        // Validate stock
        $stockValidation = $this->validateStock($items);
        if (! $stockValidation['valid']) {
            throw new \Exception(implode(', ', $stockValidation['errors']));
        }

        $itemsWithPrices = $this->calculateItemPrices($items);
        $data['total_amount'] = $this->calculateTotalAmount($itemsWithPrices);

        $order = Order::create($data);
        $this->createOrderItems($order, $itemsWithPrices);
        $this->updateStock($items);

        return $order->fresh(['items']);
    }

    /**
     * Update order
     */
    public function updateOrder($orderId, array $data, $order = null)
    {
        if (! $order) {
            $order = Order::findOrFail($orderId);
        }

        $items = $data['items'] ?? [];
        unset($data['items']);

        if (! empty($items)) {
            $order->items()->delete();
            $itemsWithPrices = $this->calculateItemPrices($items);
            $data['total_amount'] = $this->calculateTotalAmount($itemsWithPrices);
            $this->createOrderItems($order, $itemsWithPrices);
        }

        $order->update($data);

        return $order->fresh(['items']);
    }

    /**
     * Delete order
     */
    public function deleteOrderById($orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->delete();

        try {
            Order::reOrderIds();
        } catch (\Exception $e) {
            \Log::warning('Failed to reorder Order IDs: '.$e->getMessage());
        }

        return true;
    }

    /**
     * Check if user owns order
     */
    public function userOwnsOrder($order, $userId)
    {
        return $order->user_id === $userId;
    }

    /**
     * Check if order can transition to status
     */
    public function canTransitionToStatus($order, $newStatus)
    {
        return $order->canTransitionTo($newStatus);
    }

    /**
     * Validate stock availability
     */
    protected function validateStock($items)
    {
        $errors = [];
        $valid = true;

        foreach ($items as $item) {
            $product = \App\Models\Product::where('product_id', $item['product_id'])
                ->lockForUpdate()
                ->first();

            if (! $product) {
                $errors[] = "Product with ID {$item['product_id']} not found";
                $valid = false;

                continue;
            }

            if ($product->stock_quantity < $item['quantity']) {
                $errors[] = "Insufficient stock for product '{$product->name}'. Available: {$product->stock_quantity}, Requested: {$item['quantity']}";
                $valid = false;
            }
        }

        return ['valid' => $valid, 'errors' => $errors];
    }

    /**
     * Calculate item prices
     */
    protected function calculateItemPrices($items)
    {
        foreach ($items as $index => $item) {
            $product = \App\Models\Product::findOrFail($item['product_id']);
            $items[$index]['price'] = $product->price;
        }

        return $items;
    }

    /**
     * Calculate total amount
     */
    protected function calculateTotalAmount($items)
    {
        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += $item['quantity'] * $item['price'];
        }

        return $totalAmount;
    }

    /**
     * Create order items
     */
    protected function createOrderItems($order, $items)
    {
        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }
    }

    /**
     * Update stock after order creation
     */
    protected function updateStock($items)
    {
        foreach ($items as $item) {
            $product = \App\Models\Product::where('product_id', $item['product_id'])
                ->lockForUpdate()
                ->first();

            if ($product && $product->stock_quantity >= $item['quantity']) {
                $product->decrement('stock_quantity', $item['quantity']);
            }
        }
    }

    /**
     * Lấy đơn hàng để xử lý thanh toán
     * Kiểm tra quyền sở hữu đơn hàng
     */
    public function getOrderForPayment($orderId, $userId)
    {
        $order = Order::where('order_id', $orderId)->firstOrFail();

        if ($order->user_id !== $userId) {
            throw new \Exception('Đơn hàng không thuộc về người dùng này');
        }

        return $order;
    }

    /**
     * Lấy đơn hàng với items để hiển thị
     */
    public function getOrderWithItemsForDisplay($orderId)
    {
        return Order::where('order_id', $orderId)
            ->with('orderItems.product')
            ->firstOrFail();
    }

    /**
     * Lấy đơn hàng cơ bản theo ID
     */
    public function getOrderById($orderId)
    {
        return Order::where('order_id', $orderId)->firstOrFail();
    }

    /**
     * Get order statistics (for API)
     */
    public function getOrderStats($userId = null, $isAdmin = false)
    {
        $query = Order::query();

        if (! $isAdmin && $userId) {
            $query->where('user_id', $userId);
        }

        $stats = [
            'total_orders' => $query->count(),
            'pending' => (clone $query)->where('status', Order::STATUS_PENDING)->count(),
            'processing' => (clone $query)->where('status', Order::STATUS_PROCESSING)->count(),
            'shipped' => (clone $query)->where('status', Order::STATUS_SHIPPED)->count(),
            'delivered' => (clone $query)->where('status', Order::STATUS_DELIVERED)->count(),
            'cancelled' => (clone $query)->where('status', Order::STATUS_CANCELLED)->count(),
            'total_revenue' => (clone $query)->whereIn('status', [Order::STATUS_DELIVERED])->sum('total_amount'),
        ];

        if ($isAdmin) {
            $stats['pending_value'] = (clone $query)->where('status', Order::STATUS_PENDING)->sum('total_amount');
            $stats['processing_value'] = (clone $query)->where('status', Order::STATUS_PROCESSING)->sum('total_amount');
        }

        return $stats;
    }
}
