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
       Schema::create('posts', function (Blueprint $table) {
        $table->id(); // int(11) AUTO_INCREMENT PRIMARY KEY
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ربط ذكي مع جدول المستخدمين
        $table->text('content')->nullable();
        $table->string('image', 255)->nullable();
        $table->string('video', 255)->nullable();
        $table->integer('privacy_level_id')->default(1);
        $table->integer('like_count')->default(0);
        $table->integer('comment_count')->default(0);
        $table->integer('share_count')->default(0);
        $table->tinyInteger('is_active')->default(1);
        $table->integer('parent_id')->default(0);
        $table->integer('post_type_id')->default(0);
        $table->timestamps(); // ينشئ تلقائياً created_at و updated_at
        $table->softDeletes(); // ينشئ deleted_at لدعم الحذف المؤقت المتواجد بملفك
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
