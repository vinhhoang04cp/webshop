<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        // Dữ liệu điện thoại thực tế với giá cả và thông tin chi tiết
        $products = [
            // iPhone
            ['name' => 'iPhone 15 Pro Max 256GB', 'description' => 'iPhone 15 Pro Max với chip A17 Pro, camera 48MP, titanium cao cấp', 'price' => 34990000, 'category' => 'iPhone', 'stock' => 50, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/305658/iphone-15-pro-max-blue-thumbnew-600x600.jpg'],
            ['name' => 'iPhone 15 Pro 128GB', 'description' => 'iPhone 15 Pro với khung viền titanium, Dynamic Island, camera 48MP Pro', 'price' => 28990000, 'category' => 'iPhone', 'stock' => 45, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/305660/iphone-15-pro-natural-thumbnew-600x600.jpg'],
            ['name' => 'iPhone 15 Plus 128GB', 'description' => 'iPhone 15 Plus màn hình lớn 6.7 inch, Dynamic Island, camera chính 48MP', 'price' => 25990000, 'category' => 'iPhone', 'stock' => 60, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/305657/iphone-15-plus-128gb-xanh-thumb-600x600.jpg'],
            ['name' => 'iPhone 15 128GB', 'description' => 'iPhone 15 với màn hình Dynamic Island, chip A16 Bionic mạnh mẽ', 'price' => 22990000, 'category' => 'iPhone', 'stock' => 70, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/303891/iphone-15-green-thumbnew-600x600.jpg'],
            ['name' => 'iPhone 14 Pro Max 256GB', 'description' => 'iPhone 14 Pro Max camera 48MP, màn hình Always-On, chip A16 Bionic', 'price' => 31990000, 'category' => 'iPhone', 'stock' => 35, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/289700/iphone-14-pro-max-purple-thumb-600x600.jpg'],
            ['name' => 'iPhone 14 128GB', 'description' => 'iPhone 14 thiết kế sang trọng, camera kép 12MP, pin trâu', 'price' => 19990000, 'category' => 'iPhone', 'stock' => 55, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/289659/iphone-14-thumb-tim-1-600x600.jpg'],
            ['name' => 'iPhone 13 128GB', 'description' => 'iPhone 13 giá tốt, camera kép 12MP, hiệu năng A15 Bionic mạnh mẽ', 'price' => 16990000, 'category' => 'iPhone', 'stock' => 80, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/223602/iphone-13-blue-600x600.jpg'],

            // Samsung
            ['name' => 'Samsung Galaxy S24 Ultra 256GB', 'description' => 'Galaxy S24 Ultra với bút S Pen, camera 200MP, chip Snapdragon 8 Gen 3', 'price' => 29990000, 'category' => 'Samsung', 'stock' => 40, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/319541/samsung-galaxy-s24-ultra-den-thumbnew-600x600.jpg'],
            ['name' => 'Samsung Galaxy S24+ 256GB', 'description' => 'Galaxy S24+ màn hình 6.7 inch QHD+, camera AI siêu nét', 'price' => 24990000, 'category' => 'Samsung', 'stock' => 45, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/319540/samsung-galaxy-s24-plus-tim-thumbnew-600x600.jpg'],
            ['name' => 'Samsung Galaxy S24 128GB', 'description' => 'Galaxy S24 nhỏ gọn, hiệu năng mạnh mẽ, camera AI thông minh', 'price' => 19990000, 'category' => 'Samsung', 'stock' => 60, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/307174/samsung-galaxy-s24-xam-thumbnew-600x600.jpg'],
            ['name' => 'Samsung Galaxy Z Fold5 256GB', 'description' => 'Điện thoại gập cao cấp, màn hình 7.6 inch, bút S Pen', 'price' => 40990000, 'category' => 'Samsung', 'stock' => 20, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/305658/samsung-galaxy-z-fold5-kem-thumbnew-600x600.jpg'],
            ['name' => 'Samsung Galaxy Z Flip5 256GB', 'description' => 'Điện thoại gập vỏ sò, thiết kế thời trang, màn hình phụ lớn', 'price' => 23990000, 'category' => 'Samsung', 'stock' => 30, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/305658/samsung-galaxy-z-flip5-tim-thumbnew-600x600.jpg'],
            ['name' => 'Samsung Galaxy A55 5G 128GB', 'description' => 'Galaxy A55 camera 50MP OIS, pin 5000mAh, sạc nhanh 25W', 'price' => 10490000, 'category' => 'Samsung', 'stock' => 70, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/319540/samsung-galaxy-a55-5g-xanh-nhat-thumbnew-600x600.jpg'],
            ['name' => 'Samsung Galaxy A35 5G 128GB', 'description' => 'Galaxy A35 màn hình Super AMOLED 6.6 inch, pin 5000mAh', 'price' => 7690000, 'category' => 'Samsung', 'stock' => 90, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/319541/samsung-galaxy-a35-5g-xanh-duong-thumbnew-600x600.jpg'],

            // Xiaomi
            ['name' => 'Xiaomi 14 Ultra 512GB', 'description' => 'Xiaomi 14 Ultra camera Leica, chip Snapdragon 8 Gen 3, sạc 90W', 'price' => 29990000, 'category' => 'Xiaomi', 'stock' => 25, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/319540/xiaomi-14-ultra-den-thumbnew-600x600.jpg'],
            ['name' => 'Xiaomi 14 256GB', 'description' => 'Xiaomi 14 với camera Leica, màn hình LTPO AMOLED', 'price' => 19990000, 'category' => 'Xiaomi', 'stock' => 40, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/319541/xiaomi-14-xanh-la-thumbnew-600x600.jpg'],
            ['name' => 'Xiaomi Redmi Note 13 Pro+ 5G', 'description' => 'Redmi Note 13 Pro+ camera 200MP, sạc nhanh 120W', 'price' => 9990000, 'category' => 'Xiaomi', 'stock' => 70, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/314206/xiaomi-redmi-note-13-pro-plus-den-thumbnew-600x600.jpg'],
            ['name' => 'Xiaomi Redmi Note 13 Pro', 'description' => 'Redmi Note 13 Pro camera 200MP OIS, sạc 67W', 'price' => 7490000, 'category' => 'Xiaomi', 'stock' => 85, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/314205/xiaomi-redmi-note-13-pro-tim-thumbnew-600x600.jpg'],
            ['name' => 'Xiaomi Redmi 13C 128GB', 'description' => 'Redmi 13C giá rẻ, pin 5000mAh, camera 50MP', 'price' => 3290000, 'category' => 'Xiaomi', 'stock' => 120, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/313825/xiaomi-redmi-13c-xanh-la-thumbnew-600x600.jpg'],
            ['name' => 'Xiaomi POCO X6 Pro 5G', 'description' => 'POCO X6 Pro hiệu năng khủng, chip Dimensity 8300-Ultra', 'price' => 8990000, 'category' => 'Xiaomi', 'stock' => 50, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/314204/xiaomi-poco-x6-pro-xam-thumbnew-600x600.jpg'],

            // OPPO
            ['name' => 'OPPO Find X7 Ultra 512GB', 'description' => 'Find X7 Ultra camera Hasselblad, zoom quang học 6x', 'price' => 32990000, 'category' => 'OPPO', 'stock' => 20, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/319540/oppo-find-x7-ultra-den-thumbnew-600x600.jpg'],
            ['name' => 'OPPO Reno11 F 5G 256GB', 'description' => 'Reno11 F camera 64MP, sạc nhanh SUPERVOOC 67W', 'price' => 8990000, 'category' => 'OPPO', 'stock' => 60, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/314206/oppo-reno11-f-xanh-thumbnew-600x600.jpg'],
            ['name' => 'OPPO A79 5G 128GB', 'description' => 'OPPO A79 màn hình 90Hz, pin 5000mAh, sạc 33W', 'price' => 6290000, 'category' => 'OPPO', 'stock' => 75, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/313826/oppo-a79-5g-tim-thumbnew-600x600.jpg'],
            ['name' => 'OPPO A58 128GB', 'description' => 'OPPO A58 giá tốt, pin 5000mAh, màn hình 6.72 inch', 'price' => 4490000, 'category' => 'OPPO', 'stock' => 100, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/309831/oppo-a58-xanh-thumbnew-600x600.jpg'],

            // Vivo
            ['name' => 'Vivo V30 5G 256GB', 'description' => 'Vivo V30 camera 50MP OIS, sạc nhanh 80W', 'price' => 12990000, 'category' => 'Vivo', 'stock' => 45, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/319540/vivo-v30-xanh-thumbnew-600x600.jpg'],
            ['name' => 'Vivo Y100 5G 256GB', 'description' => 'Vivo Y100 màn hình AMOLED 120Hz, sạc 80W', 'price' => 7990000, 'category' => 'Vivo', 'stock' => 65, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/314206/vivo-y100-tim-thumbnew-600x600.jpg'],
            ['name' => 'Vivo Y36 128GB', 'description' => 'Vivo Y36 pin 5000mAh, sạc nhanh 44W, camera 50MP', 'price' => 5490000, 'category' => 'Vivo', 'stock' => 80, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/309108/vivo-y36-xanh-thumbnew-600x600.jpg'],

            // Realme
            ['name' => 'Realme GT 5 Pro 256GB', 'description' => 'Realme GT 5 Pro chip Snapdragon 8 Gen 3, sạc 100W', 'price' => 15990000, 'category' => 'Realme', 'stock' => 30, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/319541/realme-gt-5-pro-xam-thumbnew-600x600.jpg'],
            ['name' => 'Realme 12 Pro+ 5G 256GB', 'description' => 'Realme 12 Pro+ camera zoom quang 3x, sạc nhanh 67W', 'price' => 10490000, 'category' => 'Realme', 'stock' => 50, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/314206/realme-12-pro-plus-xanh-thumbnew-600x600.jpg'],
            ['name' => 'Realme C67 128GB', 'description' => 'Realme C67 camera 108MP, pin 5000mAh, sạc 33W', 'price' => 4990000, 'category' => 'Realme', 'stock' => 85, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/313825/realme-c67-xanh-thumbnew-600x600.jpg'],

            // Nokia
            ['name' => 'Nokia G60 5G 128GB', 'description' => 'Nokia G60 Android One, camera 50MP, pin 4500mAh', 'price' => 5990000, 'category' => 'Nokia', 'stock' => 40, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/289700/nokia-g60-5g-den-thumb-600x600.jpg'],
            ['name' => 'Nokia C32 64GB', 'description' => 'Nokia C32 giá rẻ, pin 5000mAh, màn hình lớn', 'price' => 2290000, 'category' => 'Nokia', 'stock' => 80, 'image' => 'https://cdn.tgdd.vn/Products/Images/42/306174/nokia-c32-xanh-thumb-600x600.jpg'],

            // Phụ kiện
            ['name' => 'Ốp lưng Silicone', 'description' => 'Ốp lưng chất liệu silicone mềm mại, chống sốc', 'price' => 150000, 'category' => 'Phụ kiện điện thoại', 'stock' => 200, 'image' => 'https://via.placeholder.com/600x600/00d4aa/ffffff?text=Op+Lung'],
            ['name' => 'Dán cường lực UV', 'description' => 'Dán màn hình UV full màn, độ cứng 9H', 'price' => 250000, 'category' => 'Phụ kiện điện thoại', 'stock' => 150, 'image' => 'https://via.placeholder.com/600x600/00d4aa/ffffff?text=Dan+Man+Hinh'],
            ['name' => 'Sạc dự phòng 20000mAh', 'description' => 'Sạc dự phòng sạc nhanh 22.5W, 2 cổng USB', 'price' => 799000, 'category' => 'Phụ kiện điện thoại', 'stock' => 100, 'image' => 'https://via.placeholder.com/600x600/00d4aa/ffffff?text=Sac+Du+Phong'],
            ['name' => 'Tai nghe AirPods Pro 2', 'description' => 'Tai nghe True Wireless chống ồn chủ động', 'price' => 6490000, 'category' => 'Phụ kiện điện thoại', 'stock' => 60, 'image' => 'https://cdn.tgdd.vn/Products/Images/54/289782/tai-nghe-bluetooth-airpods-pro-2nd-gen-usb-c-charge-apple-thumb-600x600.jpg'],
            ['name' => 'Củ sạc nhanh 65W GaN', 'description' => 'Củ sạc GaN công nghệ mới, nhỏ gọn, 3 cổng sạc', 'price' => 890000, 'category' => 'Phụ kiện điện thoại', 'stock' => 120, 'image' => 'https://via.placeholder.com/600x600/00d4aa/ffffff?text=Cu+Sac'],

            // Smartwatch
            ['name' => 'Apple Watch Series 9 GPS 45mm', 'description' => 'Apple Watch S9 chip mới, màn hình sáng hơn', 'price' => 11990000, 'category' => 'Smartwatch', 'stock' => 35, 'image' => 'https://cdn.tgdd.vn/Products/Images/7077/309732/apple-watch-s9-gps-45mm-vien-nhom-day-cao-su-thumb-600x600.jpg'],
            ['name' => 'Samsung Galaxy Watch6 Classic', 'description' => 'Galaxy Watch6 Classic vòng bezel xoay', 'price' => 9990000, 'category' => 'Smartwatch', 'stock' => 40, 'image' => 'https://cdn.tgdd.vn/Products/Images/7077/309108/samsung-galaxy-watch6-classic-47mm-den-thumb-600x600.jpg'],
            ['name' => 'Xiaomi Watch 2 Pro', 'description' => 'Xiaomi Watch 2 Pro HyperOS, pin 14 ngày', 'price' => 5490000, 'category' => 'Smartwatch', 'stock' => 55, 'image' => 'https://via.placeholder.com/600x600/00d4aa/ffffff?text=Xiaomi+Watch'],
        ];

        foreach ($products as $productData) {
            $category = $categories->firstWhere('name', $productData['category']);
            
            if ($category) {
                Product::updateOrCreate(
                    ['name' => $productData['name']],
                    [
                        'category_id' => $category->category_id,
                        'description' => $productData['description'],
                        'price' => $productData['price'],
                        'stock_quantity' => $productData['stock'],
                        'image_url' => $productData['image'],
                    ]
                );
            }
        }
    }
}
