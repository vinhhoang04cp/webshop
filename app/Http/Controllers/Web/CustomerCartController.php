<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerCartController extends Controller
{
    /**
     * Hiển thị giỏ hàng
     */
    public function index() // ham index de hien thi gio hang
    {
        if (!auth()->check()) { // neu check user chua dang nhap
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem giỏ hàng!'); // chuyen huong den trang dang nhap voi thong bao loi
        }

        $cart = Auth::user()->cart; // $cart la gio hang cua user hien tai dang nhap
        
        // Nếu chưa có giỏ hàng, tạo mới
        if (!$cart) { // !cart neu chua co gio hang thi tao moi
            $cart = Cart::create([ // $cart la doi tuong cart moi duoc tao thong qua phuong thuc create cua model Cart
                'user_id' => Auth::id(), // lay id cua user hien tai dang nhap lam user_id
                'total_amount' => 0, // khoi tao tong tien bang 0 
            ]);
        }

        $cartItems = $cart->items()->with('product.category')->get(); // $cartItems la bien chua danh sach cac item trong gio hang voi quan he voi product va category
        // $cart->items() goi den quan he items de lay ve danh sach cac item trong gio hang, sau do su dung with de lay ve quan he voi product va category
        // cuoi cung su dung get() de thuc hien truy van va lay ve ket qua
        $categories = Category::withCount('products')->get(); // Model Category lay ve danh sach danh muc voi so luong san pham trong tung danh muc
        $cartCount = $cart->items()->sum('quantity'); // tinh tong so luong san pham trong gio hang bang ham sum('quantity')

        return view('cart.index', compact('cart', 'cartItems', 'categories', 'cartCount')); // truyen du lieu ra view cart.index voi cac bien cart, cartItems, categories, cartCount
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function add(Request $request, $productId) // ham add de them san pham vao gio hang voi tham so truyen vao la Request $request va $productId
    {
        if (!auth()->check()) { // neu user chua dang nhap
            return response()->json([ // tra ve response dang json
                'success' => false, // bien success de biet them san pham vao gio hang co thanh cong hay khong
                'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!', // thong bao loi
            ], 401); // 401 la ma trang thai HTTP cho biet user chua dang nhap
        }

        $request->validate([ // validate du lieu truyen vao
            'quantity' => 'sometimes|integer|min:1', // quantity la so luong san pham, neu co thi phai la so nguyen va lon hon hoac bang 1
        ]);

        $quantity = $request->get('quantity', 1); // neu khong co tham so quantity thi mac dinh la 1

        try {
            DB::beginTransaction(); // bat dau giao dich

            // Lấy sản phẩm
            $product = Product::findOrFail($productId); // tim kiem san pham theo productId, neu khong tim thay thi tra ve loi 404

            // Lấy hoặc tạo giỏ hàng
            $cart = Auth::user()->cart; // lay gio hang cua user hien tai dang nhap
            if (!$cart) { // neu chua co gio hang thi tao moi
                $cart = Cart::create([ // tao moi gio hang
                    'user_id' => Auth::id(), // lay id cua user hien tai dang nhap lam user_id
                    'total_amount' => 0, // khoi tao tong tien bang 0
                ]);
            }

            // Kiểm tra xem sản phẩm đã có trong giỏ chưa
            $cartItem = CartItem::where('cart_id', $cart->cart_id) //$cartItem la bien chua item trong gio hang
                ->where('product_id', $productId) // tim kiem item trong gio hang theo cart_id va product_id
                ->first(); // lay ve item dau tien tim thay, neu khong tim thay thi tra ve null
                // Bien $cartItem se chua item trong gio hang neu tim thay, neu khong tim thay thi se la null

            if ($cartItem) { // neu tim thay item trong gio hang
                // Nếu có rồi, tăng số lượng
                $cartItem->quantity += $quantity; // tang so luong item trong gio hang qua bien quantity
                $cartItem->save(); // luu thay doi
            } else { 
                // Nếu chưa có, thêm mới
                CartItem::create([
                    'cart_id' => $cart->cart_id, // lay cart_id cua gio hang hien tai
                    'product_id' => $productId, // lay product_id cua san pham can them vao gio hang
                    'quantity' => $quantity, // so luong san pham can them vao gio hang
                    'price' => $product->price, // gia cua san pham tai thoi diem them vao gio hang
                ]);
            }

            // Cập nhật tổng tiền
            $cart->total_amount = $cart->items->sum(function ($item) { // function((%item) se tinh tong tien cua tung item trong gio hang bang cach nhan so luong voi gia cua tung item
                return $item->quantity * $item->price;
            });
            $cart->save(); // luu thay doi

            DB::commit(); // ket thuc giao dich

            return response()->json([ // tra ve response dang json
                'success' => true,
                'message' => 'Đã thêm sản phẩm vào giỏ hàng!',
                'cartCount' => $cart->items()->sum('quantity'),
            ]);

        } catch (\Exception $e) { // neu co loi xay ra trong qua trinh them san pham vao gio hang
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ
     */
    public function update(Request $request, $cartItemId)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập!',
            ], 401);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $cartItem = CartItem::findOrFail($cartItemId);
            
            // Kiểm tra quyền sở hữu
            if ($cartItem->cart->user_id != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có quyền!',
                ], 403);
            }

            $cartItem->quantity = $request->quantity;
            $cartItem->save();

            // Cập nhật tổng tiền
            $cart = $cartItem->cart;
            $cart->total_amount = $cart->items->sum(function ($item) {
                return $item->quantity * $item->price;
            });
            $cart->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật giỏ hàng!',
                'itemTotal' => $cartItem->quantity * $cartItem->price,
                'cartTotal' => $cart->total_amount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function remove($cartItemId)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập!',
            ], 401);
        }

        try {
            DB::beginTransaction();

            $cartItem = CartItem::findOrFail($cartItemId);
            
            // Kiểm tra quyền sở hữu
            if ($cartItem->cart->user_id != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có quyền!',
                ], 403);
            }

            $cart = $cartItem->cart;
            $cartItem->delete();

            // Cập nhật tổng tiền
            $cart->total_amount = $cart->items->sum(function ($item) {
                return $item->quantity * $item->price;
            });
            $cart->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng!',
                'cartTotal' => $cart->total_amount,
                'cartCount' => $cart->items()->sum('quantity'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clear()
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập!');
        }

        try {
            $cart = Auth::user()->cart;
            if ($cart) {
                $cart->items()->delete();
                $cart->total_amount = 0;
                $cart->save();
            }

            return redirect()->back()->with('success', 'Đã xóa toàn bộ giỏ hàng!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
