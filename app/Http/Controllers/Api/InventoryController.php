<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryAdjustmentRequest;
use App\Http\Requests\InventoryRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\InventoryCollection;
use App\Http\Resources\InventoryResource;
use App\Http\Resources\SuccessResource;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Hiển thị danh sách tồn kho
     */
    public function index(Request $request)
    {
        $filters = $request->only(['product_id', 'min_stock', 'max_stock', 'low_stock', 'stock_status', 'search', 'sort_by', 'sort_order']);
        $perPage = $request->get('per_page', 15);
        $inventories = $this->inventoryService->getInventories($filters, $perPage);

        return new InventoryCollection($inventories);
    }

    /**
     * Lưu bản ghi tồn kho mới được tạo
     */
    public function store(InventoryRequest $request)
    {
        try {
            $result = $this->inventoryService->storeInventory($request->validated()); // luu ban ghi moi
            $message = $result['created'] ? 'Inventory created successfully' : 'Inventory updated successfully';

            return (new InventoryResource($result['inventory']))->additional([
                // tra ve ket qua, $result['inventory'] chua ban ghi moi duoc lay tu service
                'status' => true, // trang thai thanh cong
                'message' => $message, // thong diep phu hop, $message duoc xac dinh o tren
            ])->response()->setStatusCode($result['created'] ? 201 : 200);
            // $result['created'] truy cap den key 'created' trong mang $result de xac dinh ma trang thai phu hop
        } catch (\Exception $e) { // bat loi ngoai le neu co
            return ErrorResource::serverError( // tra ve bang cach su dung ErrorResource, goi den ham serverError
                'Failed to process inventory',
                config('app.debug') ? $e->getMessage() : null
            );
        }
    }

    /**
     * Hiển thị bản ghi tồn kho theo ID
     */
    public function show(string $id)
    {
        $inventory = $this->inventoryService->findInventory($id, true);

        if (! $inventory) {
            return ErrorResource::notFound('Inventory not found');
        }

        return (new InventoryResource($inventory))->additional([
            'status' => true,
            'message' => 'Inventory retrieved successfully',
        ]);
    }

    /**
     * Cập nhật bản ghi tồn kho theo ID
     */
    public function update(InventoryRequest $request, string $id)
    {
        try {
            $inventory = $this->inventoryService->findInventory($id, false);

            if (! $inventory) {
                return ErrorResource::notFound('Inventory not found');
            }

            $inventory = $this->inventoryService->updateInventoryById($id, $request->validated(), $inventory);

            return (new InventoryResource($inventory))->additional([
                'status' => true,
                'message' => 'Inventory updated successfully',
            ]);
        } catch (\Exception $e) {
            return ErrorResource::serverError(
                'Failed to update inventory',
                config('app.debug') ? $e->getMessage() : null
            );
        }
    }

    /**
     * Xóa bản ghi tồn kho theo ID
     */
    public function destroy(string $id)
    {
        try {
            $inventory = $this->inventoryService->findInventory($id, false);

            if (! $inventory) {
                return ErrorResource::notFound('Inventory not found');
            }

            $this->inventoryService->deleteInventory($id);

            return SuccessResource::deleted('Inventory deleted successfully');
        } catch (\Exception $e) {
            return ErrorResource::serverError(
                'Failed to delete inventory',
                config('app.debug') ? $e->getMessage() : null
            );
        }
    }

    /**
     * Cập nhật tồn kho nhập/xuất và tính lại số lượng hiện tại
     */
    public function updateStock(InventoryAdjustmentRequest $request, string $id)
    {
        try {
            $inventory = $this->inventoryService->updateStockByType($id, $request->validated());

            $actionText = $request->type === 'in' ? 'Stock imported' : ($request->type === 'out' ? 'Stock exported' : 'Stock adjusted');

            return (new InventoryResource($inventory))->additional([
                'status' => true,
                'message' => "{$actionText} successfully",
            ]);
        } catch (\Exception $e) {
            return ErrorResource::serverError(
                'Failed to update stock',
                config('app.debug') ? $e->getMessage() : null
            );
        }
    }

    /**
     * Lấy danh sách sản phẩm tồn kho thấp
     */
    public function lowStock(Request $request)
    {
        $threshold = $request->get('threshold', 10);
        $inventories = $this->inventoryService->getLowStockInventories($threshold);

        return InventoryResource::collection($inventories)->additional([
            'status' => true,
            'message' => 'Low stock items retrieved successfully',
            'threshold' => $threshold,
            'count' => $inventories->count(),
        ]);
    }

    /**
     * Tạo hoặc cập nhật tồn kho (upsert)
     */
    public function upsert(InventoryRequest $request)
    {
        try {
            $result = $this->inventoryService->upsertInventory($request->validated());
            $message = $result['created'] ? 'Inventory created successfully' : 'Inventory updated successfully';
            $status = $result['created'] ? 201 : 200;

            return (new InventoryResource($result['inventory']))->additional([
                'status' => true,
                'message' => $message,
                'action' => $result['created'] ? 'created' : 'updated',
            ])->response()->setStatusCode($status);
        } catch (\Exception $e) {
            return ErrorResource::serverError(
                'Failed to process inventory',
                config('app.debug') ? $e->getMessage() : null
            );
        }
    }

    /**
     * Lấy danh sách sản phẩm hết hàng
     */
    public function outOfStock(Request $request)
    {
        $inventories = $this->inventoryService->getOutOfStockInventories();

        return InventoryResource::collection($inventories)->additional([
            'status' => true,
            'message' => 'Out of stock items retrieved successfully',
            'count' => $inventories->count(),
        ]);
    }

    /**
     * Lấy thống kê tồn kho
     */
    public function stats(Request $request)
    {
        $stats = $this->inventoryService->getInventoryStats();

        return SuccessResource::withData($stats, 'Inventory statistics retrieved successfully');
    }
}
