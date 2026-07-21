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
    Schema::create('users', function (Blueprint $table) {
        $table->id(); // int(11) AUTO_INCREMENT PRIMARY KEY
        $table->string('email', 255)->unique()->nullable();
        $table->string('password', 255);
        $table->string('password_hash', 200);
        $table->string('phone_number', 20)->nullable();
        $table->string('first_name', 50);
        $table->string('last_name', 50);
        $table->string('profile_picture', 255)->nullable();
        $table->string('cover_picture', 255)->nullable();
        $table->date('birth_date')->nullable();
        $table->string('gender', 20)->nullable();
        $table->string('address', 255)->nullable();
        $table->text('bio')->nullable();
        $table->integer('post_count')->default(0);
        $table->integer('friend_count')->default(0);
        $table->string('reset_code', 6)->nullable();
        $table->timestamp('last_login')->nullable();
        $table->timestamps(); // ينشئ تلقائياً created_at و updated_at كـ timestamp
        $table->softDeletes(); // ينشئ deleted_at لدعم الحذف المؤقت المتواجد بملفك
        $table->tinyInteger('is_active')->default(1);
        $table->string('token', 255)->nullable();
        $table->integer('status');
        $table->integer('is_verified')->default(0);
    });

    Schema::create('password_reset_tokens', function (Blueprint $table) {
        $table->string('email')->primary();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    });

    Schema::create('sessions', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
