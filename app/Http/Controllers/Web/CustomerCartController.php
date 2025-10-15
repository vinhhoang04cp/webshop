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
        if (! Auth::check()) { // neu check user chua dang nhap
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem giỏ hàng!'); // chuyen huong den trang dang nhap voi thong bao loi
        }

        $cart = Auth::user()->cart; // $cart la gio hang cua user hien tai dang nhap

        // Nếu chưa có giỏ hàng, tạo mới
        if (! $cart) { // !cart neu chua co gio hang thi tao moi
            $cart = Cart::create([ // $cart la doi tuong cart moi duoc tao thong qua phuong thuc create cua model Cart
                'user_id' => Auth::id(), // lay id cua user hien tai dang nhap lam user_id
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
        if (! Auth::check()) { // neu user chua dang nhap
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
            if (! $cart) { // neu chua co gio hang thi tao moi
                $cart = Cart::create([ // tao moi gio hang
                    'user_id' => Auth::id(), // lay id cua user hien tai dang nhap lam user_id
                ]);
            }

            // Kiểm tra xem sản phẩm đã có trong giỏ chưa
            $cartItem = CartItem::where('cart_id', $cart->cart_id) // $cartItem la bien chua item trong gio hang
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

            // Cập nhật tổng tiền không cần thiết vì sẽ tính động từ items
            // $cart->total_amount sẽ được tính qua method totalPrice()

            DB::commit(); // ket thuc giao dich

            return response()->json([ // tra ve response dang json
                'success' => true,
                'message' => 'Đã thêm sản phẩm vào giỏ hàng!',
                'cartCount' => $cart->items()->sum('quantity'),
            ]);

        } catch (\Exception $e) { // neu co loi xay ra trong qua trinh them san pham vao gio hang
            DB::rollBack();

            return response()->json([ // tra ve response dang json
                'success' => false, //  bien success de biet them san pham vao gio hang co thanh cong hay khong
                'message' => 'Có lỗi xảy ra: '.$e->getMessage(), // thong bao loi
            ], 500);
        }
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ
     */
    public function update(Request $request, $cartItemId) // ham update de cap nhat so luong san pham trong gio hang voi tham so truyen vao la Request $request va $cartItemId
    {
        // check user da dang nhap chua
        if (! Auth::check()) { // neu user chua dang nhap
            return response()->json([ // tra ve response dang json
                'success' => false, // bien success de biet cap nhat so luong san pham trong gio hang co thanh cong hay khong
                'message' => 'Vui lòng đăng nhập!', // thong bao loi
            ], 401);
        }

        $request->validate([ // validate du lieu truyen vao
            'quantity' => 'required|integer|min:1', // quantity la so luong san pham, bat buoc phai la so nguyen va lon hon hoac bang 1
        ]);

        try {
            DB::beginTransaction(); // bat dau giao dich

            $cartItem = CartItem::findOrFail($cartItemId); // tim kiem item trong gio hang theo cartItemId, neu khong tim thay thi tra ve loi 404

            // Kiểm tra quyền sở hữu
            if ($cartItem->cart->user_id != Auth::id()) { // neu user_id cua gio hang khac voi id cua user hien tai dang nhap
                return response()->json([ // tra ve response dang json
                    'success' => false, // bien success de biet cap nhat so luong san pham trong gio hang co thanh cong hay khong
                    'message' => 'Không có quyền!', // thong bao loi
                ], 403); // 403 la ma trang thai HTTP cho biet user khong co quyen truy cap
            }

            $cartItem->quantity = $request->quantity; // cap nhat so luong san pham trong gio hang qua bien quantity
            $cartItem->save(); // luu thay doi

            // Cập nhật tổng tiền không cần thiết - tính động
            $cart = $cartItem->cart; // lay gio hang cua item trong gio hang

            DB::commit(); // ket thuc giao dich

            return response()->json([ // tra ve response dang json
                'success' => true, // bien success de biet cap nhat so luong san pham trong gio hang co thanh cong hay khong
                'message' => 'Đã cập nhật giỏ hàng!', // thong bao thanh cong
                'cartCount' => $cart->items()->sum('quantity'), // tinh tong so luong san pham trong gio hang bang ham sum('quantity')
                'itemTotal' => $cartItem->quantity * $cartItem->price, // tinh tong tien cua item trong gio hang
                'cartTotal' => $cart->totalPrice(), // Sử dụng method totalPrice() thay vì total_amount
            ]);

        } catch (\Exception $e) { // neu co loi xay ra trong qua trinh cap nhat so luong san pham trong gio hang
            DB::rollBack(); // quay lai trang thai truoc khi bat dau giao dich

            return response()->json([ // tra ve response dang json
                'success' => false,
                'message' => 'Có lỗi xảy ra: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function remove($cartItemId)
    {
        if (! Auth::check()) { // neu user chua dang nhap
            return response()->json([ // tra ve response dang json
                'success' => false, // bien success de biet xoa san pham khoi gio hang co thanh cong hay khong
                'message' => 'Vui lòng đăng nhập!', // thong bao loi
            ], 401);
        }

        try {
            DB::beginTransaction(); // bat dau giao dich

            $cartItem = CartItem::findOrFail($cartItemId);
            // $cartItem la bien chua item trong gio hang, tim kiem item trong gio hang theo cartItemId, neu khong tim thay thi tra ve loi 404

            // Kiểm tra quyền sở hữu
            if ($cartItem->cart->user_id != Auth::id()) { // neu user_id cua gio hang khac voi id cua user hien tai dang nhap
                //$cartItem->cart-> user_id la cach truy cap user_id cua gio hang thong qua item trong gio hang
                return response()->json([ // tra ve response dang json
                    'success' => false, //  bien success de biet xoa san pham khoi gio hang co thanh cong hay khong
                    'message' => 'Không có quyền!', // thong bao loi
                ], 403); // 403 la ma trang thai HTTP cho biet user khong co quyen truy cap
            }

            $cart = $cartItem->cart; // $cart la bien chua gio hang cua item trong gio hang
            $cartItem->delete(); // xoa item trong gio hang

            // Cập nhật tổng tiền không cần thiết - tính động 

            DB::commit(); // ket thuc giao dich

            return response()->json([  // tra ve response dang json
                'success' => true, // bien success de biet xoa san pham khoi gio hang co thanh cong hay khong
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng!', // thong bao thanh cong
                'cartTotal' => $cart->totalPrice(), // Sử dụng method totalPrice()
                'cartCount' => $cart->items()->sum('quantity'),
            ]);

        } catch (\Exception $e) { // neu co loi xay ra trong qua trinh xoa san pham khoi gio hang
            DB::rollBack(); // quay lai trang thai truoc khi bat dau giao dich

            return response()->json([ // tra ve response dang json
                'success' => false, // bien success de biet xoa san pham khoi gio hang co thanh cong hay khong
                'message' => 'Có lỗi xảy ra: '.$e->getMessage(), // thong bao loi
            ], 500); 
        }
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clear()
    {
        if (! Auth::check()) { // neu user chua dang nhap
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập!'); // tra ve trang login voi yeu cau dang nhap
        }

        try { // bat dau khoi tao try catch de bat loi
            $cart = Auth::user()->cart; // lay gio hang cua user hien tai dang nhap
            if ($cart) { // neu co gio hang
                $cart->items()->delete(); // xoa toan bo item trong gio hang
                // Không cần reset total_amount vì tính động
            }

            return redirect()->back()->with('success', 'Đã xóa toàn bộ giỏ hàng!'); // tra ve trang truoc do voi thong bao thanh cong

        } catch (\Exception $e) { // neu co loi xay ra trong qua trinh xoa toan bo gio hang
            return redirect()->back()->with('error', 'Có lỗi xảy ra: '.$e->getMessage()); // tra ve trang truoc do voi thong bao loi
        }
    }
}
