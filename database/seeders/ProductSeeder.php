<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        $categories = Category::all();

        // Tạo 50 sản phẩm với dữ liệu đa dạng
        $products = [
            // Điện tử
            ['name' => 'iPhone 15 Pro Max', 'description' => 'Smartphone cao cấp mới nhất từ Apple', 'price' => 29999000, 'category' => 'Điện tử', 'stock' => 25, 'image' => 'iphone-15-pro-max.jpg'],
            ['name' => 'Samsung Galaxy S24 Ultra', 'description' => 'Android flagship với bút S Pen', 'price' => 27999000, 'category' => 'Điện tử', 'stock' => 30, 'image' => 'galaxy-s24-ultra.jpg'],
            ['name' => 'MacBook Air M3', 'description' => 'Laptop siêu mỏng với chip M3', 'price' => 35999000, 'category' => 'Điện tử', 'stock' => 15, 'image' => 'macbook-air-m3.jpg'],
            ['name' => 'Dell XPS 13', 'description' => 'Ultrabook cao cấp cho dân văn phòng', 'price' => 28999000, 'category' => 'Điện tử', 'stock' => 20, 'image' => 'dell-xps-13.jpg'],
            ['name' => 'iPad Pro 12.9 inch', 'description' => 'Máy tính bảng chuyên nghiệp', 'price' => 32999000, 'category' => 'Điện tử', 'stock' => 18, 'image' => 'ipad-pro-129.jpg'],
            ['name' => 'Sony WH-1000XM5', 'description' => 'Tai nghe chống ồn cao cấp', 'price' => 8999000, 'category' => 'Điện tử', 'stock' => 40, 'image' => 'sony-wh1000xm5.jpg'],
            ['name' => 'Samsung QLED 55 inch', 'description' => 'Smart TV 4K QLED 55 inch', 'price' => 19999000, 'category' => 'Điện tử', 'stock' => 12, 'image' => 'samsung-qled-55.jpg'],

            // Thời trang
            ['name' => 'Áo sơ mi nam công sở', 'description' => 'Áo sơ mi trắng lịch lãm', 'price' => 299000, 'category' => 'Thời trang', 'stock' => 100, 'image' => 'ao-so-mi-nam.jpg'],
            ['name' => 'Quần jean nữ skinny', 'description' => 'Quần jean ôm dáng thời trang', 'price' => 599000, 'category' => 'Thời trang', 'stock' => 80, 'image' => 'quan-jean-nu.jpg'],
            ['name' => 'Giày sneaker Nike', 'description' => 'Giày thể thao Nike Air Force 1', 'price' => 2599000, 'category' => 'Thời trang', 'stock' => 60, 'image' => 'nike-air-force-1.jpg'],
            ['name' => 'Túi xách nữ da thật', 'description' => 'Túi xách cao cấp từ da thật', 'price' => 1999000, 'category' => 'Thời trang', 'stock' => 35, 'image' => 'tui-xach-nu.jpg'],
            ['name' => 'Đồng hồ nam Casio', 'description' => 'Đồng hồ thể thao chống nước', 'price' => 1299000, 'category' => 'Thời trang', 'stock' => 50, 'image' => 'dong-ho-casio.jpg'],

            // Nhà cửa & Đời sống
            ['name' => 'Nồi cơm điện Panasonic', 'description' => 'Nồi cơm điện cao tần 1.8L', 'price' => 2799000, 'category' => 'Nhà cửa & Đời sống', 'stock' => 45, 'image' => 'noi-com-dien.jpg'],
            ['name' => 'Máy lọc nước RO', 'description' => 'Máy lọc nước 10 cấp độ', 'price' => 5999000, 'category' => 'Nhà cửa & Đời sống', 'stock' => 25, 'image' => 'may-loc-nuoc.jpg'],
            ['name' => 'Bộ chăn ga gối Singapore', 'description' => 'Bộ chăn ga cotton cao cấp', 'price' => 899000, 'category' => 'Nhà cửa & Đời sống', 'stock' => 70, 'image' => 'bo-chan-ga.jpg'],
            ['name' => 'Bàn làm việc gỗ sồi', 'description' => 'Bàn làm việc minimalist', 'price' => 3499000, 'category' => 'Nhà cửa & Đời sống', 'stock' => 20, 'image' => 'ban-lam-viec.jpg'],

            // Sách
            ['name' => 'Sapiens - Lược sử loài người', 'description' => 'Cuốn sách bestseller của Yuval Noah Harari', 'price' => 199000, 'category' => 'Sách', 'stock' => 150, 'image' => 'sapiens-book.jpg'],
            ['name' => 'Đắc Nhân Tâm', 'description' => 'Nghệ thuật giao tiếp và ứng xử', 'price' => 89000, 'category' => 'Sách', 'stock' => 200, 'image' => 'dac-nhan-tam.jpg'],
            ['name' => 'Clean Code', 'description' => 'Cẩm nang viết code sạch', 'price' => 299000, 'category' => 'Sách', 'stock' => 80, 'image' => 'clean-code.jpg'],
            ['name' => 'Tôi Thấy Hoa Vàng Trên Cỏ Xanh', 'description' => 'Tiểu thuyết của Nguyễn Nhật Ánh', 'price' => 79000, 'category' => 'Sách', 'stock' => 120, 'image' => 'hoa-vang-co-xanh.jpg'],

            // Thể thao & Du lịch
            ['name' => 'Máy chạy bộ điện', 'description' => 'Máy chạy bộ cao cấp cho gia đình', 'price' => 12999000, 'category' => 'Thể thao & Du lịch', 'stock' => 15, 'image' => 'may-chay-bo.jpg'],
            ['name' => 'Bóng đá Nike Premier League', 'description' => 'Bóng đá chính hãng FIFA Quality', 'price' => 799000, 'category' => 'Thể thao & Du lịch', 'stock' => 50, 'image' => 'bong-da-nike.jpg'],
            ['name' => 'Ba lô du lịch 40L', 'description' => 'Ba lô trekking chống thấm nước', 'price' => 1299000, 'category' => 'Thể thao & Du lịch', 'stock' => 40, 'image' => 'ba-lo-du-lich.jpg'],
            ['name' => 'Giày chạy bộ Adidas', 'description' => 'Giày chạy bộ công nghệ Boost', 'price' => 2299000, 'category' => 'Thể thao & Du lịch', 'stock' => 65, 'image' => 'giay-chay-bo.jpg'],

            // Làm đẹp & Sức khỏe
            ['name' => 'Kem chống nắng Anessa', 'description' => 'Kem chống nắng SPF 50+ PA++++', 'price' => 599000, 'category' => 'Làm đẹp & Sức khỏe', 'stock' => 90, 'image' => 'kem-chong-nang.jpg'],
            ['name' => 'Son môi Dior Rouge', 'description' => 'Son môi cao cấp lâu trôi', 'price' => 1399000, 'category' => 'Làm đẹp & Sức khỏe', 'stock' => 75, 'image' => 'son-moi-dior.jpg'],
            ['name' => 'Vitamin C Blackmores', 'description' => 'Viên uống bổ sung Vitamin C', 'price' => 299000, 'category' => 'Làm đẹp & Sức khỏe', 'stock' => 100, 'image' => 'vitamin-c.jpg'],
            ['name' => 'Máy massage cầm tay', 'description' => 'Máy massage thư giãn cơ bắp', 'price' => 899000, 'category' => 'Làm đẹp & Sức khỏe', 'stock' => 55, 'image' => 'may-massage.jpg'],

            // Đồ chơi
            ['name' => 'LEGO Architecture', 'description' => 'Bộ xếp hình kiến trúc nổi tiếng', 'price' => 1999000, 'category' => 'Đồ chơi', 'stock' => 30, 'image' => 'lego-architecture.jpg'],
            ['name' => 'Búp bê Barbie', 'description' => 'Búp bê thời trang cao cấp', 'price' => 699000, 'category' => 'Đồ chơi', 'stock' => 60, 'image' => 'bup-be-barbie.jpg'],
            ['name' => 'Xe điều khiển từ xa', 'description' => 'Xe địa hình điều khiển từ xa', 'price' => 1599000, 'category' => 'Đồ chơi', 'stock' => 35, 'image' => 'xe-dieu-khien.jpg'],
            ['name' => 'Rubik 3x3 cao cấp', 'description' => 'Rubik tốc độ chuyên nghiệp', 'price' => 299000, 'category' => 'Đồ chơi', 'stock' => 80, 'image' => 'rubik-3x3.jpg'],

            // Ô tô & Xe máy
            ['name' => 'Lốp xe Michelin', 'description' => 'Lốp xe ô tô cao cấp 215/60R16', 'price' => 2199000, 'category' => 'Ô tô & Xe máy', 'stock' => 40, 'image' => 'lop-xe-michelin.jpg'],
            ['name' => 'Dầu nhớt Castrol', 'description' => 'Dầu nhớt tổng hợp 5W-30', 'price' => 799000, 'category' => 'Ô tô & Xe máy', 'stock' => 70, 'image' => 'dau-nhot-castrol.jpg'],
            ['name' => 'Camera hành trình', 'description' => 'Camera hành trình 4K WiFi', 'price' => 2999000, 'category' => 'Ô tô & Xe máy', 'stock' => 25, 'image' => 'camera-hanh-trinh.jpg'],

            // Mẹ & Bé
            ['name' => 'Xe đẩy em bé Combi', 'description' => 'Xe đẩy gấp gọn 2 chiều', 'price' => 4999000, 'category' => 'Mẹ & Bé', 'stock' => 20, 'image' => 'xe-day-em-be.jpg'],
            ['name' => 'Tã Pampers newborn', 'description' => 'Tã dán sơ sinh siêu mềm', 'price' => 349000, 'category' => 'Mẹ & Bé', 'stock' => 150, 'image' => 'ta-pampers.jpg'],
            ['name' => 'Sữa Enfamil A+', 'description' => 'Sữa bột cho trẻ từ 0-6 tháng', 'price' => 659000, 'category' => 'Mẹ & Bé', 'stock' => 80, 'image' => 'sua-enfamil.jpg'],

            // Trang sức
            ['name' => 'Nhẫn vàng 18K', 'description' => 'Nhẫn vàng trơn cao cấp', 'price' => 8999000, 'category' => 'Trang sức', 'stock' => 10, 'image' => 'nhan-vang-18k.jpg'],
            ['name' => 'Đồng hồ Citizen', 'description' => 'Đồng hồ Eco-Drive năng lượng mặt trời', 'price' => 5999000, 'category' => 'Trang sức', 'stock' => 15, 'image' => 'dong-ho-citizen.jpg'],
            ['name' => 'Dây chuyền bạc 925', 'description' => 'Dây chuyền bạc thật cao cấp', 'price' => 799000, 'category' => 'Trang sức', 'stock' => 45, 'image' => 'day-chuyen-bac.jpg'],

            // Văn phòng phẩm
            ['name' => 'Bút Parker cao cấp', 'description' => 'Bút bi Parker Jotter Steel', 'price' => 899000, 'category' => 'Văn phòng phẩm', 'stock' => 60, 'image' => 'but-parker.jpg'],
            ['name' => 'Máy in HP LaserJet', 'description' => 'Máy in laser đen trắng', 'price' => 3999000, 'category' => 'Văn phòng phẩm', 'stock' => 25, 'image' => 'may-in-hp.jpg'],
            ['name' => 'Ghế văn phòng ergonomic', 'description' => 'Ghế xoay chống mỏi lưng', 'price' => 2799000, 'category' => 'Văn phòng phẩm', 'stock' => 30, 'image' => 'ghe-van-phong.jpg'],

            // Thực phẩm & Đồ uống
            ['name' => 'Cà phê Trung Nguyên', 'description' => 'Cà phê rang xay cao cấp 500g', 'price' => 199000, 'category' => 'Thực phẩm & Đồ uống', 'stock' => 200, 'image' => 'ca-phe-trung-nguyen.jpg'],
            ['name' => 'Mật ong rừng nguyên chất', 'description' => 'Mật ong hoa rừng 500ml', 'price' => 299000, 'category' => 'Thực phẩm & Đồ uống', 'stock' => 100, 'image' => 'mat-ong-rung.jpg'],
            ['name' => 'Trà Oolong Đài Loan', 'description' => 'Trà Oolong cao cấp 200g', 'price' => 499000, 'category' => 'Thực phẩm & Đồ uống', 'stock' => 80, 'image' => 'tra-oolong.jpg'],

            // Thú cưng
            ['name' => 'Thức ăn cho chó Royal Canin', 'description' => 'Thức ăn khô cho chó trưởng thành 15kg', 'price' => 1899000, 'category' => 'Thú cưng', 'stock' => 50, 'image' => 'thuc-an-cho.jpg'],
            ['name' => 'Cát vệ sinh cho mèo', 'description' => 'Cát bentonite khử mùi 10L', 'price' => 299000, 'category' => 'Thú cưng', 'stock' => 90, 'image' => 'cat-ve-sinh-meo.jpg'],
            ['name' => 'Lồng chó inox', 'description' => 'Lồng chó inox 304 size M', 'price' => 1599000, 'category' => 'Thú cưng', 'stock' => 25, 'image' => 'long-cho-inox.jpg'],

            // Vườn & Ngoài trời
            ['name' => 'Cây xanh phong thủy', 'description' => 'Cây kim ngân để bàn', 'price' => 199000, 'category' => 'Vườn & Ngoài trời', 'stock' => 100, 'image' => 'cay-kim-ngan.jpg'],
            ['name' => 'Máy cắt cỏ điện', 'description' => 'Máy cắt cỏ cầm tay 1200W', 'price' => 2599000, 'category' => 'Vườn & Ngoài trời', 'stock' => 20, 'image' => 'may-cat-co.jpg'],
        ];

        foreach ($products as $productData) {
            $category = $categories->where('name', $productData['category'])->first();
            if ($category) {
                Product::create([
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'category_id' => $category->category_id,
                    'stock_quantity' => $productData['stock'],
                    'image_url' => 'images/products/'.$productData['image'],
                ]);
            }
        }
    }
}
