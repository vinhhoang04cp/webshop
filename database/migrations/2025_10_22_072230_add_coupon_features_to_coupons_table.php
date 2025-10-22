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
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('name')->after('code')->nullable(); // Tên coupon
            $table->decimal('min_order_amount', 10, 2)->after('discount_value')->default(0); // Đơn hàng tối thiểu
            $table->decimal('max_discount_amount', 10, 2)->after('min_order_amount')->nullable(); // Giảm tối đa (cho % discount)
            $table->integer('usage_limit')->after('max_discount_amount')->nullable(); // Giới hạn số lần dùng
            $table->integer('used_count')->after('usage_limit')->default(0); // Số lần đã dùng
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['name', 'min_order_amount', 'max_discount_amount', 'usage_limit', 'used_count']);
        });
    }
};
