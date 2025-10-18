# Tính năng Tự động Tạo Inventory (Tồn kho)

## 📋 Tổng quan

Khi tạo hoặc cập nhật sản phẩm, hệ thống sẽ **tự động quản lý bản ghi inventory** (tồn kho) tương ứng.

## ✨ Các tính năng đã triển khai

### 1. Tự động tạo Inventory khi tạo sản phẩm mới

**Khi tạo sản phẩm mới:**
```
Nhập thông tin sản phẩm:
- Tên: Laptop Dell XPS 15
- Giá: 25,000,000 VNĐ
- Danh mục: Laptop
- Số lượng: 50
    ↓
Hệ thống tự động tạo:
    ↓
Product (sản phẩm)
├─ product_id: 123
├─ name: "Laptop Dell XPS 15"
├─ price: 25000000
├─ stock_quantity: 50
└─ ...

Inventory (tồn kho)
├─ inventory_id: auto
├─ product_id: 123
├─ stock_in: 50        (Nhập kho)
├─ stock_out: 0        (Xuất kho)
└─ current_stock: 50   (Tồn kho hiện tại)
```

### 2. Tự động cập nhật Inventory khi cập nhật sản phẩm

**Khi tăng số lượng sản phẩm:**
```
Sản phẩm cũ:
- stock_quantity: 50

Cập nhật:
- stock_quantity: 70 (+20)
    ↓
Inventory được cập nhật:
- stock_in: 50 → 70 (+20)
- current_stock: 50 → 70 (+20)
```

**Khi giảm số lượng sản phẩm:**
```
Sản phẩm cũ:
- stock_quantity: 70

Cập nhật:
- stock_quantity: 60 (-10)
    ↓
Inventory được cập nhật:
- stock_out: 0 → 10 (+10)
- current_stock: 70 → 60 (-10)
```

### 3. Tự động xóa Inventory khi xóa sản phẩm

**Khi xóa sản phẩm:**
```
Xóa Product → Tự động xóa Inventory liên quan
```

## 🔧 Cách hoạt động (Technical Details)

### ProductController - store() method

```php
public function store(Request $request)
{
    // 1. Tạo sản phẩm
    $product = Product::create([...]);

    // 2. Tự động tạo inventory
    Inventory::create([
        'product_id' => $product->product_id,
        'stock_in' => $request->stock_quantity,
        'stock_out' => 0,
        'current_stock' => $request->stock_quantity,
    ]);

    return redirect()->with('success', 'Sản phẩm và tồn kho đã được tạo!');
}
```

### ProductController - update() method

```php
public function update(Request $request, $id)
{
    // 1. Tính toán sự thay đổi
    $oldQuantity = $product->stock_quantity;
    $newQuantity = $request->stock_quantity;
    $quantityDifference = $newQuantity - $oldQuantity;

    // 2. Cập nhật sản phẩm
    $product->update([...]);

    // 3. Tạo hoặc tìm inventory
    $inventory = Inventory::firstOrCreate(
        ['product_id' => $product->product_id],
        ['stock_in' => 0, 'stock_out' => 0, 'current_stock' => 0]
    );

    // 4. Điều chỉnh inventory
    if ($quantityDifference > 0) {
        // Tăng = Nhập kho thêm
        $inventory->stock_in += $quantityDifference;
        $inventory->current_stock += $quantityDifference;
    } elseif ($quantityDifference < 0) {
        // Giảm = Xuất kho
        $inventory->stock_out += abs($quantityDifference);
        $inventory->current_stock += $quantityDifference;
    }

    $inventory->save();
}
```

### ProductController - destroy() method

```php
public function destroy($id)
{
    $product = Product::findOrFail($id);
    
    // Xóa inventory trước
    Inventory::where('product_id', $product->product_id)->delete();
    
    // Xóa product
    $product->delete();
}
```

## 📊 Luồng dữ liệu

```
┌─────────────────────────────────────────────────────────┐
│           USER ACTION (Tạo/Sửa/Xóa Sản phẩm)            │
└───────────────────────┬─────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│              ProductController                          │
│  • Validate input                                       │
│  • Calculate quantity changes                           │
└───────────────────────┬─────────────────────────────────┘
                        │
        ┌───────────────┼───────────────┐
        │               │               │
        ▼               ▼               ▼
   ┌─────────┐   ┌─────────────┐   ┌────────┐
   │ CREATE  │   │   UPDATE    │   │ DELETE │
   └────┬────┘   └──────┬──────┘   └───┬────┘
        │               │              │
        │               │              │
        ▼               ▼              ▼
┌────────────────────────────────────────────┐
│         DATABASE (products table)          │
└────────────────┬───────────────────────────┘
                 │
                 ▼
┌────────────────────────────────────────────┐
│       DATABASE (inventory table)           │
│  • Auto create on product create           │
│  • Auto update on product update           │
│  • Auto delete on product delete           │
└────────────────────────────────────────────┘
```

## 🎯 Lợi ích

### 1. **Tự động hóa hoàn toàn**
- ✅ Không cần tạo inventory thủ công
- ✅ Đảm bảo mọi sản phẩm đều có bản ghi inventory
- ✅ Giảm thiểu lỗi do quên tạo inventory

### 2. **Đồng bộ dữ liệu**
- ✅ `product.stock_quantity` luôn đồng bộ với `inventory.current_stock`
- ✅ Lịch sử nhập/xuất kho được ghi lại tự động
- ✅ Dữ liệu nhất quán giữa 2 bảng

### 3. **Dễ quản lý**
- ✅ Admin chỉ cần nhập số lượng ở form sản phẩm
- ✅ Hệ thống tự động xử lý inventory
- ✅ Không cần qua trang inventory riêng để tạo mới

## 📝 Ví dụ sử dụng

### Ví dụ 1: Tạo sản phẩm mới

1. Truy cập: `/dashboard/products/create`
2. Nhập thông tin:
   ```
   Tên sản phẩm: iPhone 15 Pro Max
   Giá: 32,000,000 VNĐ
   Danh mục: Điện thoại
   Số lượng tồn kho: 100
   ```
3. Click "Lưu"

**Kết quả:**
- ✅ Sản phẩm được tạo với `stock_quantity = 100`
- ✅ Inventory được tạo tự động:
  - `stock_in = 100`
  - `stock_out = 0`
  - `current_stock = 100`

### Ví dụ 2: Cập nhật số lượng sản phẩm

**Trường hợp A: Nhập thêm hàng (tăng số lượng)**

1. Sản phẩm hiện tại: 100 cái
2. Cập nhật thành: 150 cái (+50)

**Kết quả:**
```
Inventory trước:
- stock_in: 100
- stock_out: 0
- current_stock: 100

Inventory sau:
- stock_in: 150 (+50)
- stock_out: 0
- current_stock: 150 (+50)
```

**Trường hợp B: Bán hàng (giảm số lượng)**

1. Sản phẩm hiện tại: 150 cái
2. Cập nhật thành: 120 cái (-30)

**Kết quả:**
```
Inventory trước:
- stock_in: 150
- stock_out: 0
- current_stock: 150

Inventory sau:
- stock_in: 150
- stock_out: 30 (+30)
- current_stock: 120 (-30)
```

### Ví dụ 3: Xóa sản phẩm

1. Chọn sản phẩm cần xóa
2. Click "Xóa"

**Kết quả:**
- ✅ Sản phẩm bị xóa
- ✅ Inventory liên quan bị xóa tự động

## ⚙️ Configuration

### Model Requirements

**Product Model** phải có:
```php
protected $fillable = [
    'name',
    'price',
    'category_id',
    'stock_quantity', // Required!
    'image_url',
    // ...
];
```

**Inventory Model** phải có:
```php
protected $fillable = [
    'product_id',
    'stock_in',
    'stock_out',
    'current_stock',
];

public function product()
{
    return $this->belongsTo(Product::class, 'product_id', 'product_id');
}
```

### Database Schema

**products table:**
```sql
CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    price DECIMAL(10,2),
    category_id INT,
    stock_quantity INT DEFAULT 0,
    ...
);
```

**inventory table:**
```sql
CREATE TABLE inventory (
    inventory_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    stock_in INT DEFAULT 0,
    stock_out INT DEFAULT 0,
    current_stock INT DEFAULT 0,
    updated_at TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);
```

## 🔍 Kiểm tra và Debug

### Kiểm tra inventory đã được tạo

```php
// Trong tinker hoặc controller
$product = Product::find(1);
$inventory = Inventory::where('product_id', $product->product_id)->first();

dd([
    'product_stock' => $product->stock_quantity,
    'inventory_stock' => $inventory->current_stock,
    'stock_in' => $inventory->stock_in,
    'stock_out' => $inventory->stock_out,
]);
```

### Kiểm tra đồng bộ

```php
// Kiểm tra tất cả sản phẩm có inventory
$productsWithoutInventory = Product::whereDoesntHave('inventory')->get();

if ($productsWithoutInventory->count() > 0) {
    echo "Có {$productsWithoutInventory->count()} sản phẩm chưa có inventory!";
}
```

## 🚨 Lưu ý quan trọng

### 1. Migration cần thiết

Đảm bảo bảng `inventory` đã được migrate:
```bash
php artisan migrate
```

### 2. Existing Products

Nếu có sản phẩm cũ chưa có inventory, cần chạy script để tạo:
```php
// Script tạo inventory cho sản phẩm cũ
$products = Product::all();

foreach ($products as $product) {
    Inventory::firstOrCreate(
        ['product_id' => $product->product_id],
        [
            'stock_in' => $product->stock_quantity,
            'stock_out' => 0,
            'current_stock' => $product->stock_quantity,
        ]
    );
}
```

### 3. Foreign Key Constraints

Nếu có foreign key, cần xóa theo thứ tự:
```php
// 1. Xóa inventory trước
Inventory::where('product_id', $productId)->delete();

// 2. Xóa product sau
Product::destroy($productId);
```

## 📈 Future Improvements

### Có thể thêm trong tương lai:

1. **Event & Listener**
   ```php
   // ProductCreated Event
   event(new ProductCreated($product));
   
   // ProductCreatedListener
   public function handle(ProductCreated $event)
   {
       Inventory::create([...]);
   }
   ```

2. **Observer Pattern**
   ```php
   // ProductObserver
   public function created(Product $product)
   {
       Inventory::create([...]);
   }
   ```

3. **Transaction Safety**
   ```php
   DB::transaction(function () use ($request) {
       $product = Product::create([...]);
       Inventory::create([...]);
   });
   ```

4. **Logging**
   ```php
   Log::info('Inventory auto-created', [
       'product_id' => $product->product_id,
       'quantity' => $request->stock_quantity,
   ]);
   ```

## 📚 Related Files

- `app/Http/Controllers/Web/ProductController.php` - Main controller
- `app/Models/Product.php` - Product model
- `app/Models/Inventory.php` - Inventory model
- `resources/views/dashboard/products/create.blade.php` - Create form
- `resources/views/dashboard/products/edit.blade.php` - Edit form
- `database/migrations/xxx_create_inventory_table.php` - Migration

---

**Version**: 1.0.0  
**Last Updated**: 18/10/2025  
**Author**: Hoàng Quang Vinh  
**Project**: WebShop E-commerce Platform
