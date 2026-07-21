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
       Schema::create('pinned_posts', function (Blueprint $table) {
            $table->id(); // int(11) AUTO_INCREMENT PRIMARY KEY
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->enum('pin_scope', ['profile', 'home']);
            $table->integer('pin_order')->default(0);
            $table->timestamp('pinned_at')->useCurrent();
            $table->timestamps();

            // الفهارس الفريدة المذكورة بملفك لمنع تكرار تثبيت المنشور في نفس النطاق
            $table->unique(['post_id', 'pin_scope'], 'unique_profile_pin');
            $table->unique(['post_id', 'pin_scope'], 'unique_home_pin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinned_posts');
    }
};
