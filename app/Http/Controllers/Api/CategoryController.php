<?php

namespace App\Http\Controllers\Api; // Namespace cho nhóm controller API (phân tách với web)

use App\Http\Controllers\Controller; // Controller cơ sở của Laravel
use App\Http\Requests\CategoryRequest; // FormRequest: nơi định nghĩa rules/authorize để validate & phân quyền
use App\Http\Resources\CategoryCollection; // Resource Collection: chuẩn hoá danh sách dữ liệu trả về
use App\Http\Resources\CategoryResource; // Resource: chuẩn hoá 1 đối tượng trả về
use App\Models\Category; // Eloquent Model: ánh xạ bảng 'categories' trong DB

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     *                                                            Trả về danh sách dưới dạng Resource Collection để thống nhất cấu trúc JSON.
     *                                                            (Ưu điểm: kiểm soát field trả ra, thêm meta/pagination nếu cần)
     */
    public function index()
    {
        // Lấy tất cả danh mục.
        // Lưu ý: với data lớn nên phân trang (paginate) thay vì all() để tránh tốn bộ nhớ.
        // Ví dụ: $categories = Category::paginate(15);
        $categories = Category::all();  // $categories là Collection các model Category

        // Bọc lại bằng CategoryCollection để định dạng dữ liệu trả về một cách nhất quán
        // CategoryCollection sẽ quyết định các field trả ra của từng phần tử (thông qua CategoryResource).
        return new CategoryCollection($categories);
    }

    /**
     * Store a newly created category in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CategoryRequest $request)
    {
        // $request lúc này là CategoryRequest (kế thừa FormRequest) -> đã validate theo rules bạn định nghĩa.
        // Ưu điểm: Controller gọn, logic validate tách riêng, tự động trả 422 nếu sai format dữ liệu.

        // Tạo bản ghi mới dựa trên input đã validate.
        // Chú ý: cần bật fillable/guarded trong Model Category để cho phép mass assignment.
        $category = Category::create([
            'name' => $request->name,          // Lấy giá trị từ body request đã được validate
            'description' => $request->description,
        ]);

        // Reorder IDs để đảm bảo thứ tự 1, 2, 3, ...
        Category::reorderIds();
        
        // Refresh category instance để lấy ID mới sau reorder
        $category = $category->fresh();

        // Trả về đối tượng vừa tạo dưới dạng Resource để định dạng field trả về.
        // ->additional(): thêm meta đi kèm (status, message...) ngoài data chính.
        // ->response(): chuyển Resource thành Response
        // ->setStatusCode(201): HTTP 201 (Created) theo chuẩn REST khi tạo mới thành công.
        return (new CategoryResource($category))
            ->additional([
                'status' => true,
                'message' => 'Category created successfully',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified category.
     *
     * @param  int  $id  ID danh mục cần lấy (đi từ route param)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Tìm theo ID; nếu không có sẽ trả về null (khác với findOrFail sẽ throw 404 exception).
        // Ưu nhược: find() + if/else => bạn tự kiểm soát message JSON;
        // còn findOrFail() => Laravel tự trả 404 HTML (hoặc JSON tuỳ handler).
        $category = Category::find($id);

        // Nếu không tìm thấy -> trả JSON 404 thống nhất format API của bạn.
        if (! $category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // Nếu tìm thấy -> trả về dưới dạng Resource + kèm meta
        // ->response(): đảm bảo trả về instance Response (có thể set status, header...)
        return (new CategoryResource($category))
            ->additional([
                'status' => true,
                'message' => 'Category retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200); // 200 OK là mặc định, nhưng set rõ giúp đọc code dễ hiểu.
    }

    /**
     * Update the specified category in storage.
     *
     * @param  int  $id  ID danh mục cần cập nhật
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CategoryRequest $request, $id)
    {
        // Tìm danh mục cần cập nhật
        $category = Category::find($id);

        // Nếu không có -> trả 404
        if (! $category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // Cập nhật field cho bản ghi.
        // Lưu ý: đảm bảo các field có trong $fillable của Model để mass-assign được.
        $category->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Reorder IDs để đảm bảo thứ tự 1, 2, 3, ...
        Category::reorderIds();
        
        // Refresh category instance để lấy ID mới sau reorder (nếu có thay đổi)
        $category = $category->fresh();

        // Trả về Resource sau cập nhật + meta (không cần setStatusCode vì 200 là mặc định).
        // Có thể cân nhắc trả 200 (OK) hoặc 202 (Accepted) tuỳ semantics, nhưng 200 là phổ biến.
        return (new CategoryResource($category))
            ->additional([
                'status' => true,
                'message' => 'Category updated successfully',
            ]);
    }

    /**
     * Remove the specified category from storage.
     *
     * @param  int  $id  ID danh mục cần xoá
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Tìm danh mục theo ID
        $category = Category::find($id);

        // Không tìm thấy -> 404
        if (! $category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // Kiểm tra ràng buộc nghiệp vụ: nếu danh mục đang có sản phẩm liên kết thì không cho xoá.
        // $category->products: quan hệ hasMany/belongsToMany (cần định nghĩa trong Model Category).
        // Lưu ý: đây là lazy loading -> có thể phát sinh N+1 trong một số tình huống batch.
        // Nếu muốn tối ưu, có thể eager load hoặc dùng exists() để đếm nhanh:
        // if ($category->products()->exists()) { ... }
        if ($category->products->count() > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete category with associated products',
            ], 400); // 400 Bad Request (cũng có thể cân nhắc 409 Conflict tuỳ chuẩn team)
        }

        // Thực hiện xoá.
        // Nếu dùng SoftDeletes trong Model, lệnh này sẽ "đánh dấu xoá" thay vì xoá cứng.
        $category->delete();

        // Reorder IDs để đảm bảo thứ tự 1, 2, 3, ...
        Category::reorderIds();

        // Trả về JSON thông báo thành công. 200 OK là mặc định.
        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
