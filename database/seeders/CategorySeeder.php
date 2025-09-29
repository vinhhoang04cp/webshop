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
            ['name' => 'Điện tử', 'description' => 'Thiết bị điện tử, máy tính, điện thoại'],
            ['name' => 'Thời trang', 'description' => 'Quần áo, giày dép, phụ kiện thời trang'],
            ['name' => 'Nhà cửa & Đời sống', 'description' => 'Đồ gia dụng, nội thất, trang trí'],
            ['name' => 'Sách', 'description' => 'Sách văn học, giáo khoa, tham khảo'],
            ['name' => 'Thể thao & Du lịch', 'description' => 'Dụng cụ thể thao, đồ du lịch'],
            ['name' => 'Làm đẹp & Sức khỏe', 'description' => 'Mỹ phẩm, chăm sóc sức khỏe'],
            ['name' => 'Đồ chơi', 'description' => 'Đồ chơi trẻ em, game, puzzle'],
            ['name' => 'Ô tô & Xe máy', 'description' => 'Phụ tùng, phụ kiện xe hơi, xe máy'],
            ['name' => 'Mẹ & Bé', 'description' => 'Đồ dùng cho mẹ và trẻ em'],
            ['name' => 'Trang sức', 'description' => 'Nhẫn, dây chuyền, đồng hồ'],
            ['name' => 'Văn phòng phẩm', 'description' => 'Dụng cụ học tập, văn phòng'],
            ['name' => 'Thực phẩm & Đồ uống', 'description' => 'Thực phẩm tươi sống, đồ uống'],
            ['name' => 'Thú cưng', 'description' => 'Đồ dùng cho thú cưng'],
            ['name' => 'Vườn & Ngoài trời', 'description' => 'Cây cảnh, dụng cụ làm vườn'],
            ['name' => 'Nghệ thuật & Thủ công', 'description' => 'Đồ nghệ thuật, thủ công mỹ nghệ'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
