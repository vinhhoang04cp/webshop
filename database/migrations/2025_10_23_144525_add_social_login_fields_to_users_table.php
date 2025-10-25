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
        Schema::table('users', function (Blueprint $table) {
            // Thêm các trường provider, provider_id, avatar vào bảng users sau trường remember_token
            // provider: tên provider (google, facebook, github), kiểu dữ liệu là string, giá trị mặc định là null
            // provider_id: id của provider, kiểu dữ liệu là string, giá trị mặc định là null
            // avatar: url avatar của user, kiểu dữ liệu là string, giá trị mặc định là null
            $table->string('provider')->nullable()->after('remember_token');
            $table->string('provider_id')->nullable()->after('provider');
            $table->string('avatar')->nullable()->after('provider_id');

            // Cho phép password null khi đăng nhập bằng social
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['provider', 'provider_id', 'avatar']);
        });
    }
};
