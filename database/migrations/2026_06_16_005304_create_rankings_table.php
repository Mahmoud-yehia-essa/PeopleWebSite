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
        Schema::create('rankings', function (Blueprint $table) {
       $table->id();
            $table->string('rank_name', 255)->nullable(); // اسم الرتبة (مثال: محترف، مبتدئ)
            $table->text('rank_description')->nullable(); // وصف الرتبة
            $table->integer('rank_order')->nullable(); // ترتيب الرتبة تصاعدياً (1، 2، 3...)
            
            // نطاق النقاط المطلوبة للرتبة
            $table->integer('rank_start_point')->nullable(); // بداية النقاط (مثلاً: 1)
            $table->integer('rank_end_point')->nullable(); // نهاية النقاط (مثلاً: 100)
            
            $table->integer('level_reward_amount')->nullable(); // قيمة المكافأة المالية أو النقاط عند الوصول للرتبة
            $table->tinyInteger('is_last')->default(0); // تحديد ما إذا كانت هذه هي الرتبة الأخيرة في النظام (1 = نعم)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rankings');
    }
};
