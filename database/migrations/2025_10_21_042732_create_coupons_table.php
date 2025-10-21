<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id('coupon_id');
            $table->string('code', 50)->unique(); // Mã coupon duy nhất
            $table->string('name', 200); // Tên coupon
            $table->text('description')->nullable(); // Mô tả coupon
            $table->enum('discount_type', ['percentage', 'fixed']); // Loại giảm giá: phần trăm hoặc số tiền cố định
            $table->decimal('discount_value', 10, 2); // Giá trị giảm giá
            $table->decimal('min_order_amount', 10, 2)->default(0); // Giá trị đơn hàng tối thiểu
            $table->decimal('max_discount_amount', 10, 2)->nullable(); // Số tiền giảm tối đa (cho loại phần trăm)
            $table->unsignedInteger('usage_limit')->nullable(); // Giới hạn số lần sử dụng
            $table->unsignedInteger('used_count')->default(0); // Số lần đã sử dụng
            $table->datetime('start_date'); // Ngày bắt đầu
            $table->datetime('end_date'); // Ngày kết thúc
            $table->boolean('is_active')->default(true); // Trạng thái hoạt động
            $table->timestamps();
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
