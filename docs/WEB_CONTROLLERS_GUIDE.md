# TÀI LIỆU CHI TIẾT VỀ CÁC WEB CONTROLLER

## Mục lục
1. [AuthController](#authcontroller)
2. [HomeController](#homecontroller)
3. [CustomerProductController](#customerproductcontroller)
4. [CustomerCartController](#customercartcontroller)
5. [OrderController](#ordercontroller)
6. [ProfileController](#profilecontroller)
7. [CategoryController](#categorycontroller)
8. [ProductController](#productcontroller)
9. [InventoryController](#inventorycontroller)
10. [CouponController](#couponcontroller)
11. [UserManagementController](#usermanagementcontroller)
12. [ReportController](#reportcontroller)
13. [SocialAuthController](#socialauthcontroller)
14. [PasswordResetController](#passwordresetcontroller)

---

## AuthController

### Mục đích
Controller chịu trách nhiệm xử lý tất cả các chức năng liên quan đến xác thực người dùng (đăng nhập, đăng ký, đăng xuất) và hiển thị dashboard cho admin/manager.

### Các phương thức

#### `showLogin()`
**Chức năng:** Hiển thị form đăng nhập

**Hoạt động:**
- Kiểm tra người dùng đã đăng nhập chưa bằng `Auth::check()`
- Nếu đã đăng nhập → redirect về dashboard
- Nếu chưa đăng nhập → hiển thị view `auth.login`

**Return:** `RedirectResponse|View`

---

#### `showRegister()`
**Chức năng:** Hiển thị form đăng ký tài khoản mới

**Hoạt động:**
- Kiểm tra trạng thái đăng nhập
- Nếu đã đăng nhập → redirect về dashboard
- Nếu chưa đăng nhập → hiển thị view `auth.register`

**Return:** `RedirectResponse|View`

---

#### `login(Request $request)`
**Chức năng:** Xử lý đăng nhập qua web form

**Parameters:**
- `$request`: Chứa email và password từ form

**Hoạt động:**
1. **Validate dữ liệu:**
   - `email`: required, định dạng email
   - `password`: required

2. **Xác thực người dùng:**
   - Tìm user theo email trong database
   - So sánh password đã mã hóa với `Hash::check()`
   - Nếu sai → trả về lỗi và giữ lại dữ liệu đã nhập

3. **Đăng nhập thành công:**
   - Gọi `Auth::login($user)` để tạo session
   - Redirect dựa trên vai trò:
     - Admin/Manager → dashboard
     - Customer → trang sản phẩm
     - User thông thường → trang chủ

**Return:** `RedirectResponse`

---

#### `register(Request $request)`
**Chức năng:** Xử lý đăng ký tài khoản mới

**Parameters:**
- `$request`: Chứa thông tin đăng ký (name, email, password, phone, address)

**Hoạt động:**
1. **Validate dữ liệu:**
   - `name`: required, string, max 255
   - `email`: required, email, unique trong bảng users
   - `password`: required, min 8 ký tự, confirmed
   - `phone`: nullable, max 20
   - `address`: nullable, max 500

2. **Tạo user mới:**
   - Mã hóa password bằng `Hash::make()`
   - Tạo bản ghi user trong database

3. **Gán role tự động:**
   - Sử dụng database transaction
   - Tìm role 'customer'
   - Tạo bản ghi UserRole với assigned_at = now()
   - Commit hoặc rollback nếu có lỗi

4. **Redirect về trang login với thông báo thành công**

**Return:** `RedirectResponse`

---

#### `logout()`
**Chức năng:** Đăng xuất người dùng

**Hoạt động:**
- Gọi `Auth::logout()` để hủy session
- Xóa thông tin authentication
- Redirect về trang login với thông báo

**Return:** `RedirectResponse`

---

#### `dashboard()`
**Chức năng:** Hiển thị trang dashboard cho admin và manager

**Hoạt động:**
1. **Kiểm tra quyền:**
   - Lấy user hiện tại: `Auth::user()`
   - Kiểm tra role admin hoặc manager
   - Nếu không có quyền → logout và redirect về login

2. **Tính toán thống kê:**
   - `$productsCount`: Đếm tổng số sản phẩm
   - `$ordersCount`: Đếm tổng số đơn hàng
   - `$usersCount`: Đếm tổng số người dùng
   - `$totalRevenue`: Tổng doanh thu (loại trừ đơn hủy)
   - `$recentOrders`: 5 đơn hàng gần nhất (sắp xếp theo order_date desc)

3. **Eager loading:**
   - Load relationship 'user' cho orders

4. **Xử lý lỗi:**
   - Try-catch để xử lý exception
   - Fallback về giá trị 0 nếu có lỗi

**Return:** `View`

---

## HomeController

### Mục đích
Controller xử lý trang chủ website, hiển thị sản phẩm nổi bật và danh mục.

### Các phương thức

#### `index()`
**Chức năng:** Hiển thị trang chủ website

**Hoạt động:**
1. **Lấy danh mục:**
   - Query categories với `withCount('products')`
   - Sắp xếp theo tên

2. **Lấy sản phẩm nổi bật:**
   - 8 sản phẩm random với `inRandomOrder()`
   - Eager load category

3. **Lấy sản phẩm mới:**
   - 8 sản phẩm mới nhất theo created_at
   - Eager load category

4. **Đếm giỏ hàng:**
   - Kiểm tra user đã đăng nhập
   - Tính tổng quantity của cart items
   - Mặc định = 0 nếu chưa đăng nhập

**Dữ liệu trả về:**
- `categories`: Danh sách danh mục với số lượng sản phẩm
- `featuredProducts`: 8 sản phẩm nổi bật
- `newProducts`: 8 sản phẩm mới
- `cartCount`: Số lượng sản phẩm trong giỏ

**Return:** `View`

---

## CustomerProductController

### Mục đích
Controller xử lý hiển thị và tìm kiếm sản phẩm cho khách hàng, bao gồm danh sách, chi tiết, lọc và đánh giá.

### Các phương thức

#### `index(Request $request)`
**Chức năng:** Hiển thị danh sách sản phẩm với tìm kiếm, lọc và sắp xếp

**Parameters:**
- `$request`: Chứa tham số q, category, min_price, max_price, sort

**Hoạt động:**
1. **Tìm kiếm:**
   - Tham số `q`: Tìm theo name hoặc description (LIKE)

2. **Lọc theo danh mục:**
   - Tham số `category`: Lọc theo category_id

3. **Lọc theo giá:**
   - Tham số `min_price`: Giá >= min_price
   - Tham số `max_price`: Giá <= max_price

4. **Sắp xếp:**
   - `latest`: Mới nhất (mặc định)
   - `price_asc`: Giá tăng dần
   - `price_desc`: Giá giảm dần
   - `name_asc`: Tên A-Z
   - `name_desc`: Tên Z-A

5. **Phân trang:** 12 sản phẩm/trang

6. **Đếm giỏ hàng**

**Return:** `View`

---

#### `show($id)`
**Chức năng:** Hiển thị chi tiết sản phẩm

**Parameters:**
- `$id`: ID của sản phẩm

**Hoạt động:**
1. **Lấy sản phẩm:**
   - `findOrFail($id)` → throw 404 nếu không tìm thấy
   - Eager load: category, details, inventory, ratings.user

2. **Sản phẩm liên quan:**
   - Cùng danh mục
   - Loại trừ sản phẩm hiện tại
   - Lấy tối đa 4 sản phẩm

3. **Lấy categories và đếm giỏ hàng**

**Dữ liệu trả về:**
- `product`: Thông tin sản phẩm đầy đủ
- `relatedProducts`: Sản phẩm cùng danh mục
- `categories`: Danh sách danh mục
- `cartCount`: Số lượng trong giỏ

**Return:** `View`

---

#### `search(Request $request)`
**Chức năng:** Tìm kiếm sản phẩm

**Hoạt động:**
- Gọi lại phương thức `index($request)` để xử lý

**Return:** `View`

---

#### `category($id)`
**Chức năng:** Hiển thị sản phẩm theo danh mục

**Parameters:**
- `$id`: ID của category

**Hoạt động:**
1. Tìm category theo ID
2. Lọc products theo category_id
3. Sắp xếp theo created_at mới nhất
4. Phân trang 12 sản phẩm

**Return:** `View`

---

#### `addRating(Request $request, $productId)`
**Chức năng:** Thêm đánh giá cho sản phẩm

**Parameters:**
- `$request`: Chứa rating (1-5) và review
- `$productId`: ID của sản phẩm

**Hoạt động:**
1. **Kiểm tra đăng nhập:**
   - Chưa đăng nhập → redirect về login

2. **Kiểm tra sản phẩm:**
   - Sản phẩm không tồn tại → trả về lỗi

3. **Kiểm tra đã đánh giá:**
   - Tìm rating theo user_id và product_id
   - Nếu đã có → trả về lỗi

4. **Validate:**
   - `rating`: required, integer, 1-5
   - `review`: nullable, max 1000 ký tự

5. **Tạo rating mới:**
   - Lưu user_id, product_id, rating, review
   - Redirect về chi tiết sản phẩm

**Return:** `RedirectResponse`

---

#### `promotions()`
**Chức năng:** Hiển thị sản phẩm khuyến mãi

**Hoạt động:**
1. **Lọc sản phẩm khuyến mãi:**
   - original_price != NULL
   - original_price > price
   - Sắp xếp theo % giảm giá cao nhất

2. **Phân trang:** 12 sản phẩm

3. **Đếm giỏ hàng**

**Return:** `View`

---

## CustomerCartController

### Mục đích
Controller xử lý giỏ hàng của khách hàng: thêm, sửa, xóa sản phẩm và thanh toán.

### Các phương thức

#### `index()`
**Chức năng:** Hiển thị giỏ hàng

**Hoạt động:**
1. **Kiểm tra đăng nhập:**
   - Chưa đăng nhập → redirect về login

2. **Lấy giỏ hàng:**
   - Lấy cart của user hiện tại
   - Nếu chưa có → tạo mới với user_id

3. **Load cart items:**
   - Eager load: product.category
   - Sử dụng try-catch để xử lý lỗi
   - Fallback về collection rỗng nếu lỗi

4. **Tính tổng:**
   - `$cartCount`: Tổng quantity của items

**Return:** `View`

---

#### `add(Request $request, $productId)`
**Chức năng:** Thêm sản phẩm vào giỏ hàng

**Parameters:**
- `$request`: Chứa quantity (mặc định 1)
- `$productId`: ID của sản phẩm cần thêm

**Hoạt động:**
1. **Kiểm tra đăng nhập**

2. **Validate:**
   - `quantity`: sometimes, integer, min 1

3. **Sử dụng transaction:**
   - Tìm product theo ID
   - Lấy hoặc tạo cart cho user
   - Kiểm tra sản phẩm đã có trong giỏ chưa:
     - Có → tăng quantity
     - Chưa → tạo CartItem mới với price hiện tại
   - Commit hoặc rollback

**Return:** `RedirectResponse`

---

#### `update(Request $request, $cartItemId)`
**Chức năng:** Cập nhật số lượng sản phẩm trong giỏ

**Parameters:**
- `$request`: Chứa quantity mới
- `$cartItemId`: ID của cart item

**Hoạt động:**
1. **Validate:**
   - `quantity`: required, integer, min 1

2. **Kiểm tra quyền:**
   - Tìm cart item
   - Kiểm tra cart.user_id == Auth::id()
   - Không khớp → trả về lỗi

3. **Cập nhật:**
   - Cập nhật quantity mới
   - Lưu vào database

**Return:** `RedirectResponse`

---

#### `remove($cartItemId)`
**Chức năng:** Xóa sản phẩm khỏi giỏ hàng

**Parameters:**
- `$cartItemId`: ID của cart item cần xóa

**Hoạt động:**
1. **Kiểm tra quyền:**
   - Tìm cart item
   - Verify user_id

2. **Xóa:**
   - Sử dụng transaction
   - Gọi `$cartItem->delete()`
   - Commit

**Return:** `RedirectResponse`

---

#### `clear()`
**Chức năng:** Xóa toàn bộ giỏ hàng

**Hoạt động:**
1. Lấy cart của user
2. Xóa tất cả items: `$cart->items()->delete()`
3. Redirect về trang trước

**Return:** `RedirectResponse`

---

#### `checkout(Request $request)`
**Chức năng:** Xử lý thanh toán và đặt hàng (COD)

**Parameters:**
- `$request`: Chứa thông tin giao hàng và mã giảm giá

**Hoạt động:**
1. **Validate thông tin giao hàng:**
   - `shipping_name`: required, max 255
   - `shipping_phone`: required, max 20
   - `shipping_address`: required, max 1000
   - `note`: nullable, max 500
   - `coupon_code`: nullable, max 50

2. **Kiểm tra giỏ hàng:**
   - Lấy cart của user
   - Kiểm tra có items không

3. **Database Transaction:**
   
   a. **Kiểm tra tồn kho:**
      - Duyệt qua từng cart item
      - Verify product tồn tại
      - Kiểm tra stock_quantity đủ không
      - Throw exception nếu không đủ

   b. **Xử lý coupon (nếu có):**
      - Tìm coupon theo code
      - Validate coupon: `$coupon->isValid($totalAmount)`
      - Tính discount: `$coupon->calculateDiscount($totalAmount)`
      - Trừ discount vào total_amount

   c. **Tạo đơn hàng:**
      - Tạo Order mới với:
        - user_id, total_amount, status = 'pending'
        - shipping_name, shipping_phone, shipping_address, note
        - order_date = now()
      - Lưu vào database

   d. **Tăng used_count của coupon (nếu có)**

   e. **Tạo order items và TRỪ TỒN KHO:**
      - Duyệt qua từng cart item:
        - Tạo OrderItem với order_id, product_id, quantity, price
        - TRỪ stock_quantity: `$product->decrement('stock_quantity', $quantity)`
        - Cập nhật Inventory:
          - Tăng stock_out
          - Giảm current_stock

   f. **Xóa cart items:**
      - `$cart->items()->delete()`

4. **Commit transaction**

5. **Redirect với thông báo:**
   - Thông báo thành công
   - Hiển thị số tiền tiết kiệm (nếu dùng coupon)
   - Thông báo sẽ liên hệ qua SĐT

**Lưu ý quan trọng:**
- Hệ thống TRỪ TỒN KHO NGAY khi khách đặt hàng (giữ hàng cho khách)
- Nếu hủy đơn → cần hoàn trả tồn kho

**Return:** `RedirectResponse`

---

## OrderController

### Mục đích
Controller quản lý đơn hàng cho admin: xem, sửa trạng thái, xóa đơn hàng.

### Các phương thức

#### `index(Request $request)`
**Chức năng:** Hiển thị danh sách đơn hàng

**Parameters:**
- `$request`: Chứa search và status filter

**Hoạt động:**
1. **Query orders:**
   - Eager load: user, items.product

2. **Tìm kiếm:**
   - Theo order_id (LIKE)
   - Hoặc theo thông tin user (name, email) với `whereHas`

3. **Lọc theo trạng thái:**
   - pending, processing, shipped, delivered, cancelled

4. **Sắp xếp:** order_date desc (mới nhất trước)

5. **Phân trang:** 15 đơn/trang

**Dữ liệu trả về:**
- `orders`: Danh sách đơn hàng
- `statuses`: Danh sách trạng thái (key-value)

**Return:** `View`

---

#### `show($id)`
**Chức năng:** Hiển thị chi tiết đơn hàng

**Parameters:**
- `$id`: ID của order

**Hoạt động:**
1. Tìm order với eager load: user, items.product, items.productDetail
2. Lấy danh sách trạng thái có thể chuyển: `getAvailableStatuses()`
3. Trả về view với order và availableStatuses

**Return:** `View`

---

#### `edit($id)`
**Chức năng:** Hiển thị form chỉnh sửa đơn hàng

**Hoạt động:**
- Tương tự show() nhưng trả về view form edit

**Return:** `View`

---

#### `update(Request $request, $id)`
**Chức năng:** Cập nhật trạng thái đơn hàng

**Parameters:**
- `$request`: Chứa status mới
- `$id`: ID của order

**Hoạt động:**
1. **Validate:**
   - `status`: required, in:pending,processing,shipped,delivered,cancelled

2. **Kiểm tra chuyển đổi:**
   - Gọi `$order->canTransitionTo($newStatus)`
   - Nếu không thể → trả về lỗi

3. **Sử dụng transaction:**
   
   a. **Cập nhật trạng thái:**
      - `$order->update(['status' => $newStatus])`

   b. **Nếu chuyển sang 'delivered':**
      - Gọi `updateInventoryOnDelivered($order)`
      - (Chỉ log, không trừ tồn kho vì đã trừ khi đặt hàng)

   c. **Nếu chuyển sang 'cancelled':**
      - Gọi `restoreInventoryOnCancelled($order)`
      - Hoàn trả tồn kho:
        - Tăng stock_quantity
        - Giảm stock_out
        - Tăng current_stock

4. **Commit và redirect**

**Return:** `RedirectResponse`

---

#### `destroy($id)`
**Chức năng:** Xóa đơn hàng

**Parameters:**
- `$id`: ID của order

**Hoạt động:**
1. Tìm order
2. **Kiểm tra điều kiện:**
   - Chỉ cho phép xóa đơn đã hủy hoặc đã giao
   - Nếu không → trả về lỗi
3. Xóa order
4. Redirect về danh sách

**Return:** `RedirectResponse`

---

#### `getAvailableStatuses($currentStatus)` (private)
**Chức năng:** Lấy danh sách trạng thái có thể chuyển đổi

**Parameters:**
- `$currentStatus`: Trạng thái hiện tại

**Hoạt động:**
- Lấy từ `Order::STATUS_TRANSITIONS[$currentStatus]`
- Map với label tiếng Việt
- Trả về mảng [status => label]

**Return:** `array`

---

#### `getStatusLabel($status)` (private)
**Chức năng:** Chuyển mã trạng thái sang tên tiếng Việt

**Return:** `string`

---

#### `updateInventoryOnDelivered(Order $order)` (private)
**Chức năng:** Xử lý khi đơn hàng được giao (chỉ log)

**Lưu ý:** Không trừ tồn kho vì đã trừ khi đặt hàng

---

#### `restoreInventoryOnCancelled(Order $order)` (private)
**Chức năng:** Hoàn trả tồn kho khi hủy đơn

**Hoạt động:**
1. Lấy tất cả order items
2. Duyệt từng item:
   - Tăng product.stock_quantity
   - Giảm inventory.stock_out
   - Tăng inventory.current_stock

---

## ProfileController

### Mục đích
Controller quản lý thông tin cá nhân của người dùng.

### Các phương thức

#### `index()`
**Chức năng:** Hiển thị trang quản lý profile

**Hoạt động:**
- Lấy user hiện tại
- Trả về view với thông tin user

**Return:** `View`

---

#### `updateProfile(Request $request)`
**Chức năng:** Cập nhật thông tin cá nhân

**Parameters:**
- `$request`: Chứa name, phone, address, avatar

**Hoạt động:**
1. **Validate:**
   - `name`: required, max 255
   - `phone`: nullable, max 20
   - `address`: nullable, max 500
   - `avatar`: nullable, image, jpeg/png/jpg/gif, max 2MB

2. **Cập nhật thông tin:**
   - name, phone, address

3. **Xử lý avatar (nếu có):**
   - Xóa avatar cũ nếu tồn tại
   - Upload avatar mới vào `storage/app/public/avatars`
   - Lưu path vào database

4. **Lưu và quay lại**

**Return:** `RedirectResponse`

---

#### `changePassword(Request $request)`
**Chức năng:** Đổi mật khẩu

**Parameters:**
- `$request`: Chứa current_password, new_password, new_password_confirmation

**Hoạt động:**
1. **Validate:**
   - `current_password`: required
   - `new_password`: required, min 8, confirmed

2. **Kiểm tra mật khẩu hiện tại:**
   - `Hash::check($request->current_password, $user->password)`
   - Nếu sai → trả về lỗi

3. **Cập nhật mật khẩu:**
   - `Hash::make($request->new_password)`
   - Lưu vào database

**Return:** `RedirectResponse`

---

## CategoryController

### Mục đích
Controller quản lý danh mục sản phẩm cho admin.

### Các phương thức

#### `index(Request $request)`
**Chức năng:** Hiển thị danh sách danh mục

**Parameters:**
- `$request`: Chứa search

**Hoạt động:**
1. Query categories
2. **Tìm kiếm:** Theo name (LIKE)
3. **Phân trang:** 10 danh mục/trang
4. Lấy tất cả categories (cho dropdown)

**Return:** `View`

---

#### `create()`
**Chức năng:** Hiển thị form tạo danh mục

**Return:** `View`

---

#### `store(Request $request)`
**Chức năng:** Lưu danh mục mới

**Parameters:**
- `$request`: Chứa name, description

**Hoạt động:**
1. **Validate:**
   - `name`: required, max 150, unique
   - `description`: nullable

2. **Tạo category:**
   - Sử dụng `Category::create()`
   - Redirect về danh sách

**Return:** `RedirectResponse`

---

#### `show($id)`
**Chức năng:** Hiển thị chi tiết danh mục

**Parameters:**
- `$id`: ID của category

**Hoạt động:**
- Tìm category với eager load products
- Trả về view chi tiết

**Return:** `View`

---

#### `edit($id)`
**Chức năng:** Hiển thị form chỉnh sửa

**Return:** `View`

---

#### `update(Request $request, $id)`
**Chức năng:** Cập nhật danh mục

**Parameters:**
- `$request`: Chứa name, description
- `$id`: ID của category

**Hoạt động:**
1. **Validate:**
   - `name`: unique ngoại trừ ID hiện tại

2. **Cập nhật:**
   - Tìm category
   - Update name, description
   - Redirect về danh sách

**Return:** `RedirectResponse`

---

#### `destroy($id)`
**Chức năng:** Xóa danh mục

**Parameters:**
- `$id`: ID của category

**Hoạt động:**
- Tìm và xóa category
- Lưu ý: Cần xử lý ràng buộc với products

**Return:** `RedirectResponse`

---

## ProductController

### Mục đích
Controller quản lý sản phẩm cho admin: CRUD sản phẩm và tự động quản lý inventory.

### Các phương thức

#### `index(Request $request)`
**Chức năng:** Hiển thị danh sách sản phẩm

**Parameters:**
- `$request`: Chứa search

**Hoạt động:**
1. Query products với eager load category
2. **Tìm kiếm:** Theo name hoặc description (LIKE)
3. **Phân trang:** 12 sản phẩm/trang
4. Lấy tất cả products và categories

**Dữ liệu trả về:**
- `paginatedProducts`: Sản phẩm trên trang hiện tại
- `products`: Tất cả sản phẩm
- `categories`: Danh sách danh mục
- `pagination`: Thông tin phân trang

**Return:** `View`

---

#### `create()`
**Chức năng:** Hiển thị form tạo sản phẩm

**Hoạt động:**
- Lấy danh sách categories
- Trả về view form

**Return:** `View`

---

#### `store(Request $request)`
**Chức năng:** Lưu sản phẩm mới

**Parameters:**
- `$request`: Chứa thông tin sản phẩm đầy đủ

**Hoạt động:**
1. **Validate:**
   - `name`: required, max 255
   - `description`: nullable
   - `price`: required, numeric, min 0
   - `category_id`: required, exists in categories
   - `image`: nullable, image, max 2MB
   - `image_url`: nullable, url
   - `stock_quantity`: required, integer, min 0
   - **ProductDetail fields:** color, storage, ram, screen_size, chip, battery, camera_main, camera_front, os, special_features (tất cả nullable, max 100)

2. **Xử lý upload ảnh:**
   - Nếu upload file:
     - Tạo tên unique: `time()_original_name`
     - Lưu vào `storage/app/public/products`
     - Tạo URL: `/storage/products/filename`
   - Nếu không upload → dùng image_url

3. **Tạo Product:**
   - Lưu name, description, price, category_id, image_url, stock_quantity

4. **Tự động tạo Inventory:**
   - stock_in = stock_quantity
   - stock_out = 0
   - current_stock = stock_quantity

5. **Tạo ProductDetail (nếu có):**
   - Kiểm tra ít nhất 1 trường có giá trị
   - Tạo bản ghi ProductDetail

**Return:** `RedirectResponse`

---

#### `show($id)`
**Chức năng:** Hiển thị chi tiết sản phẩm

**Parameters:**
- `$id`: ID của product

**Hoạt động:**
- Tìm product với eager load: category, details
- Trả về view chi tiết

**Return:** `View`

---

#### `edit($id)`
**Chức năng:** Hiển thị form chỉnh sửa

**Hoạt động:**
- Tìm product với details
- Lấy categories
- Trả về view form

**Return:** `View`

---

#### `update(Request $request, $id)`
**Chức năng:** Cập nhật sản phẩm

**Parameters:**
- `$request`: Thông tin cập nhật
- `$id`: ID của product

**Hoạt động:**
1. **Validate** (tương tự store)

2. **Xử lý ảnh mới:**
   - Nếu upload file mới:
     - Xóa ảnh cũ (nếu là file upload)
     - Upload ảnh mới
   - Nếu thay đổi URL:
     - Xóa file cũ (nếu có)
     - Dùng URL mới

3. **Tính toán thay đổi tồn kho:**
   - `$oldQuantity` = stock_quantity cũ
   - `$newQuantity` = stock_quantity mới
   - `$quantityDifference` = new - old

4. **Cập nhật Product:**
   - name, description, price, category_id, image_url, stock_quantity

5. **Cập nhật Inventory:**
   - `firstOrCreate` inventory
   - Nếu tăng (difference > 0):
     - stock_in += difference
     - current_stock += difference
   - Nếu giảm (difference < 0):
     - stock_out += |difference|
     - current_stock += difference (trừ)

6. **Cập nhật ProductDetail:**
   - Nếu có chi tiết → update hoặc create
   - Nếu không có → xóa ProductDetail

**Return:** `RedirectResponse`

---

#### `destroy($id)`
**Chức năng:** Xóa sản phẩm

**Parameters:**
- `$id`: ID của product

**Hoạt động:**
1. Tìm product
2. Xóa file ảnh (nếu là file upload)
3. Xóa Inventory liên quan
4. Xóa ProductDetail liên quan
5. Xóa Product
6. Redirect về danh sách

**Lưu ý:** Cần xử lý ràng buộc với orders

**Return:** `RedirectResponse`

---

## InventoryController

### Mục đích
Controller quản lý tồn kho cho admin: xem, sửa, nhập/xuất kho.

### Các phương thức

#### `index(Request $request)`
**Chức năng:** Hiển thị danh sách tồn kho

**Parameters:**
- `$request`: Chứa search, stock_status, sort_by, sort_order

**Hoạt động:**
1. **Query inventory:**
   - Eager load: product.category

2. **Tìm kiếm:**
   - Theo tên product với `whereHas`

3. **Lọc theo trạng thái:**
   - `low`: current_stock < 10
   - `out`: current_stock = 0
   - `available`: current_stock >= 10

4. **Sắp xếp:**
   - Mặc định: updated_at desc
   - Có thể sort theo: stock_in, stock_out, current_stock

5. **Phân trang:** 15 bản ghi/trang

**Dữ liệu trả về:**
- `paginatedInventory`: Inventory trên trang hiện tại
- `pagination`: Thông tin phân trang
- `search`, `stock_status`, `sort_by`, `sort_order`: Giữ lại filter

**Return:** `View`

---

#### `show($id)`
**Chức năng:** Hiển thị chi tiết tồn kho

**Parameters:**
- `$id`: ID của inventory

**Hoạt động:**
- Tìm inventory với product.category
- Trả về view chi tiết

**Return:** `View`

---

#### `edit($id)`
**Chức năng:** Hiển thị form chỉnh sửa

**Return:** `View`

---

#### `update(Request $request, $id)`
**Chức năng:** Cập nhật thông tin tồn kho

**Parameters:**
- `$request`: Chứa stock_in, stock_out, current_stock
- `$id`: ID của inventory

**Hoạt động:**
1. **Validate:**
   - Tất cả required, integer, min 0

2. **Cập nhật:**
   - Update các giá trị mới
   - Redirect về chi tiết

**Return:** `RedirectResponse`

---

#### `adjustStock(Request $request, $id)`
**Chức năng:** Điều chỉnh tồn kho (nhập/xuất)

**Parameters:**
- `$request`: Chứa adjustment_type, quantity, note
- `$id`: ID của inventory

**Hoạt động:**
1. **Validate:**
   - `adjustment_type`: required, in:in,out
   - `quantity`: required, integer, min 1
   - `note`: nullable, max 500

2. **Nếu nhập kho (in):**
   - stock_in += quantity
   - current_stock += quantity

3. **Nếu xuất kho (out):**
   - Kiểm tra current_stock >= quantity
   - stock_out += quantity
   - current_stock -= quantity

4. **Lưu và redirect**

**Return:** `RedirectResponse`

---

## CouponController

### Mục đích
Controller quản lý mã giảm giá: tạo, sửa, xóa, kích hoạt coupon và tự động áp dụng giảm giá cho sản phẩm.

### Các phương thức

#### `index(Request $request)`
**Chức năng:** Hiển thị danh sách coupon

**Parameters:**
- `$request`: Chứa search

**Hoạt động:**
1. Query coupons với eager load product
2. **Tìm kiếm:** Theo code (LIKE)
3. **Sắp xếp:** created_at desc
4. **Phân trang:** 15 coupon/trang

**Return:** `View`

---

#### `create(Request $request)`
**Chức năng:** Hiển thị form tạo coupon

**Parameters:**
- `$request`: Có thể chứa product_id để pre-select

**Hoạt động:**
- Lấy danh sách products (sắp xếp theo name)
- Trả về view với products và selectedProductId

**Return:** `View`

---

#### `store(Request $request)`
**Chức năng:** Lưu coupon mới

**Parameters:**
- `$request`: Chứa thông tin coupon

**Hoạt động:**
1. **Validate:**
   - `code`: required, max 50, unique
   - `discount_type`: required, in:percentage,fixed
   - `discount_value`: required, numeric, min 0
   - `product_id`: nullable, exists in products
   - `start_date`: required, date
   - `end_date`: required, date, after:start_date
   - `is_active`: boolean

2. **Validate bổ sung:**
   - Nếu discount_type = percentage và value > 100 → lỗi

3. **Tạo Coupon:**
   - code = strtoupper($request->code) (chuyển chữ hoa)
   - Lưu các trường

4. **Áp dụng giảm giá (nếu active và có product):**
   - Gọi `applyDiscountToProduct($coupon)`

**Return:** `RedirectResponse`

---

#### `show($id)`
**Chức năng:** Hiển thị chi tiết coupon

**Return:** `View`

---

#### `edit($id)`
**Chức năng:** Hiển thị form chỉnh sửa

**Return:** `View`

---

#### `update(Request $request, $id)`
**Chức năng:** Cập nhật coupon

**Parameters:**
- `$request`: Thông tin cập nhật
- `$id`: ID của coupon

**Hoạt động:**
1. **Validate** (tương tự store, unique ngoại trừ ID hiện tại)

2. **Lưu thông tin cũ:**
   - oldProductId
   - oldIsActive

3. **Khôi phục giá gốc (nếu cần):**
   - Nếu coupon cũ active và có product → `restoreProductPrice($oldProductId)`

4. **Cập nhật Coupon**

5. **Áp dụng giảm giá mới (nếu cần):**
   - Nếu coupon mới active và có product → `applyDiscountToProduct($coupon)`

**Return:** `RedirectResponse`

---

#### `destroy($id)`
**Chức năng:** Xóa coupon

**Hoạt động:**
1. Tìm coupon
2. **Khôi phục giá (nếu cần):**
   - Nếu active và có product → `restoreProductPrice()`
3. Xóa coupon
4. Redirect

**Return:** `RedirectResponse`

---

#### `toggleStatus($id)`
**Chức năng:** Bật/tắt trạng thái coupon

**Hoạt động:**
1. Tìm coupon
2. **Nếu đang active:**
   - Khôi phục giá gốc
3. **Toggle:** is_active = !is_active
4. **Nếu mới active:**
   - Áp dụng giảm giá
5. Redirect với thông báo

**Return:** `RedirectResponse`

---

#### `applyDiscountToProduct($coupon)` (private)
**Chức năng:** Áp dụng giảm giá cho sản phẩm

**Parameters:**
- `$coupon`: Coupon cần áp dụng

**Hoạt động:**
1. Kiểm tra product_id
2. Tìm product
3. **Lưu giá gốc (nếu chưa có):**
   - original_price = price

4. **Tính giá sau giảm:**
   - discountedPrice = original_price - `$coupon->calculateDiscount(original_price)`

5. **Cập nhật:**
   - price = max(0, discountedPrice)
   - Lưu product

---

#### `restoreProductPrice($productId)` (private)
**Chức năng:** Khôi phục giá gốc cho sản phẩm

**Parameters:**
- `$productId`: ID của product

**Hoạt động:**
1. Tìm product
2. Kiểm tra original_price != null
3. **Khôi phục:**
   - price = original_price
   - original_price = null
4. Lưu product

---

## UserManagementController

### Mục đích
Controller quản lý người dùng và phân quyền cho admin.

### Các phương thức

#### `index(Request $request)`
**Chức năng:** Hiển thị danh sách người dùng

**Parameters:**
- `$request`: Chứa search

**Hoạt động:**
1. Query users với eager load roles
2. **Tìm kiếm:** Theo name hoặc email (LIKE)
3. **Sắp xếp:** created_at desc
4. **Phân trang:** 15 users/trang

**Return:** `View`

---

#### `show(User $user)`
**Chức năng:** Hiển thị chi tiết người dùng

**Parameters:**
- `$user`: Instance của User (route model binding)

**Hoạt động:**
- Eager load: roles, orders
- Trả về view với user

**Return:** `View`

---

#### `edit(User $user)`
**Chức năng:** Hiển thị form chỉnh sửa quyền

**Hoạt động:**
- Load roles của user
- Lấy tất cả roles
- Trả về view form

**Return:** `View`

---

#### `update(Request $request, User $user)`
**Chức năng:** Cập nhật quyền của người dùng

**Parameters:**
- `$request`: Chứa mảng roles (role_ids)
- `$user`: Instance của User

**Hoạt động:**
1. **Validate:**
   - `roles`: array
   - `roles.*`: exists in roles

2. **Sử dụng transaction:**
   - Xóa tất cả UserRole cũ
   - Thêm roles mới với assigned_at = now()
   - Commit

3. **Redirect với thông báo**

**Return:** `RedirectResponse`

---

#### `assignRole(Request $request, User $user)`
**Chức năng:** Gán một role cho user

**Parameters:**
- `$request`: Chứa role_id
- `$user`: Instance của User

**Hoạt động:**
1. **Validate:** role_id exists
2. **Kiểm tra đã có role chưa**
3. **Tạo UserRole:** với assigned_at = now()
4. **Redirect**

**Return:** `RedirectResponse`

---

#### `removeRole(User $user, Role $role)`
**Chức năng:** Gỡ bỏ role khỏi user

**Parameters:**
- `$user`: Instance của User
- `$role`: Instance của Role

**Hoạt động:**
1. Tìm UserRole
2. Kiểm tra tồn tại
3. Xóa UserRole
4. Redirect

**Return:** `RedirectResponse`

---

#### `destroy(User $user)`
**Chức năng:** Xóa người dùng

**Parameters:**
- `$user`: Instance của User

**Hoạt động:**
1. **Kiểm tra:**
   - Không cho phép xóa chính mình
2. **Sử dụng transaction:**
   - Xóa UserRoles
   - Xóa User
   - Commit
3. **Redirect**

**Lưu ý:** Cần xử lý dữ liệu liên quan (orders, cart)

**Return:** `RedirectResponse`

---

#### `roles()`
**Chức năng:** Hiển thị danh sách roles

**Hoạt động:**
- Lấy roles với `withCount('users')`
- Trả về view

**Return:** `View`

---

#### `permissions()`
**Chức năng:** Hiển thị thống kê phân quyền

**Hoạt động:**
1. **Lấy permissions của user hiện tại:**
   - `$currentUser->getAllPermissions()`

2. **Thống kê:**
   - total_users
   - admin_count (whereHas role admin)
   - manager_count
   - user_count

3. **Trả về view**

**Return:** `View`

---

## ReportController

### Mục đích
Controller tạo các báo cáo thống kê cho admin: doanh thu, sản phẩm, khách hàng.

### Các phương thức

#### `index()`
**Chức năng:** Hiển thị trang báo cáo tổng quan

**Hoạt động:**
1. **Thống kê tổng quan:**
   - totalRevenue: Tổng doanh thu (loại trừ cancelled)
   - totalOrders: Tổng đơn hàng
   - totalCustomers: Đếm user có role 'customer'
   - totalProducts: Tổng sản phẩm

2. **Doanh thu theo tháng:**
   - Gọi `getMonthlyRevenue()` → 12 tháng gần nhất

3. **Top sản phẩm:**
   - Gọi `getTopSellingProducts(10)`

4. **Đơn hàng theo trạng thái:**
   - Group by status, đếm số lượng

5. **Doanh thu hôm nay:**
   - `whereDate('order_date', Carbon::today())`

6. **Doanh thu tháng này:**
   - `whereMonth()` và `whereYear()`

**Return:** `View`

---

#### `revenue(Request $request)`
**Chức năng:** Báo cáo doanh thu chi tiết

**Parameters:**
- `$request`: Chứa start_date, end_date, group_by

**Hoạt động:**
1. **Lấy tham số:**
   - start_date (mặc định: đầu tháng)
   - end_date (mặc định: cuối tháng)
   - group_by (day/week/month)

2. **Doanh thu theo khoảng:**
   - Gọi `getRevenueByPeriod()`

3. **Tính toán:**
   - totalRevenue
   - totalOrders
   - averageOrderValue = totalRevenue / totalOrders

**Return:** `View`

---

#### `products(Request $request)`
**Chức năng:** Báo cáo sản phẩm

**Parameters:**
- `$request`: Chứa start_date, end_date

**Hoạt động:**
1. **Top sản phẩm:** Gọi `getTopSellingProducts(20, ...)`

2. **Sản phẩm theo danh mục:**
   - Join: order_items, products, categories, orders
   - Group by category
   - Sum quantity và revenue
   - Order by revenue desc

**Return:** `View`

---

#### `customers(Request $request)`
**Chức năng:** Báo cáo khách hàng

**Parameters:**
- `$request`: Chứa start_date, end_date

**Hoạt động:**
1. **Top khách hàng:**
   - Join orders với users
   - Group by user
   - Sum total_amount, count orders
   - Order by total_spent desc
   - Limit 20

2. **Khách hàng mới:**
   - Đếm customer trong khoảng thời gian

3. **Khách hàng có đơn:**
   - Distinct user_id trong khoảng thời gian

**Return:** `View`

---

#### `getMonthlyRevenue()` (private)
**Chức năng:** Lấy doanh thu 12 tháng gần nhất

**Hoạt động:**
- Select YEAR, MONTH, SUM(total_amount)
- Where: >= 11 tháng trước
- Group by year, month
- Map: Tạo period label (format: M Y)

**Return:** `Collection`

---

#### `getTopSellingProducts($limit, $startDate, $endDate)` (private)
**Chức năng:** Lấy top sản phẩm bán chạy

**Parameters:**
- `$limit`: Số lượng sản phẩm
- `$startDate`, `$endDate`: Khoảng thời gian (nullable)

**Hoạt động:**
- Join: order_items, products, orders
- Where: status != cancelled
- Group by product
- Order by total_sold desc

**Return:** `Collection`

---

#### `getRevenueByPeriod($startDate, $endDate, $groupBy)` (private)
**Chức năng:** Lấy doanh thu theo khoảng thời gian

**Parameters:**
- `$groupBy`: day/week/month

**Hoạt động:**
- Tùy group_by → format DATE khác nhau
- Select period, SUM(revenue), COUNT(orders)
- Group by period
- Order by period

**Return:** `Collection`

---

#### `export(Request $request)`
**Chức năng:** Export báo cáo ra CSV

**Parameters:**
- `$request`: Chứa type (revenue/products/customers), start_date, end_date

**Hoạt động:**
- Switch theo type → gọi hàm export tương ứng

**Return:** `StreamedResponse`

---

#### `exportRevenue($startDate, $endDate)` (private)
**Chức năng:** Export báo cáo doanh thu

**Hoạt động:**
- Lấy dữ liệu: `getRevenueByPeriod()`
- Tạo CSV stream với headers: Ngày, Doanh thu, Số đơn
- Return download response

---

#### `exportProducts($startDate, $endDate)` (private)
**Chức năng:** Export báo cáo sản phẩm

**Hoạt động:**
- Lấy top 100 sản phẩm
- CSV headers: Tên sản phẩm, Đã bán, Doanh thu

---

#### `exportCustomers($startDate, $endDate)` (private)
**Chức năng:** Export báo cáo khách hàng

**Hoạt động:**
- Lấy dữ liệu khách hàng
- CSV headers: Tên, Email, Số đơn, Tổng chi tiêu

---

## SocialAuthController

### Mục đích
Controller xử lý đăng nhập qua các nền tảng mạng xã hội (Google, Facebook, GitHub).

### Các phương thức

#### `redirect($provider)`
**Chức năng:** Chuyển hướng đến provider để đăng nhập

**Parameters:**
- `$provider`: Tên provider (google/facebook/github)

**Hoạt động:**
1. **Validate provider:**
   - Chỉ chấp nhận: google, facebook, github
   - Nếu không hợp lệ → redirect về login với lỗi

2. **Redirect đến provider:**
   - Sử dụng `Socialite::driver($provider)->redirect()`

**Return:** `RedirectResponse`

---

#### `callback($provider)`
**Chức năng:** Xử lý callback từ provider sau khi đăng nhập

**Parameters:**
- `$provider`: Tên provider

**Hoạt động:**
1. **Validate provider**

2. **Lấy thông tin user từ provider:**
   - `Socialite::driver($provider)->user()`

3. **Tìm hoặc tạo user:**
   - Gọi `findOrCreateUser($socialUser, $provider)`

4. **Đăng nhập:**
   - `Auth::login($user)`

5. **Redirect theo role:**
   - Admin/Manager → dashboard
   - Customer → products
   - Other → home

**Return:** `RedirectResponse`

---

#### `findOrCreateUser($socialUser, $provider)` (private)
**Chức năng:** Tìm hoặc tạo user từ thông tin social

**Parameters:**
- `$socialUser`: Thông tin từ provider
- `$provider`: Tên provider

**Hoạt động:**
1. **Tìm user theo provider và provider_id:**
   - Nếu tìm thấy → cập nhật avatar và return

2. **Kiểm tra email đã tồn tại:**
   - Nếu có → cập nhật provider, provider_id, avatar và return

3. **Tạo user mới:**
   - Sử dụng transaction
   - Tạo User với:
     - name = getName() || getNickname() || 'User'
     - email = getEmail()
     - provider, provider_id
     - avatar = getAvatar()
     - password = null (không cần cho social login)
   
   - Tự động gán role 'customer':
     - Tạo UserRole với assigned_at = now()
   
   - Commit transaction

**Return:** `User`

---

## PasswordResetController

### Mục đích
Controller xử lý quên mật khẩu và đặt lại mật khẩu qua email.

### Các phương thức

#### `showForgotForm()`
**Chức năng:** Hiển thị form yêu cầu reset password

**Return:** `View`

---

#### `sendResetLink(Request $request)`
**Chức năng:** Xử lý gửi email reset password

**Parameters:**
- `$request`: Chứa email

**Hoạt động:**
1. **Validate:**
   - `email`: required, email, exists in users

2. **Tạo token:**
   - `Str::random(64)` → token ngẫu nhiên

3. **Lưu token vào database:**
   - Table: password_reset_tokens
   - Columns: email, token (hashed), created_at
   - Sử dụng `updateOrInsert()`

4. **Tạo link reset:**
   - `route('password.reset', ['token' => $token, 'email' => $email])`

5. **Gửi email:**
   - Template: emails.reset-password
   - Subject: Yêu cầu đặt lại mật khẩu
   - Try-catch để xử lý lỗi

**Return:** `RedirectResponse`

---

#### `showResetForm(Request $request, $token)`
**Chức năng:** Hiển thị form reset password

**Parameters:**
- `$request`: Chứa email
- `$token`: Token từ link email

**Hoạt động:**
- Trả về view với token và email

**Return:** `View`

---

#### `resetPassword(Request $request)`
**Chức năng:** Xử lý đặt lại mật khẩu

**Parameters:**
- `$request`: Chứa email, password, password_confirmation, token

**Hoạt động:**
1. **Validate:**
   - `email`: required, email, exists
   - `password`: required, min 8, confirmed
   - `token`: required

2. **Kiểm tra token:**
   - Tìm trong password_reset_tokens
   - Nếu không có → lỗi

3. **Verify token:**
   - `Hash::check($request->token, $passwordReset->token)`
   - Nếu không khớp → lỗi

4. **Kiểm tra hết hạn:**
   - Token có hiệu lực 24 giờ
   - `Carbon::parse($created_at)->addHours(24)->isPast()`
   - Nếu hết hạn → lỗi

5. **Cập nhật mật khẩu:**
   - Tìm user theo email
   - `password = Hash::make($request->password)`
   - Lưu user

6. **Xóa token đã sử dụng:**
   - Delete từ password_reset_tokens

7. **Redirect về login với thông báo**

**Return:** `RedirectResponse`

---

## KẾT LUẬN

Tài liệu này cung cấp mô tả chi tiết về 14 controller trong thư mục Web, bao gồm:

- **Xác thực:** AuthController, SocialAuthController, PasswordResetController
- **Quản lý người dùng:** ProfileController, UserManagementController
- **Quản lý sản phẩm:** ProductController, CategoryController, InventoryController
- **Bán hàng:** CustomerProductController, CustomerCartController, OrderController
- **Marketing:** CouponController
- **Báo cáo:** ReportController
- **Giao diện:** HomeController

Mỗi controller đều được thiết kế với:
- Validation đầy đủ
- Error handling
- Transaction cho các thao tác quan trọng
- Eager loading để tối ưu performance
- Flash messages để thông báo cho người dùng

Hệ thống quản lý tồn kho tự động, đảm bảo tính toàn vẹn dữ liệu qua database transactions, và hỗ trợ nhiều tính năng nâng cao như mã giảm giá, đánh giá sản phẩm, và báo cáo thống kê.

