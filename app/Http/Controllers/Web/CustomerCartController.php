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
    public function checkout(Request $request) // Ham checkout de xu ly thanh toan gio hang
    {
        if (! Auth::check()) { // neu user chua dang nhap
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để thanh toán!'); // chuyen huong den trang dang nhap voi thong bao loi
        }

        // Validate thông tin giao hàng
        $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:1000',
            'note' => 'nullable|string|max:500',
        ], [
            'shipping_name.required' => 'Vui lòng nhập họ và tên người nhận',
            'shipping_phone.required' => 'Vui lòng nhập số điện thoại',
            'shipping_address.required' => 'Vui lòng nhập địa chỉ giao hàng',
        ]);

        $cart = Auth::user()->cart; // $cart la gio hang cua user hien tai dang nhap
        if (! $cart || $cart->items()->count() == 0) { // ! $cart neu chua co cart, $cart->items()->count() == 0 neu so luong item trong gio hang bang 0
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống!'); // chuyen huong den trang gio hang voi thong bao loi
        }

        DB::beginTransaction();
        try {
            // Kiểm tra tồn kho trước khi tạo đơn hàng
            foreach ($cart->items as $item) { // duyet qua tung item trong gio hang
                $product = $item->product; // $product la san pham cua item trong gio hang
                if (! $product) { // neu khong tim thay san pham
                    throw new \Exception('Sản phẩm không tồn tại!');
                }

                // Kiểm tra số lượng tồn kho
                if ($product->stock_quantity < $item->quantity) { // neu so luong ton kho cua san pham nho hon so luong trong gio hang
                    throw new \Exception("Sản phẩm '{$product->name}' chỉ còn {$product->stock_quantity} sản phẩm trong kho!"); // thong bao loi so luong ton kho khong du
                }
            }

            // Tạo đơn hàng với thông tin giao hàng
            $order = new \App\Models\Order; // Su dung model Order de tao don hang moi
            $order->user_id = Auth::id(); // Auth::id() lay id cua user hien tai dang nhap
            $order->total_amount = $cart->totalPrice(); // $cart->totalPrice() goi den method totalPrice() trong model Cart de tinh tong tien gio hang
            $order->status = 'pending'; // trang thai don hang mac dinh la 'pending'
            $order->order_date = now();  // thoi gian dat hang la thoi diem hien tai

            // Thêm thông tin giao hàng COD
            $order->shipping_name = $request->shipping_name; // Lấy tên người nhận từ form
            $order->shipping_phone = $request->shipping_phone; // Lấy số điện thoại từ form
            $order->shipping_address = $request->shipping_address; // Lấy địa chỉ từ form
            $order->note = $request->note; // Lấy ghi chú từ form (có thể null)

            $order->save(); // luu don hang vao database

            // Thêm các sản phẩm vào order_items VÀ TRỪ TỒN KHO NGAY
            foreach ($cart->items as $item) { // voi moi item trong gio hang
                $product = $item->product; // $product la san pham cua item trong gio hang

                // Tạo order item
                $orderItem = new \App\Models\OrderItem; // Su dung model OrderItem de tao moi item trong don hang
                $orderItem->order_id = $order->order_id; // gan order_id cua item trong don hang bang order_id cua don hang vua tao
                $orderItem->product_id = $item->product_id; // gan product_id cua item trong don hang bang product_id cua item trong gio hang
                $orderItem->quantity = $item->quantity; // gan so luong cua item trong don hang bang so luong cua item trong gio hang
                $orderItem->price = $item->price ?? $product->price; // gan gia cua item trong don hang bang gia cua item trong gio hang, neu khong co thi lay gia cua san pham
                $orderItem->save(); // luu item trong don hang vao database

                // TRỪ TỒN KHO NGAY KHI ĐẶT HÀNG (giữ hàng cho khách)
                $product->decrement('stock_quantity', $item->quantity); // decrement la giam so luong ton kho cua san pham

                // Cập nhật inventory - tăng stock_out và giảm current_stock
                $inventory = \App\Models\Inventory::firstOrCreate( // firstOrCreate tim kiem hoac tao moi inventory cho san pham
                    ['product_id' => $product->product_id], // dieu kien tim kiem inventory theo product_id
                    [
                        'stock_in' => 0, // neu khong tim thay thi tao moi voi stock_in = 0
                        'stock_out' => 0, // neu khong tim thay thi tao moi voi stock_out = 0
                        'current_stock' => 0, // neu khong tim thay thi tao moi voi current_stock = 0
                    ]
                );
                $inventory->increment('stock_out', $item->quantity); // increment la tang so luong stock_out cua inventory
                $inventory->decrement('current_stock', $item->quantity); // decrement la giam so luong current_stock cua inventory
                $inventory->save(); // luu thay doi inventory vao database
            }

            $cart->items()->delete(); // xoa toan bo item trong gio hang
            // Không cần reset total_amount vì tính động

            DB::commit(); // ket thuc giao dich

            return redirect()->route('cart.index')->with('success', 'Đặt hàng thành công! Đơn hàng của bạn đã được ghi nhận. Chúng tôi sẽ liên hệ với bạn qua số điện thoại '.$request->shipping_phone.' để xác nhận.');
            // chuyen huong den trang gio hang voi thong bao thanh cong
        } catch (\Exception $e) { // neu co loi xay ra trong qua trinh dat hang
            DB::rollBack(); // quay lai trang thai truoc khi bat dau giao dich

            return redirect()->route('cart.index')->with('error', 'Có lỗi xảy ra khi đặt hàng: '.$e->getMessage()); // chuyen huong den trang gio hang voi thong bao loi
        }
    }

    /**
     * Hiển thị giỏ hàng
     */
    public function index() // ham index de hien thi gio hang
    {
        if (! Auth::check()) { // neu check user chua dang nhap
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem giỏ hàng!'); // chuyen huong den trang dang nhap voi thong bao loi
        }

        try {
            $cart = Auth::user()->cart; // $cart la gio hang cua user hien tai dang nhap

            // Nếu chưa có giỏ hàng, tạo mới
            if (! $cart) { // !cart neu chua co gio hang thi tao moi
                $cart = Cart::create([ // $cart la doi tuong cart moi duoc tao thong qua phuong thuc create cua model Cart
                    'user_id' => Auth::id(), // lay id cua user hien tai dang nhap lam user_id
                ]);
            }

            // Tải các mục trong giỏ hàng cùng với thông tin sản phẩm và danh mục
            $cartItems = collect(); // Khởi tạo bộ sưu tập rỗng

            try {
                $cartItems = $cart->items() // $cart->items() lay tat ca cac item trong gio hang
                    ->with(['product' => function ($query) { // tai thong tin san pham cho moi item trong gio hang
                        $query->with('category'); // tai thong tin danh muc cho san pham
                    }]) // lay thong tin quan he product va category
                    ->get(); // thuc thi truy van va lay ve ket qua
            } catch (\Exception $e) {
                \Log::error('Error loading cart items: '.$e->getMessage());
                // Return empty collection on error
                $cartItems = collect();
            }

            $categories = \App\Models\Category::withCount('products')->get(); // Model Category lay ve danh sach danh muc voi so luong san pham trong tung danh muc
            $cartCount = $cartItems->sum('quantity'); // tinh tong so luong san pham trong gio hang

            return view('cart.index', compact('cart', 'cartItems', 'categories', 'cartCount')); // truyen du lieu ra view cart.index voi cac bien cart, cartItems, categories, cartCount

        } catch (\Exception $e) {
            \Log::error('Error in cart index: '.$e->getMessage());

            return redirect()->route('home')->with('error', 'Có lỗi xảy ra khi tải giỏ hàng: '.$e->getMessage());
        }
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function add(Request $request, $productId) // ham add de them san pham vao gio hang voi tham so truyen vao la Request $request va $productId
    {
        if (! Auth::check()) { // neu user chua dang nhap
            // Kiểm tra nếu là AJAX request thì trả về JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!',
                ], 401);
            }

            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
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

            // Kiểm tra nếu là AJAX request thì trả về JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã thêm sản phẩm vào giỏ hàng!',
                ]);
            }

            return redirect()->back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng!');

        } catch (\Exception $e) { // neu co loi xay ra trong qua trinh them san pham vao gio hang
            DB::rollBack();

            // Kiểm tra nếu là AJAX request thì trả về JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Có lỗi xảy ra: '.$e->getMessage());
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
                'itemTotal' => $cartItem->quantity * ($cartItem->price ?? $cartItem->product->price ?? 0), // tinh tong tien cua item trong gio hang
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
                // $cartItem->cart-> user_id la cach truy cap user_id cua gio hang thong qua item trong gio hang
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
