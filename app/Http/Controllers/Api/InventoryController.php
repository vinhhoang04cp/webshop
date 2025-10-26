<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryAdjustmentRequest;
use App\Http\Requests\InventoryRequest;
use App\Http\Resources\InventoryCollection;
use App\Http\Resources\InventoryResource;
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
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['product_id', 'min_stock', 'max_stock', 'low_stock', 'stock_status', 'search', 'sort_by', 'sort_order']);
        $perPage = $request->get('per_page', 15);
        $inventories = $this->inventoryService->getInventories($filters, $perPage);

        return new InventoryCollection($inventories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InventoryRequest $request)
    {
        try {
            $result = $this->inventoryService->storeInventory($request->validated());
            $message = $result['created'] ? 'Inventory created successfully' : 'Inventory updated successfully';

            return (new InventoryResource($result['inventory']))->additional([
                'success' => true,
                'message' => $message,
            ])->response()->setStatusCode($result['created'] ? 201 : 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process inventory',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $inventory = $this->inventoryService->findInventory($id, true);

        if (! $inventory) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory not found',
            ], 404);
        }

        return (new InventoryResource($inventory))->additional([
            'success' => true,
            'message' => 'Inventory retrieved successfully',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InventoryRequest $request, string $id)
    {
        try {
            $inventory = $this->inventoryService->findInventory($id, false);

            if (! $inventory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inventory not found',
                ], 404);
            }

            $inventory = $this->inventoryService->updateInventoryById($id, $request->validated(), $inventory);

            return (new InventoryResource($inventory))->additional([
                'success' => true,
                'message' => 'Inventory updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inventory',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $inventory = $this->inventoryService->findInventory($id, false);

            if (! $inventory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inventory not found',
                ], 404);
            }

            $this->inventoryService->deleteInventory($id);

            return response()->json([
                'success' => true,
                'message' => 'Inventory deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete inventory',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update stock in/out and recalculate current stock
     */
    public function updateStock(InventoryAdjustmentRequest $request, string $id)
    {
        try {
            $inventory = $this->inventoryService->updateStockByType($id, $request->validated());

            $actionText = $request->type === 'in' ? 'Stock imported' : ($request->type === 'out' ? 'Stock exported' : 'Stock adjusted');

            return (new InventoryResource($inventory))->additional([
                'success' => true,
                'message' => "{$actionText} successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get low stock items
     */
    public function lowStock(Request $request)
    {
        $threshold = $request->get('threshold', 10);
        $inventories = $this->inventoryService->getLowStockInventories($threshold);

        return InventoryResource::collection($inventories)->additional([
            'success' => true,
            'message' => 'Low stock items retrieved successfully',
            'threshold' => $threshold,
            'count' => $inventories->count(),
        ]);
    }

    /**
     * Create or update inventory (upsert)
     */
    public function upsert(InventoryRequest $request)
    {
        try {
            $result = $this->inventoryService->upsertInventory($request->validated());
            $message = $result['created'] ? 'Inventory created successfully' : 'Inventory updated successfully';
            $status = $result['created'] ? 201 : 200;

            return (new InventoryResource($result['inventory']))->additional([
                'success' => true,
                'message' => $message,
                'action' => $result['created'] ? 'created' : 'updated',
            ])->response()->setStatusCode($status);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process inventory',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get out of stock items
     */
    public function outOfStock(Request $request)
    {
        $inventories = $this->inventoryService->getOutOfStockInventories();

        return InventoryResource::collection($inventories)->additional([
            'success' => true,
            'message' => 'Out of stock items retrieved successfully',
            'count' => $inventories->count(),
        ]);
    }

    /**
     * Get inventory statistics
     */
    public function stats(Request $request)
    {
        $stats = $this->inventoryService->getInventoryStats();

        return response()->json([
            'success' => true,
            'message' => 'Inventory statistics retrieved successfully',
            'data' => $stats,
        ], 200);
    }
}
