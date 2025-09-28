<?php

namespace App\Http\Controllers\Api; // Nhóm controller cho API

use App\Http\Controllers\Controller; // Controller cơ sở
use App\Http\Requests\ProductRequest; // FormRequest để validate/authorize dữ liệu vào
use App\Http\Resources\ProductCollection; // Resource Collection: chuẩn hoá danh sách
use App\Http\Resources\ProductResource; // Resource: chuẩn hoá 1 bản ghi
use App\Models\Product; // Eloquent Model ánh xạ bảng 'products'

// (Không dùng trực tiếp ở đây vì đã dùng ProductRequest, nhưng vẫn có thể hữu ích)

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     *                                                            Trả về danh sách sản phẩm dưới dạng Resource Collection, giúp thống nhất cấu trúc JSON.
     */
    public function index() // Trả về danh sách sản phẩm
    {
        // Eager load quan hệ 'category' để tránh N+1 query khi serialize ra JSON.
        // Lưu ý: nếu dữ liệu lớn, cân nhắc paginate() thay vì get().
        // Ví dụ: $products = Product::with('category')->paginate(15);
        $products = Product::with('category')->get(); // $products là Collection các Product kèm Category

        // Bọc bằng ProductCollection để kiểm soát field trả ra, có thể thêm meta/pagination nếu dùng paginate().
        return new ProductCollection($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductRequest $request)  // $request là ProductRequest đã validate rules trước khi vào controller
    {
        // Tạo sản phẩm mới bằng mass assignment.
        // Đảm bảo các cột trong mảng dưới có mặt trong $fillable của Model Product để cho phép create/update hàng loạt.
        // only([...]) giúp chống truyền thừa field ngoài ý muốn (an toàn hơn so với all()).
        $product = Product::create(
            $request->only(['name', 'description', 'price', 'category_id', 'stock_quantity', 'image_url'])
        );

        // Reorder IDs để đảm bảo thứ tự 1, 2, 3, ...
        Product::reorderIds();
        
        // Refresh product instance để lấy ID mới sau reorder
        $product = $product->fresh();

        // Trả về ProductResource cho đối tượng vừa tạo + meta kèm status/message.
        // Sử dụng HTTP 201 (Created) đúng chuẩn REST khi tạo thành công.
        return (new ProductResource($product))
            ->additional([
                'status' => true,
                'message' => 'Product created successfully',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int|string  $id  ID sản phẩm cần lấy (đi từ route param)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Eager load category để trả về thông tin danh mục kèm theo sản phẩm.
        // Dùng find() để tự kiểm soát JSON 404 (khác với findOrFail() sẽ throw exception).
        $product = Product::with('category')->find($id);

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
     * @param  int|string  $id  ID sản phẩm cần cập nhật
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ProductRequest $request, $id)
    {
        // Tìm sản phẩm cần cập nhật
        $product = Product::find($id);

        // Không tìm thấy -> trả 404
        if (! $product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Cập nhật bằng mass assignment (nhớ $fillable trong Model).
        // only([...]) để hạn chế field không mong muốn.
        $product->update(
            $request->only(['name', 'description', 'price', 'category_id', 'stock_quantity', 'image_url'])
        );

        // Reorder IDs để đảm bảo thứ tự 1, 2, 3, ...
        Product::reorderIds();
        
        // Refresh product instance để lấy ID mới sau reorder (nếu có thay đổi)
        $product = $product->fresh();

        // Trả về ProductResource sau cập nhật + meta
        // Thường dùng 200 OK (hoặc 204 No Content nếu không cần body).
        return (new ProductResource($product))
            ->additional([
                'status' => true,
                'message' => 'Product updated successfully',
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int|string  $id  ID sản phẩm cần xoá
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Tìm danh mục theo ID
        $product = Product::find($id);

        // Không tìm thấy -> 404
        if (! $product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Kiểm tra ràng buộc nghiệp vụ: nếu sản phẩm đang có liên kết thì không cho xoá.
        // Kiểm tra xem sản phẩm có trong bất kỳ đơn hàng nào không
        if ($product->orderItems()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete product with associated orders',
            ], 400);
        }

        // Kiểm tra xem sản phẩm có trong giỏ hàng nào không
        if ($product->cartItems()->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete product with items in cart',
            ], 400);
        }

        // Thực hiện xoá.
        // Nếu dùng SoftDeletes trong Model, lệnh này sẽ "đánh dấu xoá" thay vì xoá cứng.
        $product->delete();

        // Reorder IDs để đảm bảo thứ tự 1, 2, 3, ...
        Product::reorderIds();

        // Trả về JSON thông báo thành công. 200 OK là mặc định.
        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
