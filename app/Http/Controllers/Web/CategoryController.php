<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;   // Import Controller de ke thua
use App\Models\Category; // Import model Category de su dung trong controller
use Illuminate\Http\Request; // Import Request de lay du lieu tu form

class CategoryController extends Controller
{
    /**
     * Display a listing of categories for admin UI.
     */
    public function index(Request $request) // (Request $request) la tham so truyen vao ham , duoc gui tu form index
    {
        try {
            // Lấy danh sách categories với search
            $query = Category::query(); // Category lay tu model Category

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
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('dashboard.categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request) // (Request $request) la tham so truyen vao ham , duoc gui tu form create
    {
        $request->validate([    // validate du lieu truoc khi luu vao database
            'name' => 'required|string|max:150|unique:categories,name', // name la truong trong database, unique:categories,name kiem tra xem name da ton tai trong bang categories chua
            'description' => 'nullable|string', // description co the rong (nullable) va phai la chuoi (string)
        ]);

        try {
            // Tạo category mới sử dụng Eloquent
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
     * Display the specified category.
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
     * Show the form for editing the specified category.
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
     * Update the specified category.
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
     * Remove the specified category.
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
