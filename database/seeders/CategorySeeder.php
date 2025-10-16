<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'iPhone', 'description' => 'Điện thoại iPhone chính hãng Apple - Thiết kế sang trọng, hiệu năng mạnh mẽ'],
            ['name' => 'Samsung', 'description' => 'Điện thoại Samsung Galaxy - Công nghệ tiên tiến, màn hình tuyệt đẹp'],
            ['name' => 'Xiaomi', 'description' => 'Điện thoại Xiaomi - Giá tốt, cấu hình cao, pin trâu'],
            ['name' => 'OPPO', 'description' => 'Điện thoại OPPO - Camera selfie đẹp, sạc nhanh VOOC'],
            ['name' => 'Vivo', 'description' => 'Điện thoại Vivo - Thiết kế thời trang, âm thanh sống động'],
            ['name' => 'Realme', 'description' => 'Điện thoại Realme - Hiệu năng gaming, giá phải chăng'],
            ['name' => 'Nokia', 'description' => 'Điện thoại Nokia - Độ bền cao, pin khủng, Android One'],
            ['name' => 'Google Pixel', 'description' => 'Điện thoại Google Pixel - Android thuần, camera AI xuất sắc'],
            ['name' => 'OnePlus', 'description' => 'Điện thoại OnePlus - Flagship killer, sạc cực nhanh'],
            ['name' => 'Asus ROG Phone', 'description' => 'Điện thoại gaming Asus ROG - Chuyên game, hiệu năng khủng'],
            ['name' => 'Phụ kiện điện thoại', 'description' => 'Ốp lưng, dán màn hình, sạc dự phòng, tai nghe'],
            ['name' => 'Smartwatch', 'description' => 'Đồng hồ thông minh - Theo dõi sức khỏe, kết nối smartphone'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
