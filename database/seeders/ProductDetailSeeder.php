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

        // Chi tiết kỹ thuật cho điện thoại
        $productDetails = [
            // iPhone 15 Pro Max
            [
                'product_name' => 'iPhone 15 Pro Max 256GB',
                'color' => 'Titan Xanh',
                'storage' => '256GB',
                'ram' => '8GB',
                'screen_size' => '6.7 inch',
                'chip' => 'Apple A17 Pro',
                'battery' => '4422 mAh',
                'camera_main' => '48MP Main + 12MP Telephoto + 12MP Ultra Wide',
                'camera_front' => '12MP',
                'os' => 'iOS 17',
                'special_features' => 'Dynamic Island, Titanium Design, ProMotion 120Hz, USB-C, Action Button'
            ],
            [
                'product_name' => 'iPhone 15 Pro Max 256GB',
                'color' => 'Titan Tự Nhiên',
                'storage' => '256GB',
                'ram' => '8GB',
                'screen_size' => '6.7 inch',
                'chip' => 'Apple A17 Pro',
                'battery' => '4422 mAh',
                'camera_main' => '48MP Main + 12MP Telephoto + 12MP Ultra Wide',
                'camera_front' => '12MP',
                'os' => 'iOS 17',
                'special_features' => 'Dynamic Island, Titanium Design, ProMotion 120Hz, USB-C, Action Button'
            ],

            // iPhone 15 Pro
            [
                'product_name' => 'iPhone 15 Pro 128GB',
                'color' => 'Titan Đen',
                'storage' => '128GB',
                'ram' => '8GB',
                'screen_size' => '6.1 inch',
                'chip' => 'Apple A17 Pro',
                'battery' => '3274 mAh',
                'camera_main' => '48MP Main + 12MP Telephoto + 12MP Ultra Wide',
                'camera_front' => '12MP',
                'os' => 'iOS 17',
                'special_features' => 'Dynamic Island, Titanium Design, ProMotion 120Hz, USB-C'
            ],

            // iPhone 15
            [
                'product_name' => 'iPhone 15 128GB',
                'color' => 'Xanh Lá',
                'storage' => '128GB',
                'ram' => '6GB',
                'screen_size' => '6.1 inch',
                'chip' => 'Apple A16 Bionic',
                'battery' => '3349 mAh',
                'camera_main' => '48MP Main + 12MP Ultra Wide',
                'camera_front' => '12MP',
                'os' => 'iOS 17',
                'special_features' => 'Dynamic Island, USB-C, Ceramic Shield'
            ],
            [
                'product_name' => 'iPhone 15 128GB',
                'color' => 'Hồng',
                'storage' => '128GB',
                'ram' => '6GB',
                'screen_size' => '6.1 inch',
                'chip' => 'Apple A16 Bionic',
                'battery' => '3349 mAh',
                'camera_main' => '48MP Main + 12MP Ultra Wide',
                'camera_front' => '12MP',
                'os' => 'iOS 17',
                'special_features' => 'Dynamic Island, USB-C, Ceramic Shield'
            ],

            // Samsung Galaxy S24 Ultra
            [
                'product_name' => 'Samsung Galaxy S24 Ultra 256GB',
                'color' => 'Titan Đen',
                'storage' => '256GB',
                'ram' => '12GB',
                'screen_size' => '6.8 inch',
                'chip' => 'Snapdragon 8 Gen 3 for Galaxy',
                'battery' => '5000 mAh',
                'camera_main' => '200MP Main + 50MP Periscope + 10MP Telephoto + 12MP Ultra Wide',
                'camera_front' => '12MP',
                'os' => 'Android 14, One UI 6.1',
                'special_features' => 'S Pen, Galaxy AI, QHD+ AMOLED 2X, Gorilla Armor, Titanium Frame'
            ],
            [
                'product_name' => 'Samsung Galaxy S24 Ultra 256GB',
                'color' => 'Titan Tím',
                'storage' => '256GB',
                'ram' => '12GB',
                'screen_size' => '6.8 inch',
                'chip' => 'Snapdragon 8 Gen 3 for Galaxy',
                'battery' => '5000 mAh',
                'camera_main' => '200MP Main + 50MP Periscope + 10MP Telephoto + 12MP Ultra Wide',
                'camera_front' => '12MP',
                'os' => 'Android 14, One UI 6.1',
                'special_features' => 'S Pen, Galaxy AI, QHD+ AMOLED 2X, Gorilla Armor, Titanium Frame'
            ],

            // Samsung Galaxy S24
            [
                'product_name' => 'Samsung Galaxy S24 128GB',
                'color' => 'Xám',
                'storage' => '128GB',
                'ram' => '8GB',
                'screen_size' => '6.2 inch',
                'chip' => 'Exynos 2400',
                'battery' => '4000 mAh',
                'camera_main' => '50MP Main + 10MP Telephoto + 12MP Ultra Wide',
                'camera_front' => '12MP',
                'os' => 'Android 14, One UI 6.1',
                'special_features' => 'Galaxy AI, FHD+ AMOLED 2X, Gorilla Glass Victus 2'
            ],

            // Samsung Galaxy A55
            [
                'product_name' => 'Samsung Galaxy A55 5G 128GB',
                'color' => 'Xanh Nhạt',
                'storage' => '128GB',
                'ram' => '8GB',
                'screen_size' => '6.6 inch',
                'chip' => 'Exynos 1480',
                'battery' => '5000 mAh',
                'camera_main' => '50MP Main OIS + 12MP Ultra Wide + 5MP Macro',
                'camera_front' => '32MP',
                'os' => 'Android 14, One UI 6.1',
                'special_features' => 'Super AMOLED 120Hz, IP67, Knox Security'
            ],

            // Xiaomi 14 Ultra
            [
                'product_name' => 'Xiaomi 14 Ultra 512GB',
                'color' => 'Đen',
                'storage' => '512GB',
                'ram' => '16GB',
                'screen_size' => '6.73 inch',
                'chip' => 'Snapdragon 8 Gen 3',
                'battery' => '5000 mAh',
                'camera_main' => '50MP Main Leica + 50MP Periscope + 50MP Telephoto + 50MP Ultra Wide',
                'camera_front' => '32MP',
                'os' => 'Android 14, HyperOS',
                'special_features' => 'Leica Professional Optics, LTPO AMOLED 120Hz, Sạc nhanh 90W'
            ],

            // Xiaomi Redmi Note 13 Pro+
            [
                'product_name' => 'Xiaomi Redmi Note 13 Pro+ 5G',
                'color' => 'Đen',
                'storage' => '256GB',
                'ram' => '8GB',
                'screen_size' => '6.67 inch',
                'chip' => 'MediaTek Dimensity 7200-Ultra',
                'battery' => '5000 mAh',
                'camera_main' => '200MP Main + 8MP Ultra Wide + 2MP Macro',
                'camera_front' => '16MP',
                'os' => 'Android 13, MIUI 14',
                'special_features' => 'AMOLED 120Hz, IP68, Sạc nhanh 120W'
            ],
            [
                'product_name' => 'Xiaomi Redmi Note 13 Pro+ 5G',
                'color' => 'Tím',
                'storage' => '256GB',
                'ram' => '8GB',
                'screen_size' => '6.67 inch',
                'chip' => 'MediaTek Dimensity 7200-Ultra',
                'battery' => '5000 mAh',
                'camera_main' => '200MP Main + 8MP Ultra Wide + 2MP Macro',
                'camera_front' => '16MP',
                'os' => 'Android 13, MIUI 14',
                'special_features' => 'AMOLED 120Hz, IP68, Sạc nhanh 120W'
            ],

            // Xiaomi Redmi 13C
            [
                'product_name' => 'Xiaomi Redmi 13C 128GB',
                'color' => 'Xanh Lá',
                'storage' => '128GB',
                'ram' => '4GB',
                'screen_size' => '6.74 inch',
                'chip' => 'MediaTek Helio G85',
                'battery' => '5000 mAh',
                'camera_main' => '50MP Main + 2MP Depth',
                'camera_front' => '8MP',
                'os' => 'Android 13, MIUI 14',
                'special_features' => 'IPS LCD 90Hz, Sạc nhanh 18W'
            ],

            // OPPO Find X7 Ultra
            [
                'product_name' => 'OPPO Find X7 Ultra 512GB',
                'color' => 'Đen',
                'storage' => '512GB',
                'ram' => '16GB',
                'screen_size' => '6.82 inch',
                'chip' => 'Snapdragon 8 Gen 3',
                'battery' => '5000 mAh',
                'camera_main' => '50MP Main Hasselblad + 50MP Periscope + 50MP Telephoto + 50MP Ultra Wide',
                'camera_front' => '32MP',
                'os' => 'Android 14, ColorOS 14',
                'special_features' => 'Hasselblad Camera, LTPO AMOLED 120Hz, Sạc nhanh 100W'
            ],

            // OPPO Reno11 F
            [
                'product_name' => 'OPPO Reno11 F 5G 256GB',
                'color' => 'Xanh',
                'storage' => '256GB',
                'ram' => '8GB',
                'screen_size' => '6.7 inch',
                'chip' => 'MediaTek Dimensity 7050',
                'battery' => '5000 mAh',
                'camera_main' => '64MP Main + 8MP Ultra Wide + 2MP Macro',
                'camera_front' => '32MP',
                'os' => 'Android 14, ColorOS 14',
                'special_features' => 'AMOLED 120Hz, IP65, Sạc nhanh SUPERVOOC 67W'
            ],

            // Vivo V30
            [
                'product_name' => 'Vivo V30 5G 256GB',
                'color' => 'Xanh',
                'storage' => '256GB',
                'ram' => '12GB',
                'screen_size' => '6.78 inch',
                'chip' => 'Snapdragon 7 Gen 3',
                'battery' => '5000 mAh',
                'camera_main' => '50MP Main OIS + 50MP Ultra Wide',
                'camera_front' => '50MP',
                'os' => 'Android 14, Funtouch OS 14',
                'special_features' => 'AMOLED 120Hz, Camera Zeiss, Sạc nhanh 80W, IP54'
            ],

            // Realme GT 5 Pro
            [
                'product_name' => 'Realme GT 5 Pro 256GB',
                'color' => 'Xám',
                'storage' => '256GB',
                'ram' => '12GB',
                'screen_size' => '6.78 inch',
                'chip' => 'Snapdragon 8 Gen 3',
                'battery' => '5400 mAh',
                'camera_main' => '50MP Main OIS + 50MP Periscope + 8MP Ultra Wide',
                'camera_front' => '32MP',
                'os' => 'Android 14, Realme UI 5.0',
                'special_features' => 'LTPO AMOLED 144Hz, Sạc nhanh 100W, IP64'
            ],

            // Realme C67
            [
                'product_name' => 'Realme C67 128GB',
                'color' => 'Xanh',
                'storage' => '128GB',
                'ram' => '6GB',
                'screen_size' => '6.72 inch',
                'chip' => 'Snapdragon 685',
                'battery' => '5000 mAh',
                'camera_main' => '108MP Main + 2MP Depth',
                'camera_front' => '8MP',
                'os' => 'Android 13, Realme UI 4.0',
                'special_features' => 'IPS LCD 90Hz, Sạc nhanh 33W, IP54'
            ],

            // Nokia G60
            [
                'product_name' => 'Nokia G60 5G 128GB',
                'color' => 'Đen',
                'storage' => '128GB',
                'ram' => '6GB',
                'screen_size' => '6.58 inch',
                'chip' => 'Snapdragon 695 5G',
                'battery' => '4500 mAh',
                'camera_main' => '50MP Main + 5MP Ultra Wide + 2MP Depth',
                'camera_front' => '8MP',
                'os' => 'Android 12, Pure Android',
                'special_features' => 'IPS LCD 120Hz, Android One, Recyclable Materials'
            ],
        ];

        foreach ($productDetails as $detail) {
            $product = $products->where('name', 'LIKE', '%' . $detail['product_name'] . '%')->first();
            if ($product) {
                ProductDetail::create([
                    'product_id' => $product->product_id,
                    'color' => $detail['color'],
                    'storage' => $detail['storage'],
                    'ram' => $detail['ram'],
                    'screen_size' => $detail['screen_size'],
                    'chip' => $detail['chip'],
                    'battery' => $detail['battery'],
                    'camera_main' => $detail['camera_main'],
                    'camera_front' => $detail['camera_front'],
                    'os' => $detail['os'],
                    'special_features' => $detail['special_features'],
                ]);
            }
        }
    }
}
