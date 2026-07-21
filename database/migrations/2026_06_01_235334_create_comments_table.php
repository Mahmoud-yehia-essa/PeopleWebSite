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
      Schema::create('comments', function (Blueprint $table) {
        $table->id(); // int(11) AUTO_INCREMENT PRIMARY KEY
        $table->foreignId('post_id')->constrained('posts')->onDelete('cascade'); // ربط مع المنشور
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ربط مع صاحب التعليق
        $table->text('content');
        $table->tinyInteger('is_active')->default(1);
        $table->integer('reaction_count')->default(0);
        $table->integer('reply_count')->default(0);
        $table->integer('parent_id')->default(0);
        $table->timestamps(); // created_at, updated_at
        $table->softDeletes(); // deleted_at
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
