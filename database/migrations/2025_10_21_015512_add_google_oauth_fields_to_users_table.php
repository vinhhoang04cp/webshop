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
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('firebase_uid')->nullable()->unique()->after('google_id');
            $table->string('provider')->default('email')->after('firebase_uid'); // email, google, etc.
            $table->string('avatar')->nullable()->after('provider'); // URL của avatar từ Google
            $table->timestamp('email_verified_at')->nullable()->change(); // Make email_verified_at nullable if not already
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'firebase_uid', 'provider', 'avatar']);
        });
    }
};
