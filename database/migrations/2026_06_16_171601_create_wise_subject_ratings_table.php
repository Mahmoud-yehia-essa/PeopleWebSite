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
        Schema::create('wise_subject_ratings', function (Blueprint $table) {
           
$table->id();
            
            // معرف الحكيم الذي قام بالتقييم
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // التعديل هنا: ربط التقييم بجدول الـ posts مباشرة
            $table->foreignId('post_id')
                  ->constrained('posts') // اسم جدولك الحالي
                  ->onDelete('cascade');
            
            // معدل التقييم (درجة من 1 إلى 10 مثلاً أو نسبة مئوية)
            $table->decimal('rating', 5, 2); 
            
            // وصف أو مبرر التقييم من جانب الحكيم
            $table->text('reason')->nullable(); 
            
            $table->timestamps(); 

            // منع الحكيم من تقييم نفس المنشور أو الموضوع مرتين
            $table->unique(['user_id', 'post_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wise_subject_ratings');
    }
};
