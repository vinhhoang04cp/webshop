<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id('coupon_id');
            $table->string('code', 50)->unique(); // Mã coupon duy nhất
            $table->string('name')->nullable(); // Tên coupon
            $table->enum('discount_type', ['percentage', 'fixed']); // Loại giảm giá
            $table->decimal('discount_value', 10, 2); // Giá trị giảm
            $table->decimal('min_order_amount', 10, 2)->default(0); // Đơn hàng tối thiểu
            $table->decimal('max_discount_amount', 10, 2)->nullable(); // Giảm tối đa (cho % discount)
            $table->integer('usage_limit')->nullable(); // Giới hạn số lần dùng
            $table->integer('used_count')->default(0); // Số lần đã dùng
            $table->unsignedBigInteger('product_id')->nullable(); // Nếu null = áp dụng cho tất cả sản phẩm
            $table->datetime('start_date'); // Ngày bắt đầu
            $table->datetime('end_date'); // Ngày kết thúc
            $table->boolean('is_active')->default(true); // Trạng thái
            $table->timestamps();

            // Foreign key
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');

            // Index để tăng tốc độ truy vấn
            $table->index('code');
            $table->index('product_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
