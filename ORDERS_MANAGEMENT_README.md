# Chức năng Quản lý Đơn hàng

## Tổng quan
Đã thêm chức năng quản lý đơn hàng hoàn chỉnh cho dashboard admin của WebShop.

## Các file đã tạo/cập nhật

### 1. Controller
- **File**: `app/Http/Controllers/Web/OrderController.php`
- **Chức năng**: Xử lý các thao tác CRUD cho đơn hàng
  - `index()`: Hiển thị danh sách đơn hàng với tìm kiếm và lọc
  - `show()`: Hiển thị chi tiết đơn hàng
  - `edit()`: Hiển thị form cập nhật trạng thái
  - `update()`: Cập nhật trạng thái đơn hàng
  - `destroy()`: Xóa đơn hàng (chỉ cho đơn đã hủy/đã giao)

### 2. Views
Thư mục: `resources/views/dashboard/orders/`

#### a. `index.blade.php` - Danh sách đơn hàng
- Hiển thị bảng danh sách tất cả đơn hàng
- Tìm kiếm theo mã đơn, tên, email khách hàng
- Lọc theo trạng thái đơn hàng
- Phân trang
- Các nút thao tác: Xem, Sửa, Xóa

#### b. `show.blade.php` - Chi tiết đơn hàng
- Thông tin đơn hàng (mã, ngày, trạng thái, tổng tiền)
- Danh sách sản phẩm trong đơn
- Thông tin khách hàng
- Các hành động chuyển trạng thái nhanh

#### c. `edit.blade.php` - Cập nhật đơn hàng
- Form cập nhật trạng thái
- Chỉ hiển thị các trạng thái hợp lệ có thể chuyển đổi
- Hướng dẫn quy trình xử lý đơn hàng
- Tóm tắt thông tin đơn hàng

### 3. Routes
**File**: `routes/web.php`

Đã thêm các routes sau:
```php
Route::get('/dashboard/orders', [OrderController::class, 'index'])
    ->name('dashboard.orders.index');
Route::get('/dashboard/orders/{id}', [OrderController::class, 'show'])
    ->name('dashboard.orders.show');
Route::get('/dashboard/orders/{id}/edit', [OrderController::class, 'edit'])
    ->name('dashboard.orders.edit');
Route::put('/dashboard/orders/{id}', [OrderController::class, 'update'])
    ->name('dashboard.orders.update');
Route::delete('/dashboard/orders/{id}', [OrderController::class, 'destroy'])
    ->name('dashboard.orders.destroy')->middleware('role:admin');
```

### 4. Navigation
Đã cập nhật tất cả các file view trong dashboard để link đến trang quản lý đơn hàng:
- `resources/views/dashboard/index.blade.php`
- `resources/views/dashboard/products/*.blade.php`
- `resources/views/dashboard/categories/*.blade.php`
- `resources/views/dashboard/orders/*.blade.php`

## Tính năng chính

### 1. Quản lý trạng thái đơn hàng
Hệ thống hỗ trợ 5 trạng thái:
- **Chờ xử lý** (pending)
- **Đang xử lý** (processing)
- **Đã gửi hàng** (shipped)
- **Đã giao hàng** (delivered)
- **Đã hủy** (cancelled)

### 2. Workflow chuyển trạng thái
Quy trình được quản lý chặt chẽ:
- Chờ xử lý → Đang xử lý hoặc Đã hủy
- Đang xử lý → Đã gửi hàng hoặc Đã hủy
- Đã gửi hàng → Đã giao hàng
- Đã giao hàng/Đã hủy → Không thể thay đổi (trạng thái cuối)

### 3. Tìm kiếm và lọc
- Tìm kiếm theo mã đơn hàng, tên khách hàng, email
- Lọc theo trạng thái đơn hàng
- Phân trang với 15 đơn hàng mỗi trang

### 4. Bảo mật
- Middleware `auth` và `role:admin,manager` bảo vệ tất cả routes
- Chỉ admin mới có quyền xóa đơn hàng
- Kiểm tra workflow trước khi chuyển trạng thái

## Cách sử dụng

### Truy cập quản lý đơn hàng:
1. Đăng nhập vào dashboard với tài khoản admin/manager
2. Click vào "Đơn hàng" trong sidebar menu
3. Xem danh sách tất cả đơn hàng

### Xem chi tiết đơn hàng:
1. Click vào nút "Xem" (icon mắt) trong danh sách
2. Xem thông tin chi tiết đơn hàng và khách hàng
3. Có thể chuyển trạng thái nhanh từ sidebar

### Cập nhật trạng thái:
1. Click vào nút "Sửa" (icon bút) trong danh sách
2. Hoặc click "Cập nhật trạng thái" trong trang chi tiết
3. Chọn trạng thái mới (chỉ hiển thị các trạng thái hợp lệ)
4. Click "Cập nhật"

### Xóa đơn hàng:
1. Chỉ có thể xóa đơn đã hủy hoặc đã giao
2. Click vào nút "Xóa" (icon thùng rác)
3. Xác nhận xóa trong modal

## Kiểm tra

Để kiểm tra chức năng, bạn cần:
1. Đảm bảo database đã có dữ liệu mẫu về orders
2. Đăng nhập với tài khoản admin/manager
3. Truy cập `/dashboard/orders`

## Lưu ý
- Model `Order` đã có sẵn các phương thức `canTransitionTo()` và `transitionTo()` để quản lý workflow
- Relationship sử dụng `items()` thay vì `orderItems()` theo chuẩn của model
- Tất cả các view đều có responsive design và icon Font Awesome
