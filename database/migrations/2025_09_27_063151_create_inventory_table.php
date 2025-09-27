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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id('inventory_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('stock_in')->default(0);
            $table->unsignedInteger('stock_out')->default(0);
            $table->integer('current_stock');
            $table->timestamp('updated_at')->nullable();

            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->unique('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
