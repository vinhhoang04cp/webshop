<?php

namespace App\Http\Controllers\Api; // Nhóm controller cho API

use App\Http\Controllers\Controller; // Controller cơ sở
use App\Http\Requests\ProductRequest; // FormRequest để validate/authorize dữ liệu vào
use App\Http\Resources\ProductCollection; // Resource Collection: chuẩn hoá danh sách
use App\Http\Resources\ProductResource; // Resource: chuẩn hoá 1 bản ghi
use App\Models\Inventory; // Model Inventory để quản lý tồn kho
use App\Models\Product; // Eloquent Model ánh xạ bảng 'products'
use App\Models\ProductDetail; // Model ProductDetail để quản lý chi tiết sản phẩm
use Illuminate\Http\Request; // Lớp Request của Laravel

// (Không dùng trực tiếp ở đây vì đã dùng ProductRequest, nhưng vẫn có thể hữu ích)

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index(Request $request) // Trả về danh sách sản phẩm
    {
        $query = Product::with(['category', 'details']); // $query là biến để thực hiện query đến bảng Product thông qua model, kèm category và details

        // Lọc theo category
        if ($request->has('category')) { // nếu request truyền lên có category
            $query->where('category_id', $request->get('category'));
        }

        // Lọc theo tên (tìm gần đúng)
        if ($request->has('name')) { //
            $query->where('name', 'LIKE', '%'.$request->get('name').'%'); // thuc hien query den name
        }

        // Lọc theo giá (có thể theo khoảng giá)
        if ($request->has('min_price')) {   // neu request truyen len co min_price
            $query->where('price', '>=', $request->get('min_price')); // thuc hien query den min_price
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->get('max_price')); // thuc hien query den max_price
        }

        // Lọc theo tồn kho
        if ($request->has('stock_quantity')) { // nếu request truyền lên có stock_quantity
            $query->where('stock_quantity', $request->get('stock_quantity')); // thuc hien query den stock_quantity
        }

        // Nếu dữ liệu lớn, khuyến nghị paginate
        $products = $query->paginate(15); // Phân trang, mỗi trang 15 bản ghi

        return new ProductCollection($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\ProductRequest  $request  ProductRequest đã validate đầy đủ
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductRequest $request)  // $request là ProductRequest đã validate rules trước khi vào controller
    {
        try {
            // Tạo sản phẩm mới bằng mass assignment.
            // Đảm bảo các cột trong mảng dưới có mặt trong $fillable của Model Product để cho phép create/update hàng loạt.
            // only([...]) giúp chống truyền thừa field ngoài ý muốn (an toàn hơn so với all()).

            $product = Product::create(
                $request->only(['name', 'description', 'price', 'category_id', 'stock_quantity', 'image_url'])
            );

            // Tự động tạo bản ghi inventory cho sản phẩm mới
            Inventory::create([
                'product_id' => $product->product_id,
                'stock_in' => $request->stock_quantity ?? 0,
                'stock_out' => 0,
                'current_stock' => $request->stock_quantity ?? 0,
            ]);

            // Tạo ProductDetail nếu có thông tin chi tiết
            $hasDetails = $request->color || $request->storage || $request->ram ||
                         $request->screen_size || $request->chip || $request->battery ||
                         $request->camera_main || $request->camera_front || $request->os ||
                         $request->special_features;

            if ($hasDetails) {
                ProductDetail::create([
                    'product_id' => $product->product_id,
                    'color' => $request->color,
                    'storage' => $request->storage,
                    'ram' => $request->ram,
                    'screen_size' => $request->screen_size,
                    'chip' => $request->chip,
                    'battery' => $request->battery,
                    'camera_main' => $request->camera_main,
                    'camera_front' => $request->camera_front,
                    'os' => $request->os,
                    'special_features' => $request->special_features,
                ]);
            }

            $product = $product->fresh(['category', 'details']); // Tải lại đối tượng product với relationships

            return (new ProductResource($product))
                ->additional([
                    'status' => true,
                    'message' => 'Product created successfully',
                ])
                ->response()
                ->setStatusCode(201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error creating product: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int|string
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Eager load category và details để trả về thông tin danh mục và chi tiết kỹ thuật kèm theo sản phẩm.
        // Dùng find() để tự kiểm soát JSON 404 (khác với findOrFail() sẽ throw exception).
        $product = Product::with(['category', 'details'])->find($id); // query den bang Product thong qua model, tim kiem theo id, voi category va details

        // Không tìm thấy -> trả 404 với format JSON thống nhất của API.
        if (! $product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Tìm thấy -> bọc ProductResource, thêm meta, trả 200 OK.
        return (new ProductResource($product))
            ->additional([
                'status' => true,
                'message' => 'Product retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200); // 200 là mặc định, set rõ giúp đọc code dễ hiểu.
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\ProductRequest  $request  ProductRequest đã validate đầy đủ
     * @param  int|string  $id  ID sản phẩm cần cập nhật
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ProductRequest $request, $id)
    {
        try {
            // Tìm sản phẩm cần cập nhật
            $product = Product::find($id);

            // Không tìm thấy -> trả 404
            if (! $product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            // Lưu số lượng cũ để tính toán thay đổi inventory
            $oldQuantity = $product->stock_quantity;
            $newQuantity = $request->stock_quantity;
            $quantityDifference = $newQuantity - $oldQuantity;

            // Cập nhật bằng mass assignment (nhớ $fillable trong Model).
            // only([...]) để hạn chế field không mong muốn.
            $product->update(
                $request->only(['name', 'description', 'price', 'category_id', 'stock_quantity', 'image_url'])
            );

            // Cập nhật hoặc tạo bản ghi inventory
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $product->product_id],
                [
                    'stock_in' => 0,
                    'stock_out' => 0,
                    'current_stock' => 0,
                ]
            );

            // Điều chỉnh inventory dựa trên sự thay đổi số lượng
            if ($quantityDifference > 0) {
                // Tăng số lượng - coi như nhập kho thêm
                $inventory->stock_in += $quantityDifference;
                $inventory->current_stock += $quantityDifference;
            } elseif ($quantityDifference < 0) {
                // Giảm số lượng - coi như xuất kho
                $inventory->stock_out += abs($quantityDifference);
                $inventory->current_stock += $quantityDifference;
            }

            $inventory->save();

            // Cập nhật hoặc tạo ProductDetail
            $hasDetails = $request->color || $request->storage || $request->ram ||
                         $request->screen_size || $request->chip || $request->battery ||
                         $request->camera_main || $request->camera_front || $request->os ||
                         $request->special_features;

            if ($hasDetails) {
                // Tìm hoặc tạo ProductDetail
                $productDetail = ProductDetail::firstOrCreate(
                    ['product_id' => $product->product_id],
                    []
                );

                // Cập nhật thông tin
                $productDetail->update([
                    'color' => $request->color,
                    'storage' => $request->storage,
                    'ram' => $request->ram,
                    'screen_size' => $request->screen_size,
                    'chip' => $request->chip,
                    'battery' => $request->battery,
                    'camera_main' => $request->camera_main,
                    'camera_front' => $request->camera_front,
                    'os' => $request->os,
                    'special_features' => $request->special_features,
                ]);
            } else {
                // Nếu không có chi tiết nào, xóa ProductDetail (nếu có)
                ProductDetail::where('product_id', $product->product_id)->delete();
            }

            $product = $product->fresh(['category', 'details']); // Tải lại với relationships

            // Trả về ProductResource sau cập nhật + meta
            // Thường dùng 200 OK (hoặc 204 No Content nếu không cần body).
            return (new ProductResource($product))
                ->additional([
                    'status' => true,
                    'message' => 'Product updated successfully',
                ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error updating product: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int|string  $id  ID sản phẩm cần xóa
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // Tìm sản phẩm cần xóa
            $product = Product::find($id);

            // Không tìm thấy -> trả 404
            if (! $product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            // Xóa các bản ghi liên quan trước khi xóa sản phẩm
            // để tránh lỗi foreign key constraint

            // 1. Xóa ProductDetail nếu có
            ProductDetail::where('product_id', $product->product_id)->delete();

            // 2. Xóa Inventory nếu có
            Inventory::where('product_id', $product->product_id)->delete();

            // 3. Có thể xóa các bản ghi khác như OrderItem, CartItem... (nếu cần)
            // Nhưng thường ta sẽ soft delete hoặc đánh dấu inactive thay vì xóa cứng

            // 4. Cuối cùng xóa sản phẩm
            $product->delete();

            // Trả về thông báo thành công
            return response()->json([
                'status' => true,
                'message' => 'Product and related data deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting product: '.$e->getMessage(),
            ], 500);
        }
    }
}
