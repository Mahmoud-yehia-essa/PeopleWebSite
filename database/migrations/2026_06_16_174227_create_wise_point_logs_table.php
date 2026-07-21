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
        Schema::create('wise_point_logs', function (Blueprint $table) {
            $table->id();
            
            // 1. معرف الحكيم الذي أعطى الدرجات/النقاط
            $table->foreignId('wise_user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // 2. معرف المستخدم (العضو) الذي استلم الدرجات/النقاط
            $table->foreignId('recipient_user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // 3. معرف المنشور الذي بسببه مُنحت الدرجات (مرتبط بجدول posts)
            $table->foreignId('post_id')
                  ->nullable()
                  ->constrained('posts')
                  ->onDelete('cascade');
            
            // 4. عدد الدرجات/النقاط الممنوحة في هذه العملية (مثلاً: 5 نقاط أو 10 نقاط)
            $table->integer('points_given'); 
            
            // 5. ملاحظة أو سبب منح هذه الدرجات (مثال: أسلوب طرح متميز، فكرة مبتكرة)
            $table->string('note')->nullable(); 
            
            $table->timestamps(); // تاريخ ووقت منح الدرجات
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wise_point_logs');
    }
};
