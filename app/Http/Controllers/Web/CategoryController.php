<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;   // Import Controller de ke thua
use App\Models\Category; // Import model Category de su dung trong controller
use Illuminate\Http\Request; // Import Request de lay du lieu tu form

class CategoryController extends Controller
{
    /**
     * Hiển thị danh sách danh mục cho admin
     *
     * Chức năng: Hiển thị danh sách tất cả danh mục với tính năng tìm kiếm và phân trang
     * Hoạt động:
     * - Khởi tạo query builder để lấy danh sách categories
     * - Nếu có tham số search, lọc categories theo tên (LIKE search)
     * - Phân trang kết quả với 10 danh mục mỗi trang
     * - Lấy tất cả categories để sử dụng cho các dropdown
     * - Trả về view với dữ liệu categories đã phân trang
     * - Xử lý exception và hiển thị thông báo lỗi nếu có
     *
     * @param  \Illuminate\Http\Request  $request  Chứa tham số search và filter
     * @return \Illuminate\View\View
     */
    public function index(Request $request) // (Request $request) la tham so truyen vao ham , duoc gui tu form index
    {
        try {
            // Lấy danh sách categories với search
            $query = Category::query(); // Category lay tu model Category voi eloquent

            // Nếu có search, filter dữ liệu
            if ($request->has('search') && $request->search) { // $request->has('search') kiem tra xem co tham so search khong, && $request->search kiem tra xem gia tri search co khac rong khong
                $searchTerm = $request->search; // $request->search lay gia tri search tu form
                $query->where('name', 'LIKE', "%{$searchTerm}%"); // where de loc du lieu, LIKE de tim kiem gan dung, %{$searchTerm}% de tim kiem gan dung o dau va cuoi
            }

            // Pagination
            $perPage = 10; // so luong hien thi tren mot trang
            $categories = $query->paginate($perPage); // phan trang voi so luong tren mot trang

            // lay tat ca categories
            $allCategories = Category::all(); // Lấy tất cả categories

            return view('dashboard.categories.index', compact('categories', 'allCategories')); // Truyen du lieu vao view bang compact

        } catch (\Exception $e) { // Bat loi neu co
            return view('dashboard.categories.index', [     // Trả về view với thông báo lỗi
                'categories' => collect()->paginate(10), // Trả về một collection rỗng với phân trang
                'allCategories' => collect(), // Trả về một collection rỗng
                'error' => 'Lỗi khi tải danh sách danh mục: '.$e->getMessage(), // Hiển thị thông báo lỗi
            ]);
        }
    }

    /**
     * Hiển thị form tạo danh mục mới
     *
     * Chức năng: Hiển thị giao diện form để nhập thông tin danh mục mới
     * Hoạt động:
     * - Trả về view chứa form tạo category
     * - Form bao gồm các trường: tên danh mục, mô tả
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('dashboard.categories.create');
    }

    /**
     * Lưu danh mục mới vào database
     *
     * Chức năng: Xử lý dữ liệu từ form và tạo danh mục mới trong hệ thống
     * Hoạt động:
     * - Validate dữ liệu đầu vào (name bắt buộc, unique, max 150 ký tự; description nullable)
     * - Tạo category mới sử dụng Eloquent model
     * - Lưu vào database
     * - Redirect về trang danh sách categories với thông báo thành công
     * - Xử lý exception và redirect với thông báo lỗi nếu có
     *
     * @param  \Illuminate\Http\Request  $request  Dữ liệu từ form tạo category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request) // (Request $request) la tham so truyen vao ham , duoc gui tu form create
    {
        $request->validate([    // validate du lieu truoc khi luu vao database
            'name' => 'required|string|max:150|unique:categories,name', // name la truong trong database, unique:categories,name kiem tra xem name da ton tai trong bang categories chua
            'description' => 'nullable|string', // description co the rong (nullable) va phai la chuoi (string)
        ]);

        try {
            // Tạo category mới sử dụng model Category voi eloquent
            Category::create([    // Category lay tu model Category
                'name' => $request->name,   // $request->name lay gia tri name tu form
                'description' => $request->description,  // $request->description lay gia tri description tu form
            ]);

            return redirect()->route('dashboard.categories.index') // Chuyen huong ve trang index sau khi luu thanh cong
                ->with('success', 'Danh mục đã được tạo thành công!'); // with('success', '...') de hien thi thong bao thanh cong

        } catch (\Exception $e) { // Bat loi neu co
            return redirect()->route('dashboard.categories.index') // Chuyen huong ve trang index neu co loi
                ->with('error', 'Lỗi khi tạo danh mục: '.$e->getMessage()); // with('error', '...') de hien thi thong bao loi
        }
    }

    /**
     * Hiển thị chi tiết một danh mục
     *
     * Chức năng: Hiển thị thông tin chi tiết của một danh mục cụ thể và danh sách sản phẩm thuộc danh mục đó
     * Hoạt động:
     * - Tìm category theo ID sử dụng findOrFail (throw exception nếu không tìm thấy)
     * - Load eager relationship 'products' để lấy tất cả sản phẩm thuộc danh mục
     * - Trả về view chi tiết với thông tin category và products
     * - Xử lý exception và redirect về danh sách với thông báo lỗi nếu có
     *
     * @param  int  $id  ID của category cần hiển thị
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id) // $id la tham so truyen vao ham , duoc gui tu form index
    {
        try {
            $category = Category::with('products')->findOrFail($id); // Tim category theo id, neu khong tim thay se nem ngoai le, with('products') de lay cac san pham thuoc danh muc

            return view('dashboard.categories.show', compact('category')); // Truyen du lieu vao view bang compact
        } catch (\Exception $e) { // Bat loi neu co
            return redirect()->route('dashboard.categories.index') // Chuyen huong ve trang index neu co loi
                ->with('error', 'Lỗi khi tải chi tiết danh mục: '.$e->getMessage()); // with('error', '...') de hien thi thong bao loi
        }
    }

    /**
     * Hiển thị form chỉnh sửa danh mục
     *
     * Chức năng: Hiển thị form để chỉnh sửa thông tin danh mục đã tồn tại
     * Hoạt động:
     * - Tìm category theo ID sử dụng findOrFail
     * - Trả về view form edit với dữ liệu category hiện tại
     * - Xử lý exception và redirect về danh sách nếu không tìm thấy
     *
     * @param  int  $id  ID của category cần chỉnh sửa
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        try {
            $category = Category::findOrFail($id); // TTim category theo id, neu khong tim thay se nem ngoai le

            return view('dashboard.categories.edit', compact('category')); // Truyen du lieu vao view bang compact
        } catch (\Exception $e) { // BBat loi neu co
            return redirect()->route('dashboard.categories.index') // Chuyen huong ve trang index neu co loi
                ->with('error', 'Lỗi khi tải form chỉnh sửa: '.$e->getMessage()); // with('error', '...') de hien thi thong bao loi
        }
    }

    /**
     * Cập nhật thông tin danh mục
     *
     * Chức năng: Xử lý cập nhật thông tin danh mục trong database
     * Hoạt động:
     * - Validate dữ liệu đầu vào (name unique ngoại trừ ID hiện tại, description nullable)
     * - Tìm category theo ID
     * - Cập nhật thông tin mới vào database sử dụng Eloquent
     * - Redirect về trang danh sách với thông báo thành công
     * - Xử lý exception và hiển thị lỗi nếu có
     *
     * @param  \Illuminate\Http\Request  $request  Dữ liệu cập nhật từ form
     * @param  int  $id  ID của category cần cập nhật
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id) // (Request $request, $id) la tham so truyen vao ham , duoc gui tu form edit
    {
        $request->validate([ // request validate de kiem tra du lieu truoc khi cap nhat vao database
            'name' => 'required|string|max:150|unique:categories,name,'.$id.',category_id',     // name la truong trong database, unique:categories,name,'.$id.',category_id kiem tra xem name da ton tai trong bang categories chua, ngoai tru id hien tai
            'description' => 'nullable|string', // description co the rong (nullable) va phai la chuoi (string)
        ]);

        try {
            // Tìm và cập nhật category
            $category = Category::findOrFail($id); // Tim category theo id, neu khong tim thay se nem ngoai le
            $category->update([
                'name' => $request->name, // $request->name lay gia tri name tu form
                'description' => $request->description, // $request->description lay gia tri description tu form
            ]);

            return redirect()->route('dashboard.categories.index') // Chuyen huong ve trang index sau khi cap nhat thanh cong
                ->with('success', 'Danh mục đã được cập nhật thành công!'); // with('success', '...') de hien thi thong bao thanh cong

        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index') // Chuyen huong ve trang index neu co loi
                ->with('error', 'Lỗi khi cập nhật danh mục: '.$e->getMessage()); // with('error', '...') de hien thi thong bao loi
        }
    }

    /**
     * Xóa danh mục khỏi database
     *
     * Chức năng: Xóa một danh mục cụ thể khỏi hệ thống
     * Hoạt động:
     * - Tìm category theo ID sử dụng findOrFail
     * - Thực hiện xóa category khỏi database
     * - Redirect về trang danh sách với thông báo thành công
     * - Xử lý exception nếu có lỗi (ví dụ: danh mục có ràng buộc với sản phẩm)
     *
     * Lưu ý: Cần kiểm tra ràng buộc với sản phẩm trước khi xóa để tránh lỗi foreign key
     *
     * @param  int  $id  ID của category cần xóa
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id) // $id la tham so truyen vao ham , duoc gui tu form index
    {
        try {
            // Tìm và xóa category
            $category = Category::findOrFail($id); // Tim category theo id, neu khong tim thay se nem ngoai le
            $category->delete(); // xoa category

            return redirect()->route('dashboard.categories.index') // Chuyen huong ve trang index sau khi xoa thanh cong
                ->with('success', 'Danh mục đã được xóa thành công!'); // with('success', '...') de hien thi thong bao thanh cong

        } catch (\Exception $e) { // Bat loi neu co
            return redirect()->route('dashboard.categories.index') // Chuyen huong ve trang index neu co loi
                ->with('error', 'Lỗi khi xóa danh mục: '.$e->getMessage()); // with('error', '...') de hien thi thong bao loi
        }
    }
}
