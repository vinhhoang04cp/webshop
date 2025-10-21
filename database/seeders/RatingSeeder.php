<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Seeder;

class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy tất cả users và products
        $users = User::all();
        $products = Product::all();

        if ($users->count() == 0 || $products->count() == 0) {
            $this->command->info('Không có users hoặc products để tạo ratings');

            return;
        }

        // Tạo ratings mẫu
        $sampleRatings = [
            [
                'rating' => 5,
                'review' => 'Sản phẩm rất tuyệt vời! Chất lượng tốt, giao hàng nhanh. Tôi rất hài lòng với sản phẩm này.',
            ],
            [
                'rating' => 4,
                'review' => 'Sản phẩm khá ổn, đúng như mô tả. Chỉ có điều đóng gói hơi đơn giản.',
            ],
            [
                'rating' => 5,
                'review' => 'Tuyệt vời! Sản phẩm chất lượng cao, phù hợp với giá tiền. Sẽ mua lại lần sau.',
            ],
            [
                'rating' => 3,
                'review' => 'Sản phẩm bình thường, không có gì đặc biệt. Giá cả hợp lý.',
            ],
            [
                'rating' => 4,
                'review' => 'Chất lượng tốt, thiết kế đẹp. Dịch vụ chăm sóc khách hàng tận tình.',
            ],
            [
                'rating' => 5,
                'review' => 'Đây là lần thứ 3 tôi mua sản phẩm này. Rất tin tưởng vào chất lượng của shop.',
            ],
            [
                'rating' => 2,
                'review' => 'Sản phẩm không đúng như mong đợi. Chất lượng không tốt lắm.',
            ],
            [
                'rating' => 4,
                'review' => 'Sản phẩm đẹp, chất lượng khá tốt. Giao hàng đúng hẹn. Hài lòng với lần mua hàng này.',
            ],
            [
                'rating' => 5,
                'review' => 'Xuất sắc! Vượt quá mong đợi của tôi. Chắc chắn sẽ giới thiệu cho bạn bè.',
            ],
            [
                'rating' => 3,
                'review' => 'Sản phẩm ổn, không có gì để phải than phiền. Phù hợp với mức giá.',
            ],
        ];

        // Tạo 20-30 ratings ngẫu nhiên
        for ($i = 0; $i < 25; $i++) {
            $user = $users->random();
            $product = $products->random();
            $sampleRating = $sampleRatings[array_rand($sampleRatings)];

            Rating::create([
                'user_id' => $user->id,
                'product_id' => $product->product_id,
                'rating' => $sampleRating['rating'],
                'review' => $sampleRating['review'],
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        $this->command->info('Đã tạo 25 ratings mẫu!');
    }
}
