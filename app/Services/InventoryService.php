<?php

namespace App\Services;

use App\Models\Inventory;

class InventoryService
{
    /**
     * Lấy danh sách inventory với filter và sort
     */
    public function getInventoriesForAdmin(array $filters = [], int $perPage = 15)
    {
        $query = Inventory::with('product.category');

        // Tìm kiếm theo tên sản phẩm
        if (! empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->whereHas('product', function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%'.$searchTerm.'%');
            });
        }

        // Filter theo trạng thái tồn kho
        if (isset($filters['stock_status']) && $filters['stock_status'] !== '') {
            $status = $filters['stock_status'];
            if ($status === 'low') {
                // Tồn kho thấp: current_stock < 10
                $query->where('current_stock', '<', 10);
            } elseif ($status === 'out') {
                // Hết hàng: current_stock = 0
                $query->where('current_stock', '=', 0);
            } elseif ($status === 'available') {
                // Còn hàng: current_stock >= 10
                $query->where('current_stock', '>=', 10);
            }
        }

        // Sắp xếp
        $sortBy = $filters['sort_by'] ?? 'updated_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Lấy chi tiết inventory
     */
    public function getInventoryDetail($inventoryId)
    {
        return Inventory::with('product.category')->findOrFail($inventoryId);
    }

    /**
     * Lấy inventory với product
     */
    public function getInventoryWithProduct($inventoryId)
    {
        return Inventory::with('product')->findOrFail($inventoryId);
    }

    /**
     * Cập nhật inventory
     */
    public function updateInventory($inventoryId, array $data)
    {
        $inventory = Inventory::findOrFail($inventoryId);

        $inventory->update([
            'stock_in' => $data['stock_in'],
            'stock_out' => $data['stock_out'],
            'current_stock' => $data['current_stock'],
        ]);

        return $inventory;
    }

    /**
     * Điều chỉnh tồn kho (nhập/xuất)
     */
    public function adjustStock($inventoryId, $adjustmentType, $quantity)
    {
        $inventory = Inventory::findOrFail($inventoryId);

        if ($adjustmentType === 'in') {
            // Nhập kho
            $inventory->stock_in += $quantity;
            $inventory->current_stock += $quantity;
        } else {
            // Xuất kho
            if ($inventory->current_stock < $quantity) {
                throw new \Exception('Số lượng xuất kho vượt quá tồn kho hiện tại!');
            }
            $inventory->stock_out += $quantity;
            $inventory->current_stock -= $quantity;
        }

        $inventory->save();

        return $inventory;
    }

    /**
     * Kiểm tra tồn kho thấp
     */
    public function getLowStockInventories(int $threshold = 10)
    {
        return Inventory::with('product')
            ->where('current_stock', '<', $threshold)
            ->where('current_stock', '>', 0)
            ->orderBy('current_stock', 'asc')
            ->get();
    }

    /**
     * Kiểm tra hết hàng
     */
    public function getOutOfStockInventories()
    {
        return Inventory::with('product')
            ->where('current_stock', '=', 0)
            ->get();
    }

    /**
     * Lấy thống kê inventory
     */
    public function getInventoryStats()
    {
        return [
            'total_products' => Inventory::count(),
            'low_stock' => Inventory::where('current_stock', '<', 10)->where('current_stock', '>', 0)->count(),
            'out_of_stock' => Inventory::where('current_stock', '=', 0)->count(),
            'total_stock_value' => Inventory::with('product')->get()->sum(function ($inventory) {
                return $inventory->current_stock * ($inventory->product->price ?? 0);
            }),
        ];
    }

    /**
     * Get inventories with filters (for API)
     */
    public function getInventories(array $filters = [], int $perPage = 10)
    {
        $query = Inventory::with('product');

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['min_stock'])) {
            $query->where('current_stock', '>=', $filters['min_stock']);
        }

        if (isset($filters['max_stock'])) {
            $query->where('current_stock', '<=', $filters['max_stock']);
        }

        if (isset($filters['low_stock']) && $filters['low_stock'] == true) {
            $query->where('current_stock', '<', 10);
        }

        return $query->paginate($perPage);
    }

    /**
     * Find inventory by ID
     */
    public function findInventory($inventoryId)
    {
        return Inventory::with('product')->where('inventory_id', $inventoryId)->first();
    }

    /**
     * Create or update inventory
     */
    public function storeInventory(array $data)
    {
        $existingInventory = Inventory::where('product_id', $data['product_id'])->first();

        $preparedData = $this->prepareInventoryData($data);

        if ($existingInventory) {
            $existingInventory->update($preparedData);
            $existingInventory->load('product');
            
            return ['inventory' => $existingInventory, 'created' => false];
        }

        $inventory = Inventory::create($preparedData);
        $inventory->load('product');
        
        return ['inventory' => $inventory, 'created' => true];
    }

    /**
     * Update inventory by ID
     */
    public function updateInventoryById($inventoryId, array $data, $existingInventory = null)
    {
        if (!$existingInventory) {
            $existingInventory = Inventory::where('inventory_id', $inventoryId)->firstOrFail();
        }

        $preparedData = $this->prepareInventoryData($data, $existingInventory);
        $existingInventory->update($preparedData);
        $existingInventory->load('product');

        return $existingInventory;
    }

    /**
     * Delete inventory
     */
    public function deleteInventory($inventoryId)
    {
        $inventory = Inventory::where('inventory_id', $inventoryId)->firstOrFail();
        $inventory->delete();

        return true;
    }

    /**
     * Update stock by type
     */
    public function updateStockByType($inventoryId, array $data)
    {
        $inventory = Inventory::where('inventory_id', $inventoryId)->firstOrFail();

        switch ($data['type']) {
            case 'in':
                $inventory->stock_in += $data['stock_in'] ?? 0;
                break;
            case 'out':
                $inventory->stock_out += $data['stock_out'] ?? 0;
                break;
            case 'adjust':
                if (isset($data['stock_in'])) {
                    $inventory->stock_in = $data['stock_in'];
                }
                if (isset($data['stock_out'])) {
                    $inventory->stock_out = $data['stock_out'];
                }
                break;
        }

        $inventory->current_stock = $inventory->stock_in - $inventory->stock_out;
        $inventory->save();
        $inventory->load('product');

        return $inventory;
    }

    /**
     * Upsert inventory
     */
    public function upsertInventory(array $data)
    {
        $preparedData = $this->prepareInventoryData($data);
        $inventory = Inventory::updateOrCreate(
            ['product_id' => $data['product_id']],
            $preparedData
        );
        $inventory->load('product');

        return [
            'inventory' => $inventory,
            'created' => $inventory->wasRecentlyCreated,
        ];
    }

    /**
     * Prepare inventory data
     */
    protected function prepareInventoryData(array $data, $existingInventory = null)
    {
        if (!isset($data['current_stock'])) {
            if ($existingInventory && (isset($data['stock_in']) || isset($data['stock_out']))) {
                $stockIn = $data['stock_in'] ?? $existingInventory->stock_in;
                $stockOut = $data['stock_out'] ?? $existingInventory->stock_out;
                $data['current_stock'] = $stockIn - $stockOut;
            } else {
                $data['current_stock'] = ($data['stock_in'] ?? 0) - ($data['stock_out'] ?? 0);
            }
        }

        return $data;
    }
}
