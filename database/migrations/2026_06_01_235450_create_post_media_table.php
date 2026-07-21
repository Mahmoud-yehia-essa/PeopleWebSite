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
      Schema::create('post_media', function (Blueprint $table) {
        $table->id(); // int(11) AUTO_INCREMENT PRIMARY KEY
        $table->foreignId('post_id')->constrained('posts')->onDelete('cascade'); // ربط مع المنشور
        $table->string('image', 255)->nullable();
        $table->string('video', 255)->nullable();
        $table->text('caption')->nullable();
        $table->tinyInteger('is_active')->default(1);
        $table->integer('position')->default(0); // لترتيب الميديا داخل المنشور في Flutter
        $table->timestamps(); // created_at, updated_at
        $table->softDeletes(); // deleted_at
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_media');
    }
};
