<?php

namespace App\Http\Controllers\Api; // Nhóm controller cho API

use App\Http\Controllers\Controller; // Controller cơ sở
use App\Http\Requests\ProductRequest; // FormRequest để validate/authorize dữ liệu vào
use App\Http\Resources\ProductCollection; // Resource Collection: chuẩn hoá danh sách
use App\Http\Resources\ProductResource; // Resource: chuẩn hoá 1 bản ghi
use App\Models\Product; // Eloquent Model ánh xạ bảng 'products'
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
        $query = Product::with('category'); // $query là biến để thực hiện query đến bảng Product thông qua model

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

        $product = $product->fresh(); // Tải lại đối tượng product để lấy thông tin mới nhất

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
     * @param  int|string
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Eager load category để trả về thông tin danh mục kèm theo sản phẩm.
        // Dùng find() để tự kiểm soát JSON 404 (khác với findOrFail() sẽ throw exception).
        $product = Product::with('category')->find($id); // query den bang Product thong qua model, tim kiem theo id, voi category

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

        // Trả về JSON thông báo thành công. 200 OK là mặc định.
        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
