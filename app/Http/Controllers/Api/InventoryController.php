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

        $this->applyFilters($query, $request);

        $perPage = $request->get('per_page', 10);
        $inventories = $query->paginate($perPage);

        return new InventoryCollection($inventories);
    }

    /**
     * Store a newly created resource in storage.
     * If inventory exists for the product, it will be updated instead of creating new one.
     */
    public function store(Request $request)
    {
        $validator = $this->validateInventoryData($request);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            $existingInventory = Inventory::where('product_id', $request->product_id)->first();
            $data = $this->prepareInventoryData($request);

            if ($existingInventory) {
                $existingInventory->update($data);
                $existingInventory->load('product');

                return $this->successResponse('Inventory updated successfully', new InventoryResource($existingInventory));
            }

            $inventory = Inventory::create($data);
            $inventory->load('product');

            return $this->successResponse('Inventory created successfully', new InventoryResource($inventory), 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process inventory', $e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $inventory = Inventory::with('product')->where('inventory_id', $id)->firstOrFail();

            return $this->successResponse(null, new InventoryResource($inventory));
        } catch (\Exception $e) {
            return $this->errorResponse('Inventory not found', null, 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $inventory = Inventory::where('inventory_id', $id)->firstOrFail();
            $validator = $this->validateInventoryData($request, $id);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $data = $this->prepareInventoryData($request, $inventory);
            $inventory->update($data);
            $inventory->load('product');

            return $this->successResponse('Inventory updated successfully', new InventoryResource($inventory));
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update inventory', $e);
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

            return $this->successResponse('Inventory deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete inventory', $e);
        }
    }

    /**
     * Update stock in/out and recalculate current stock
     */
    public function updateStock(Request $request, string $id)
    {
        $validator = $this->validateStockUpdate($request);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            $inventory = Inventory::where('inventory_id', $id)->firstOrFail();
            $this->processStockUpdate($inventory, $request);
            $inventory->load('product');

            return $this->successResponse('Stock updated successfully', new InventoryResource($inventory));
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update stock', $e);
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

    /**
     * Create or update inventory (upsert)
     */
    public function upsert(Request $request)
    {
        $validator = $this->validateInventoryData($request);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            $data = $this->prepareInventoryData($request);
            $inventory = Inventory::updateOrCreate(['product_id' => $request->product_id], $data);
            $inventory->load('product');

            $message = $inventory->wasRecentlyCreated ? 'Inventory created successfully' : 'Inventory updated successfully';
            $status = $inventory->wasRecentlyCreated ? 201 : 200;
            $action = $inventory->wasRecentlyCreated ? 'created' : 'updated';

            return $this->successResponse($message, new InventoryResource($inventory), $status, ['action' => $action]);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process inventory', $e);
        }
    }

    /**
     * Apply filters to inventory query
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->has('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }

        if ($request->has('min_stock')) {
            $query->where('current_stock', '>=', $request->get('min_stock'));
        }

        if ($request->has('max_stock')) {
            $query->where('current_stock', '<=', $request->get('max_stock'));
        }

        if ($request->has('low_stock') && $request->get('low_stock') == true) {
            $query->where('current_stock', '<', 10);
        }
    }

    /**
     * Validate inventory data
     */
    private function validateInventoryData(Request $request, $excludeId = null)
    {
        $rules = [
            'product_id' => 'required|exists:products,product_id',
            'stock_in' => 'required|integer|min:0',
            'stock_out' => 'integer|min:0',
            'current_stock' => 'integer|min:0',
        ];

        // Add unique constraint for update operation
        if ($excludeId) {
            $rules['product_id'] = 'sometimes|exists:products,product_id|unique:inventory,product_id,'.$excludeId.',inventory_id';
            $rules['stock_in'] = 'sometimes|integer|min:0';
        }

        return Validator::make($request->all(), $rules);
    }

    /**
     * Validate stock update data
     */
    private function validateStockUpdate(Request $request)
    {
        return Validator::make($request->all(), [
            'stock_in' => 'sometimes|integer|min:0',
            'stock_out' => 'sometimes|integer|min:0',
            'type' => 'required|in:in,out,adjust',
        ]);
    }

    /**
     * Prepare inventory data for storage
     */
    private function prepareInventoryData(Request $request, $existingInventory = null)
    {
        $data = $request->all();

        if (! isset($data['current_stock'])) {
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

    /**
     * Process stock update based on type
     */
    private function processStockUpdate($inventory, Request $request)
    {
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

        $inventory->current_stock = $inventory->stock_in - $inventory->stock_out;
        $inventory->save();
    }

    /**
     * Return success response
     */
    private function successResponse($message = null, $data = null, $status = 200, $extra = [])
    {
        $response = array_merge([
            'success' => true,
        ], $extra);

        if ($message) {
            $response['message'] = $message;
        }

        if ($data) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Return error response
     */
    private function errorResponse($message, $exception = null, $status = 500)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($exception && config('app.debug')) {
            $response['error'] = $exception->getMessage();
        }

        return response()->json($response, $status);
    }

    /**
     * Return validation error response
     */
    private function validationErrorResponse($validator)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422);
    }
}
