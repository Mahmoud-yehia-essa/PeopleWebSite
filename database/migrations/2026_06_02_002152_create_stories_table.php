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
        Schema::create('stories', function (Blueprint $table) {
            $table->id(); // int(11) AUTO_INCREMENT PRIMARY KEY
            
            // ربط القصة بالمستخدم الذي نشرها
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->text('content')->nullable();
            $table->string('image', 255)->nullable();
            $table->string('video', 255)->nullable();
            $table->integer('view_count')->default(0);
            $table->dateTime('expires_at')->nullable(); // وقت اختفاء الستوري تلقائياً
            $table->integer('is_active')->default(1);
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
