<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryCollection;
use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Inventory::query()->with('product');

        // Filter by product_id
        if ($request->has('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }
        
        // Filter by current stock range
        if ($request->has('min_stock')) {
            $query->where('current_stock', '>=', $request->get('min_stock'));
        }
        if ($request->has('max_stock')) {
            $query->where('current_stock', '<=', $request->get('max_stock'));
        }
        
        // Filter by low stock (current_stock < 10)
        if ($request->has('low_stock') && $request->get('low_stock') == true) {
            $query->where('current_stock', '<', 10);
        }

        $perPage = $request->get('per_page', 10);
        $inventories = $query->paginate($perPage);

        return new InventoryCollection($inventories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,product_id|unique:inventory,product_id',
            'stock_in' => 'required|integer|min:0',
            'stock_out' => 'integer|min:0',
            'current_stock' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Calculate current_stock if not provided
            $data = $request->all();
            if (!isset($data['current_stock'])) {
                $data['current_stock'] = ($data['stock_in'] ?? 0) - ($data['stock_out'] ?? 0);
            }
            
            $inventory = Inventory::create($data);
            $inventory->load('product');
            
            return response()->json([
                'success' => true,
                'message' => 'Inventory created successfully',
                'data' => new InventoryResource($inventory)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create inventory',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $inventory = Inventory::with('product')->where('inventory_id', $id)->firstOrFail();
            
            return response()->json([
                'success' => true,
                'data' => new InventoryResource($inventory)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $inventory = Inventory::where('inventory_id', $id)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'product_id' => 'sometimes|exists:products,product_id|unique:inventory,product_id,' . $id . ',inventory_id',
                'stock_in' => 'sometimes|integer|min:0',
                'stock_out' => 'sometimes|integer|min:0',
                'current_stock' => 'sometimes|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            
            // Recalculate current_stock if stock_in or stock_out is updated
            if (isset($data['stock_in']) || isset($data['stock_out'])) {
                $stockIn = $data['stock_in'] ?? $inventory->stock_in;
                $stockOut = $data['stock_out'] ?? $inventory->stock_out;
                $data['current_stock'] = $stockIn - $stockOut;
            }
            
            $inventory->update($data);
            $inventory->load('product');

            return response()->json([
                'success' => true,
                'message' => 'Inventory updated successfully',
                'data' => new InventoryResource($inventory)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inventory',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $inventory = Inventory::where('inventory_id', $id)->firstOrFail();
            $inventory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Inventory deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete inventory'
            ], 500);
        }
    }

    /**
     * Update stock in/out and recalculate current stock
     */
    public function updateStock(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'stock_in' => 'sometimes|integer|min:0',
            'stock_out' => 'sometimes|integer|min:0',
            'type' => 'required|in:in,out,adjust'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $inventory = Inventory::where('inventory_id', $id)->firstOrFail();
            
            switch ($request->type) {
                case 'in':
                    $inventory->stock_in += $request->stock_in ?? 0;
                    break;
                case 'out':
                    $inventory->stock_out += $request->stock_out ?? 0;
                    break;
                case 'adjust':
                    if ($request->has('stock_in')) {
                        $inventory->stock_in = $request->stock_in;
                    }
                    if ($request->has('stock_out')) {
                        $inventory->stock_out = $request->stock_out;
                    }
                    break;
            }
            
            // Recalculate current stock
            $inventory->current_stock = $inventory->stock_in - $inventory->stock_out;
            $inventory->save();
            $inventory->load('product');

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'data' => new InventoryResource($inventory)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock items
     */
    public function lowStock(Request $request)
    {
        $threshold = $request->get('threshold', 10);
        
        $inventories = Inventory::with('product')
            ->where('current_stock', '<', $threshold)
            ->paginate($request->get('per_page', 10));
            
        return new InventoryCollection($inventories);
    }
}
