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
       Schema::create('chats', function (Blueprint $table) {
            $table->id(); // int(11) AUTO_INCREMENT PRIMARY KEY
            
            // ربط حقول المرسل والمستقبل بجدول المستخدمين
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            
            $table->text('message')->nullable();
            $table->string('media', 512)->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->tinyInteger('is_viewed')->default(0); // 0 غير مقروءة، 1 مقروءة
            
            $table->timestamps(); // ينشئ تلقائياً created_at و updated_at كـ timestamp
            $table->softDeletes(); // ينشئ deleted_at لدعم الحذف المؤقت المتواجد بملفك
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
