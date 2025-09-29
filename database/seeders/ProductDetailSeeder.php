<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductDetail;
use Illuminate\Database\Seeder;

class ProductDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        // Tạo chi tiết cho một số sản phẩm
        $productDetails = [
            ['product_name' => 'iPhone 15 Pro Max', 'size' => '6.7 inch', 'color' => 'Titan Tự Nhiên', 'material' => 'Titanium'],
            ['product_name' => 'iPhone 15 Pro Max', 'size' => '6.7 inch', 'color' => 'Titan Xanh', 'material' => 'Titanium'],
            ['product_name' => 'Samsung Galaxy S24 Ultra', 'size' => '6.8 inch', 'color' => 'Đen Titanium', 'material' => 'Titanium + Glass'],
            ['product_name' => 'Samsung Galaxy S24 Ultra', 'size' => '6.8 inch', 'color' => 'Tím', 'material' => 'Titanium + Glass'],
            ['product_name' => 'MacBook Air M3', 'size' => '13 inch', 'color' => 'Xám', 'material' => 'Aluminum'],
            ['product_name' => 'MacBook Air M3', 'size' => '15 inch', 'color' => 'Bạc', 'material' => 'Aluminum'],
            ['product_name' => 'Dell XPS 13', 'size' => '13.4 inch', 'color' => 'Bạc Platinum', 'material' => 'Carbon Fiber'],
            ['product_name' => 'Áo sơ mi nam công sở', 'size' => 'M', 'color' => 'Trắng', 'material' => 'Cotton'],
            ['product_name' => 'Áo sơ mi nam công sở', 'size' => 'L', 'color' => 'Xanh nhạt', 'material' => 'Cotton'],
            ['product_name' => 'Áo sơ mi nam công sở', 'size' => 'XL', 'color' => 'Trắng', 'material' => 'Cotton'],
            ['product_name' => 'Quần jean nữ skinny', 'size' => '27', 'color' => 'Xanh đậm', 'material' => 'Denim'],
            ['product_name' => 'Quần jean nữ skinny', 'size' => '28', 'color' => 'Xanh nhạt', 'material' => 'Denim'],
            ['product_name' => 'Quần jean nữ skinny', 'size' => '29', 'color' => 'Đen', 'material' => 'Denim'],
            ['product_name' => 'Giày sneaker Nike', 'size' => '40', 'color' => 'Trắng', 'material' => 'Leather + Synthetic'],
            ['product_name' => 'Giày sneaker Nike', 'size' => '41', 'color' => 'Đen', 'material' => 'Leather + Synthetic'],
            ['product_name' => 'Giày sneaker Nike', 'size' => '42', 'color' => 'Trắng/Đen', 'material' => 'Leather + Synthetic'],
            ['product_name' => 'Túi xách nữ da thật', 'size' => 'Medium', 'color' => 'Nâu', 'material' => 'Genuine Leather'],
            ['product_name' => 'Túi xách nữ da thật', 'size' => 'Large', 'color' => 'Đen', 'material' => 'Genuine Leather'],
            ['product_name' => 'Nồi cơm điện Panasonic', 'size' => '1.8L', 'color' => 'Trắng', 'material' => 'Stainless Steel'],
            ['product_name' => 'Bộ chăn ga gối Singapore', 'size' => '1m8 x 2m', 'color' => 'Hoa văn', 'material' => '100% Cotton'],
            ['product_name' => 'Bộ chăn ga gối Singapore', 'size' => '1m6 x 2m', 'color' => 'Trơn xanh', 'material' => '100% Cotton'],
            ['product_name' => 'Bàn làm việc gỗ sồi', 'size' => '120x60cm', 'color' => 'Vàng gỗ tự nhiên', 'material' => 'Oak Wood'],
            ['product_name' => 'LEGO Architecture', 'size' => 'Standard', 'color' => 'Đa màu', 'material' => 'ABS Plastic'],
            ['product_name' => 'Xe điều khiển từ xa', 'size' => '1:18', 'color' => 'Đỏ', 'material' => 'Plastic + Metal'],
            ['product_name' => 'Xe điều khiển từ xa', 'size' => '1:18', 'color' => 'Xanh', 'material' => 'Plastic + Metal'],
            ['product_name' => 'Nhẫn vàng 18K', 'size' => '16', 'color' => 'Vàng', 'material' => '18K Gold'],
            ['product_name' => 'Nhẫn vàng 18K', 'size' => '17', 'color' => 'Vàng', 'material' => '18K Gold'],
            ['product_name' => 'Dây chuyền bạc 925', 'size' => '45cm', 'color' => 'Bạc', 'material' => '925 Silver'],
            ['product_name' => 'Dây chuyền bạc 925', 'size' => '50cm', 'color' => 'Bạc', 'material' => '925 Silver'],
            ['product_name' => 'Ghế văn phòng ergonomic', 'size' => 'Standard', 'color' => 'Đen', 'material' => 'Mesh + Nylon'],
        ];

        foreach ($productDetails as $detail) {
            $product = $products->where('name', $detail['product_name'])->first();
            if ($product) {
                ProductDetail::create([
                    'product_id' => $product->product_id,
                    'size' => $detail['size'],
                    'color' => $detail['color'],
                    'material' => $detail['material'],
                ]);
            }
        }
    }
}
