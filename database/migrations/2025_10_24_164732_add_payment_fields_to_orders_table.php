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
        Schema::table('orders', function (Blueprint $table) {
            // Thêm các trường payment_status, payment_method, transaction_id, paid_at vào bảng orders
            // payment_status: trạng thái thanh toán , kiểu dữ liệu là enum, giá trị mặc định là 'pending'
            // payment_method: phương thức thanh toán , kiểu dữ liệu là string, giá trị mặc định là null
            // transaction_id: mã giao dịch, kiểu dữ liệu là string, giá trị mặc định là null
            // paid_at: thời gian thanh toán, kiểu dữ liệu là timestamp, giá trị mặc định là null
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])
                ->default('pending')
                ->after('status');
            $table->string('payment_method', 50)->nullable()->after('payment_status');
            $table->string('transaction_id')->nullable()->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_method', 'transaction_id', 'paid_at']); // Xóa các trường payment_status, payment_method, transaction_id, paid_at vào bảng orders
        });
    }
};
