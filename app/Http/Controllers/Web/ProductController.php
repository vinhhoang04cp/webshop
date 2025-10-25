<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm cho admin
     *
     * Chức năng: Hiển thị tất cả sản phẩm với tính năng tìm kiếm và phân trang
     * Hoạt động:
     * - Query products với eager loading category
     * - Tìm kiếm theo tên hoặc mô tả sản phẩm (LIKE search)
     * - Phân trang với 12 sản phẩm mỗi trang
     * - Lấy tất cả products không phân trang (cho dropdown, export, v.v.)
     * - Lấy tất cả categories
     * - Trả về view với:
     *   + paginatedProducts: sản phẩm trên trang hiện tại
     *   + products: tất cả sản phẩm
     *   + categories: danh sách danh mục
     *   + pagination: thông tin phân trang
     * - Xử lý exception và trả về view với danh sách rỗng nếu có lỗi
     *
     * @param  \Illuminate\Http\Request  $request  Chứa tham số search
     * @return \Illuminate\View\View
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
     * Hiển thị form tạo sản phẩm mới
     *
     * Chức năng: Hiển thị form để nhập thông tin sản phẩm mới
     * Hoạt động:
     * - Lấy danh sách tất cả categories cho dropdown
     * - Trả về view form tạo sản phẩm với danh sách categories
     * - Redirect về danh sách với thông báo lỗi nếu có exception
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
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
     * Lưu sản phẩm mới vào database
     *
     * Chức năng: Xử lý tạo sản phẩm mới trong hệ thống
     * Hoạt động:
     * - Validate dữ liệu đầu vào:
     *   + name: bắt buộc, string, max 255 ký tự
     *   + description: nullable, string
     *   + price: bắt buộc, numeric, >= 0
     *   + category_id: bắt buộc, integer, phải tồn tại trong bảng categories
     *   + image: nullable, file ảnh (jpg, png, gif), max 2MB
     *   + image_url: nullable, phải là URL hợp lệ (nếu không upload file)
     *   + stock_quantity: bắt buộc, integer, >= 0
     * - Upload và lưu file ảnh vào storage/app/public/products (nếu có)
     * - Tạo product mới sử dụng Eloquent
     * - Tự động tạo bản ghi inventory cho sản phẩm:
     *   + stock_in = stock_quantity
     *   + stock_out = 0
     *   + current_stock = stock_quantity
     * - Redirect về danh sách với thông báo thành công
     * - Redirect về form create với lỗi và giữ input nếu có exception
     *
     * @param  \Illuminate\Http\Request  $request  Dữ liệu từ form tạo product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ProductRequest $request)
    {
        try {
            $imagePath = null;
            if ($request->hasFile('image')) { // Kiểm tra xem có file ảnh được upload không
                // Tạo tên file unique để tránh trùng lặp
                $imageName = time().'_'.$request->file('image')->getClientOriginalName(); // imageName la bien luu gia tri cua image name

                // Lưu ảnh vào thư mục storage/app/public/products
                $imagePath = $request->file('image')->storeAs('products', $imageName, 'public'); // storeAs('products', $imageName, 'public') la ham de luu image vao thu muc storage/app/public/products

                // Tạo URL để lưu vào database (sẽ là /storage/products/filename.jpg)
                $imageUrl = '/storage/'.$imagePath; // imageUrl la bien luu gia tri cua image url
            } else { // nếu không upload file thì sử dụng image_url (nếu có)
                // Nếu không upload file thì sử dụng image_url
                $imageUrl = $request->image_url; // imageUrl la bien luu gia tri cua image url  (nếu không upload file thì sử dụng image_url)
            }
            // Tạo product mới sử dụng Eloquent
            $product = Product::create([ // Tao moi product su dung model Product voi phuong thuc create() eloquent
                'name' => $request->name, // name la truong bat buoc, kieu string, do dai toi da 255 ky tu
                'description' => $request->description, // description la truong nullable, kieu string
                'price' => $request->price, // price la truong bat buoc, kieu numeric, gia tri toi thieu 0
                'category_id' => $request->category_id, // category_id la truong bat buoc, kieu integer, phai ton tai trong bang categories cot category_id
                'image_url' => $imageUrl, // image_url la truong nullable, kieu url
                'stock_quantity' => $request->stock_quantity, // stock_quantity la truong bat buoc, kieu integer, gia tri toi thieu 0
            ]);

            // Tự động tạo bản ghi inventory cho sản phẩm mới
            Inventory::create([ // Tạo mới inventory
                'product_id' => $product->product_id, // Lấy product_id từ product vừa tạo
                'stock_in' => $request->stock_quantity, // Số lượng nhập kho ban đầu // = số lượng nhập
                'stock_out' => 0, // Chưa có xuất kho // = 0
                'current_stock' => $request->stock_quantity, // Tồn kho hiện tại = số lượng nhập
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

            return redirect()->route('dashboard.products.index')
                ->with('success', 'Sản phẩm và tồn kho đã được tạo thành công!'); // Thong bao thanh cong

        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.create')
                ->with('error', 'Lỗi khi tạo sản phẩm: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Hiển thị chi tiết sản phẩm cho admin
     *
     * Chức năng: Hiển thị thông tin chi tiết của một sản phẩm cụ thể
     * Hoạt động:
     * - Tìm product theo ID với eager loading category
     * - Throw exception nếu không tìm thấy
     * - Trả về view chi tiết với thông tin product và category
     * - Redirect về danh sách với thông báo lỗi nếu có exception
     *
     * @param  int  $id  ID của product cần hiển thị
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        try {
            // Lấy product với relationship category và details
            $product = Product::with(['category', 'details'])->findOrFail($id);
            // findOrFail($id) neu khong tim thay se throw exception, su dung eloquent de lay product voi relationship category va details

            return view('dashboard.products.show', compact('product'));
            // Truyen du lieu sang view voi ham compact de tao mang tu bien

        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Không tìm thấy sản phẩm hoặc lỗi: '.$e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa sản phẩm
     *
     * Chức năng: Hiển thị form để chỉnh sửa thông tin sản phẩm
     * Hoạt động:
     * - Tìm product theo ID
     * - Lấy danh sách tất cả categories cho dropdown
     * - Trả về view form edit với product và categories
     * - Redirect về danh sách với thông báo lỗi nếu không tìm thấy
     *
     * @param  int  $id  ID của product cần chỉnh sửa
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        try {
            // Lấy thông tin product với details
            $product = Product::with('details')->findOrFail($id);

            // Lấy danh sách categories
            $categories = Category::all();

            return view('dashboard.products.edit', compact('product', 'categories'));

        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Không tìm thấy sản phẩm hoặc lỗi: '.$e->getMessage());
        }
    }

    /**
     * Cập nhật thông tin sản phẩm
     *
     * Chức năng: Xử lý cập nhật thông tin sản phẩm trong database
     * Hoạt động:
     * - Validate dữ liệu đầu vào (name, description, price, category_id, image, image_url, stock_quantity)
     * - Tìm product theo ID
     * - Xử lý upload ảnh mới (nếu có) và xóa ảnh cũ
     * - Lưu số lượng cũ để tính toán sự thay đổi tồn kho
     * - Cập nhật thông tin product
     * - Cập nhật hoặc tạo bản ghi inventory:
     *   + Nếu tăng số lượng: cập nhật stock_in và current_stock
     *   + Nếu giảm số lượng: cập nhật stock_out và current_stock
     * - Redirect về trang danh sách với thông báo thành công
     * - Redirect về form edit với lỗi và giữ input nếu có exception
     *
     * @param  \Illuminate\Http\Request  $request  Dữ liệu cập nhật
     * @param  int  $id  ID của product cần cập nhật
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProductRequest $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            // Xử lý upload ảnh mới
            $imageUrl = $product->image_url; // Giữ ảnh cũ làm mặc định

            if ($request->hasFile('image')) { // Nếu có upload file ảnh mới
                // Xóa ảnh cũ nếu có (chỉ xóa file được upload, không xóa URL)
                if ($product->image_url && strpos($product->image_url, '/storage/products/') !== false) {
                    $oldImagePath = str_replace('/storage/', '', $product->image_url);
                    Storage::disk('public')->delete($oldImagePath);
                }

                // Upload ảnh mới
                $imageName = time().'_'.$request->file('image')->getClientOriginalName();
                $imagePath = $request->file('image')->storeAs('products', $imageName, 'public');
                $imageUrl = '/storage/'.$imagePath;
            } elseif ($request->filled('image_url')) { // Nếu có URL mới
                // Xóa ảnh cũ nếu có file upload (không xóa khi thay đổi từ URL này sang URL khác)
                if ($product->image_url && strpos($product->image_url, '/storage/products/') !== false) {
                    $oldImagePath = str_replace('/storage/', '', $product->image_url);
                    Storage::disk('public')->delete($oldImagePath);
                }
                $imageUrl = $request->image_url;
            }

            // Lưu số lượng cũ để tính toán thay đổi
            $oldQuantity = $product->stock_quantity; // lay so luong ton kho cu truoc khi update
            $newQuantity = $request->stock_quantity; // lay so luong ton kho moi tu request
            $quantityDifference = $newQuantity - $oldQuantity; // tinh toan su khac biet giua so luong moi va cu

            $product->update([ // cap nhat product su dung phuong thuc update() cua eloquent
                'name' => $request->name, // lay gia tri name da validate tu request
                'description' => $request->description, // lay gia tri description da validate tu request
                'price' => $request->price, // lay gia tri price da validate tu request
                'category_id' => $request->category_id, // lay gia tri category_id da validate tu request
                'image_url' => $imageUrl, // lay gia tri image_url da xu ly
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

            return redirect()->route('dashboard.products.index') // chuyen huong ve trang danh sach products
                ->with('success', 'Sản phẩm và tồn kho đã được cập nhật thành công!'); // Thong bao thanh cong

        } catch (\Exception $e) { // bat loi neu co
            return redirect()->route('dashboard.products.edit', $id) // neu co loi thi chuyen huong ve trang edit product
                ->with('error', 'Lỗi khi cập nhật sản phẩm: '.$e->getMessage()) // Thong bao loi
                ->withInput(); // Giữ lại dữ liệu đã nhập
        }
    }

    /**
     * Xóa sản phẩm khỏi database
     *
     * Chức năng: Xóa một sản phẩm cụ thể khỏi hệ thống
     * Hoạt động:
     * - Tìm product theo ID
     * - Xóa các bản ghi inventory liên quan đến product (nếu có)
     * - Xóa product khỏi database
     * - Redirect về danh sách với thông báo thành công
     * - Xử lý exception và hiển thị lỗi nếu có (ví dụ: sản phẩm có ràng buộc với đơn hàng)
     *
     * Lưu ý: Cần cân nhắc soft delete thay vì hard delete để giữ dữ liệu lịch sử
     *
     * @param  int  $id  ID của product cần xóa
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            // Tìm và xóa product
            $product = Product::findOrFail($id); // dau tien tim product can xoa bang phuong thuc findOrFail($id) cua eloquent

            // Xóa file ảnh nếu có (chỉ xóa file được upload, không xóa URL bên ngoài)
            if ($product->image_url && strpos($product->image_url, '/storage/products/') !== false) {
                $imagePath = str_replace('/storage/', '', $product->image_url);
                Storage::disk('public')->delete($imagePath);
            }

            // Xóa inventory liên quan (nếu có)
            Inventory::where('product_id', $product->product_id)->delete(); // xoa inventory theo product_id

            // Xóa ProductDetail liên quan (nếu có)
            ProductDetail::where('product_id', $product->product_id)->delete(); // xoa product detail theo product_id

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
