<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products for admin UI.
     */
    public function index(Request $request) // Request $request de lay du lieu tu Http request
    {
        try {
            // $query de lay products voi relationship category
            $query = Product::with('category'); // Product lay tu model Product voi eloquent

            // Search
            if ($request->has('search') && $request->search) { // $request->has('search') kiem tra xem co tham so search trong request khong, && $request->search kiem tra xem tham so search co gia tri khong
                $searchTerm = $request->search; // $searchTerm la bien luu gia tri search tu request
                $query->where(function ($q) use ($searchTerm) { // where(function ($q) use ($searchTerm)) la ham de loc du lieu, voi tham so $q la query builder, va su dung bien $searchTerm ben ngoai ham
                    $q->where('name', 'like', '%'.$searchTerm.'%') // $q se duoc truyen lai cho query builder de loc theo name , su dung callback function de gom nhom cac dieu kien loc
                        ->orWhere('description', 'like', '%'.$searchTerm.'%'); // Hoac loc theo description
                });
            }

            // Pagination
            $perPage = 12; // So luong products tren moi trang
            $products = $query->paginate($perPage); // $products la bien chua ket qua phan trang
            // paginate($perPage) de phan trang voi so luong tren moi trang voi tham so $perPage

            // allProducts de lay tat ca products khong phan trang
            $allProducts = Product::all();

            // categories de lay tat ca categories
            $categories = Category::all(); // eloquent lay tat ca categories tu model Category

            return view('dashboard.products.index', [ // Truyen du lieu sang view
                'paginatedProducts' => $products->items(), // 'paginatedProducts' chi chua products tren trang hien tai
                'products' => $allProducts, // 'products' chua tat ca products
                'categories' => $categories, // 'categories' chua tat ca categories
                'pagination' => $products, // 'pagination' chua thong tin phan trang (tong so trang, trang hien tai, so luong tren moi trang, v.v.)
            ]);

        } catch (\Exception $e) { // Bat loi neu co
            return view('dashboard.products.index', [ // Neu co loi thi tra ve view voi cac bien rong va thong bao loi
                'paginatedProducts' => [],
                'products' => [],
                'categories' => [],
                'error' => 'Lỗi khi tải danh sách sản phẩm: '.$e->getMessage(), // Thong bao loi
            ]);
        }
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        try {
            // Lấy danh sách categories cho dropdown
            $categories = Category::all(); // eloquent lay tat ca categories tu model Category

            return view('dashboard.products.create', compact('categories')); // Truyen du lieu sang view
        } catch (\Exception $e) { // Bat loi neu co
            return redirect()->route('dashboard.products.index') // Neu co loi thi chuyen huong ve trang danh sach products
                ->with('error', 'Không thể tải form tạo sản phẩm: '.$e->getMessage()); // Thong bao loi
        }
    }

    /**
     * Ham xu ly luu san pham moi vao database.
     * dau tien se validate du lieu tu request
     * sau do tao product moi va luu vao database
     * cuoi cung tao ban ghi inventory cho product moi tao
     */
    public function store(Request $request) // Request $request de lay du lieu tu Http request
    {

        $request->validate([ // Validate du lieu tu request
            'name' => 'required|string|max:255', // name bat buoc phai co, kieu string, do dai toi da 255 ky tu
            'description' => 'nullable|string', // description co the khong co, neu co phai la kieu string
            'price' => 'required|numeric|min:0', // price bat buoc phai co, kieu numeric, gia tri toi thieu 0
            'category_id' => 'required|integer|exists:categories,category_id', // category_id bat buoc phai co, kieu integer, phai ton tai trong bang categories cot category_id
            'image_url' => 'nullable|url', // image_url co the khong co, neu co phai la kieu url
            'stock_quantity' => 'required|integer|min:0', // stock_quantity bat buoc phai co, kieu integer, gia tri toi thieu 0
        ]);

        try {
            // Tạo product mới sử dụng Eloquent
            $product = Product::create([ // Tao moi product su dung model Product voi phuong thuc create() eloquent
                'name' => $request->name, // lay gia tri name da validate tu request
                'description' => $request->description, // lay gia tri description da validate tu request
                'price' => $request->price, // lay gia tri price da validate tu request
                'category_id' => $request->category_id, // lay gia tri category_id da validate tu request
                'image_url' => $request->image_url, // lay gia tri image_url da validate tu request
                'stock_quantity' => $request->stock_quantity, // lay gia tri stock_quantity da validate tu request
            ]);

            // Tự động tạo bản ghi inventory cho sản phẩm mới
            Inventory::create([ // Tạo mới inventory
                'product_id' => $product->product_id, // Lấy product_id từ product vừa tạo
                'stock_in' => $request->stock_quantity, // Số lượng nhập kho ban đầu // = số lượng nhập
                'stock_out' => 0, // Chưa có xuất kho // = 0
                'current_stock' => $request->stock_quantity, // Tồn kho hiện tại = số lượng nhập
            ]);

            return redirect()->route('dashboard.products.index')
                ->with('success', 'Sản phẩm và tồn kho đã được tạo thành công!'); // Thong bao thanh cong

        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.create')
                ->with('error', 'Lỗi khi tạo sản phẩm: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        try {
            // Lấy product với relationship category
            $product = Product::with('category')->findOrFail($id);
            // findOrFail($id) neu khong tim thay se throw exception, su dung eloquent de lay product voi relationship category

            return view('dashboard.products.show', compact('product'));
            // Truyen du lieu sang view voi ham compact de tao mang tu bien

        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Không tìm thấy sản phẩm hoặc lỗi: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        try {
            // Lấy thông tin product
            $product = Product::findOrFail($id);

            // Lấy danh sách categories
            $categories = Category::all();

            return view('dashboard.products.edit', compact('product', 'categories'));

        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Không tìm thấy sản phẩm hoặc lỗi: '.$e->getMessage());
        }
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id)
    // Request $request de lay du lieu tu Http request, $id la id cua product can update
    {
        $request->validate([
            'name' => 'required|string|max:255', // name bat buoc phai co, kieu string, do dai toi da 255 ky tu
            'description' => 'nullable|string', // description co the khong co, neu co phai la kieu string
            'price' => 'required|numeric|min:0', // price bat buoc phai co, kieu numeric, gia tri toi thieu 0
            'category_id' => 'required|integer|exists:categories,category_id', // category_id bat buoc phai co, kieu integer, phai ton tai trong bang categories cot category_id
            'image_url' => 'nullable|url', // image_url co the khong co, neu co phai la kieu url
            'stock_quantity' => 'required|integer|min:0', // stock_quantity bat buoc phai co, kieu integer, gia tri toi thieu 0
        ]);

        try {
            // Tìm và cập nhật product
            $product = Product::findOrFail($id); // dau tien tim product can update bang phuong thuc findOrFail($id) cua eloquent

            // Lưu số lượng cũ để tính toán thay đổi
            $oldQuantity = $product->stock_quantity; // lay so luong ton kho cu truoc khi update
            $newQuantity = $request->stock_quantity; // lay so luong ton kho moi tu request
            $quantityDifference = $newQuantity - $oldQuantity; // tinh toan su khac biet giua so luong moi va cu

            $product->update([ // cap nhat product su dung phuong thuc update() cua eloquent
                'name' => $request->name, // lay gia tri name da validate tu request
                'description' => $request->description, // lay gia tri description da validate tu request
                'price' => $request->price, // lay gia tri price da validate tu request
                'category_id' => $request->category_id, // lay gia tri category_id da validate tu request
                'image_url' => $request->image_url, // lay gia tri image_url da validate tu request
                'stock_quantity' => $request->stock_quantity, // lay gia tri stock_quantity da validate tu request
            ]);

            // Cập nhật hoặc tạo bản ghi inventory
            $inventory = Inventory::firstOrCreate( // tim hoac tao moi inventory
                ['product_id' => $product->product_id],// dieu kien tim kiem inventory theo product_id
                [
                    'stock_in' => 0, // neu khong tim thay thi tao moi voi gia tri mac dinh
                    'stock_out' => 0, // neu khong tim thay thi tao moi voi gia tri mac dinh
                    'current_stock' => 0, // neu khong tim thay thi tao moi voi gia tri mac dinh
                ]
            );

            // Điều chỉnh inventory dựa trên sự thay đổi số lượng
            if ($quantityDifference > 0) { // neu so luong moi lon hon so luong cu
                // Tăng số lượng - coi như nhập kho thêm
                $inventory->stock_in += $quantityDifference; // $inventory->stock_in la so luong nhap kho hien tai, cong them su khac biet
                $inventory->current_stock += $quantityDifference; // cong them su khac biet vao ton kho hien tai
            } elseif ($quantityDifference < 0) { // neu so luong moi nho hon so luong cu
                // Giảm số lượng - coi như xuất kho
                $inventory->stock_out += abs($quantityDifference); // abs($quantityDifference) lay gia tri tuyet doi cua su khac biet
                $inventory->current_stock += $quantityDifference; // $inventory->current_stock la ton kho hien tai, tru di su khac biet
            }

            $inventory->save(); // luu thay doi vao database

            return redirect()->route('dashboard.products.index') // chuyen huong ve trang danh sach products
                ->with('success', 'Sản phẩm và tồn kho đã được cập nhật thành công!'); // Thong bao thanh cong

        } catch (\Exception $e) { // bat loi neu co
            return redirect()->route('dashboard.products.edit', $id) // neu co loi thi chuyen huong ve trang edit product
                ->with('error', 'Lỗi khi cập nhật sản phẩm: '.$e->getMessage()) // Thong bao loi
                ->withInput(); // Giữ lại dữ liệu đã nhập
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id)
    {
        try {
            // Tìm và xóa product
            $product = Product::findOrFail($id); // dau tien tim product can xoa bang phuong thuc findOrFail($id) cua eloquent

            // Xóa inventory liên quan (nếu có)
            Inventory::where('product_id', $product->product_id)->delete(); // xoa inventory theo product_id

            // Xóa product
            $product->delete(); // xoa product

            return redirect()->route('dashboard.products.index')
                ->with('success', 'Sản phẩm và tồn kho đã được xóa thành công!'); // Thong bao thanh cong

        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Lỗi khi xóa sản phẩm: '.$e->getMessage()); // Thong bao loi
        }
    }
}
