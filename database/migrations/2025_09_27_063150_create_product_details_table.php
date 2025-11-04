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
        Schema::create('product_details', function (Blueprint $table) {
            $table->id('detail_id');
            $table->unsignedBigInteger('product_id');

            // Thông số kỹ thuật điện thoại
            $table->string('color', 50)->nullable();           // Màu sắc
            $table->string('storage', 20)->nullable();         // Bộ nhớ (128GB, 256GB, 512GB)
            $table->string('ram', 20)->nullable();             // RAM (8GB, 12GB, 16GB)
            $table->string('screen_size', 20)->nullable();     // Kích thước màn hình (6.7 inch)
            $table->string('chip', 100)->nullable();           // Chip xử lý
            $table->string('battery', 50)->nullable();         // Pin (mAh)
            $table->string('camera_main', 100)->nullable();    // Camera chính
            $table->string('camera_front', 100)->nullable();   // Camera trước
            $table->string('os', 50)->nullable();              // Hệ điều hành
            $table->text('special_features')->nullable();      // Tính năng đặc biệt
            $table->timestamps();

            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_details');
    }
};
