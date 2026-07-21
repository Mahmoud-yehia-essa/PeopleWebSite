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
        Schema::create('support_tickets', function (Blueprint $table) {
      $table->id();
            
            // المستخدم الذي فتح التذكرة
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->string('subject'); // عنوان التذكرة (مثال: مشكلة في شحن النقاط)
            
            // درجة الأهمية (منخفضة، متوسطة، عالية) تفيد الإدارة في ترتيب الأولويات
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            
            // حالة التذكرة (مفتوحة، جاري الرد، مغلقة)
            $table->enum('status', ['open', 'pending', 'closed'])->default('open');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
