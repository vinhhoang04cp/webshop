<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerProductController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm cho khách hàng
     *
     * Chức năng: Hiển thị trang danh sách sản phẩm với các tính năng tìm kiếm, lọc và sắp xếp
     * Hoạt động:
     * - Khởi tạo query với eager loading category
     * - Tìm kiếm theo tên hoặc mô tả sản phẩm (tham số 'q')
     * - Lọc theo danh mục (tham số 'category')
     * - Lọc theo khoảng giá (min_price, max_price)
     * - Sắp xếp theo nhiều tiêu chí: latest, price_asc, price_desc, name_asc, name_desc
     * - Phân trang 12 sản phẩm mỗi trang
     * - Lấy danh sách categories kèm số lượng sản phẩm
     * - Đếm số lượng sản phẩm trong giỏ hàng nếu user đã đăng nhập
     * - Trả về view với đầy đủ dữ liệu
     *
     * @param  \Illuminate\Http\Request  $request  Chứa các tham số search, filter, sort
     * @return \Illuminate\View\View
     */
    public function index(Request $request) // Request $request la cac tham so truyen vao de loc, tim kiem, sap xep san pham
    {
        $query = Product::with('category'); // $query la mot doi tuong query builder de truy van du lieu tu bang products voi quan he voi bang categories

        // Tìm kiếm theo tên
        if ($request->has('q') && $request->q) { // neu co tham so q va q khac rong
            $query->where('name', 'like', "%{$request->q}%")
                ->orWhere('description', 'like', "%{$request->q}%");
        }

        // Lọc theo danh mục
        if ($request->has('category') && $request->category) { // neu request co tham so category va category khac rong duoc truyen len
            $query->where('category_id', $request->category); // query builder se them dieu kien where de loc theo category_id
        }

        // Lọc theo giá
        if ($request->has('min_price') && $request->min_price) { // neu co tham so min_price va min_price khac rong
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) { // neu co tham so max_price va max_price khac rong
            $query->where('price', '<=', $request->max_price);
        }

        // Sắp xếp
        $sortBy = $request->get('sort', 'latest'); // $sortBy la bien tao ra chua result cua tham so sort, neu khong co tham so sort thi mac dinh la 'latest'
        switch ($sortBy) { // su dung switch de kiem tra gia tri cua $sortBy voi tham so $sortBy la tham so chua request truyen vao
            case 'price_asc': // neu tham so sort la price_asc thi sap xep theo gia tang dan
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc': // neu tham so sort la price_desc thi sap xep theo gia giam dan
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc': // neu tham so sort la name_asc thi sap xep theo ten tang dan
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc': // neu tham so sort la name_desc thi sap xep theo ten giam dan
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->latest('created_at');
        }

        $products = $query->paginate(12); // phan trang 12 san pham tren mot trang
        $categories = Category::withCount('products')->get(); // Model Category lay ve danh sach danh muc voi so luong san pham trong tung danh muc

        // Đếm số lượng sản phẩm trong giỏ hàng
        $cartCount = 0; // Khoi tao cartCount bang 0
        if (Auth::check()) { // check neu user da dang nhap bang ham auth()
            $cart = Auth::user()->cart; // neu user da dang nhap thi lay gio hang cua user hien tai
            if ($cart) { // neu co gio hang thi tinh tong so luong san pham trong gio hang
                $cartCount = $cart->items()->sum('quantity');
                // $cart->items() goi den quan he items de lay ve danh sach cac item trong gio hang, sau do tinh tong so luong san pham trong gio hang bang ham sum('quantity')
            }
        }

        return view('products.index', compact('products', 'categories', 'cartCount')); // tra ve product view, compact dung de truyen du lieu ra view
    }

    /**
     * Hiển thị chi tiết sản phẩm cho khách hàng
     *
     * Chức năng: Hiển thị trang chi tiết một sản phẩm cụ thể với thông tin đầy đủ
     * Hoạt động:
     * - Tìm sản phẩm theo ID với eager loading (category, details, inventory)
     * - Throw 404 exception nếu không tìm thấy
     * - Lấy 4 sản phẩm liên quan cùng danh mục (loại trừ sản phẩm hiện tại)
     * - Đếm số lượng sản phẩm trong giỏ hàng nếu user đã đăng nhập
     * - Lấy danh sách categories để hiển thị menu
     * - Trả về view chi tiết với product, relatedProducts, categories, cartCount
     *
     * @param  int  $id  ID của sản phẩm cần hiển thị
     * @return \Illuminate\View\View
     */
    public function show($id) // $id la tham so cua san pham can hien thi chi tiet
    {
        $product = Product::with(['category', 'details', 'inventory', 'ratings.user']) // Model Product lay ve san pham voi quan he voi category, details, inventory, ratings va user
            ->findOrFail($id); // tim kiem san pham theo id, neu khong tim thay thi tra ve loi 404

        // Sản phẩm liên quan (cùng danh mục)
        $relatedProducts = Product::with('category') // Model Product lay ve san pham voi quan he voi category
            ->where('category_id', $product->category_id) // loc nhung san pham cung danh muc voi san pham hien tai
            ->where('product_id', '!=', $id) // loai bo san pham hien tai ra khoi danh sach san pham lien quan
            ->take(4) // lay 4 san pham lien quan
            ->get(); // lay ve danh sach san pham lien quan

        // Đếm số lượng sản phẩm trong giỏ hàng
        $cartCount = 0; // Khoi tao cartCount bang 0
        if (Auth::check()) { // Neu user da dang nhap
            $cart = Auth::user()->cart; // lay gio hang cua user hien tai
            if ($cart) { // neu co gio hang thi tinh tong so luong san pham trong gio hang
                $cartCount = $cart->items()->sum('quantity'); // $cart->items() goi den quan he items de lay ve danh sach cac item trong gio hang, sau do tinh tong so luong san pham trong gio hang bang ham sum('quantity')
            }
        }

        $categories = Category::withCount('products')->get(); // lay so luong san pham trong tung danh muc

        return view('products.show', compact('product', 'relatedProducts', 'categories', 'cartCount')); // Tra ve view chi tiet , truyen du lieu qua ham compact
    }

    /**
     * Tìm kiếm sản phẩm
     */
    public function search(Request $request) // su dung Request de lay tham so truyen vao de tim kiem
    {
        return $this->index($request);
    }

    /**
     * Hiển thị sản phẩm theo danh mục
     */
    public function category($id) // $id la tham so cua danh muc can hien thi san pham
    {
        $category = Category::findOrFail($id); // Tim category qua id

        $products = Product::with('category') // Tim product co quan he voi Category qua model Product
            ->where('category_id', $id) //
            ->latest('created_at')
            ->paginate(12);

        $categories = Category::withCount('products')->get();

        // Đếm số lượng sản phẩm trong giỏ hàng
        $cartCount = 0;
        if (Auth::check()) {
            $cart = Auth::user()->cart;
            if ($cart) {
                $cartCount = $cart->items()->sum('quantity');
            }
        }

        return view('products.category', compact('category', 'products', 'categories', 'cartCount'));
    }

    public function addRating(Request $request, $productId)
    {
        // Kiểm tra đăng nhập
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn phải đăng nhập để đánh giá sản phẩm.');
        }

        // Kiểm tra sản phẩm có tồn tại không
        $product = Product::find($productId);
        if (! $product) {
            return redirect()->back()->with('error', 'Sản phẩm không tồn tại.');
        }

        // Kiểm tra user đã đánh giá sản phẩm này chưa
        $existingRating = Rating::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->first();

        if ($existingRating) {
            return redirect()->back()->with('error', 'Bạn đã đánh giá sản phẩm này rồi.');
        }

        // Validate dữ liệu
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ], [
            'rating.required' => 'Bạn phải chọn số sao đánh giá.',
            'rating.min' => 'Số sao tối thiểu là 1.',
            'rating.max' => 'Số sao tối đa là 5.',
            'review.max' => 'Nhận xét không được quá 1000 ký tự.',
        ]);

        // Tạo rating mới
        $rating = new Rating;
        $rating->user_id = Auth::id();
        $rating->product_id = $productId;
        $rating->rating = $request->input('rating');
        $rating->review = $request->input('review', '');
        $rating->save();

        return redirect()->back()->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
    }
}
