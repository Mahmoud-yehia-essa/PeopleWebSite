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
        Schema::create('wise_committees', function (Blueprint $table) {
          
            $table->id();
            
            // ربط الحكيم بجدول المستخدمين (كل حكيم هو مستخدم في الأصل)
            $table->foreignId('user_id')
                  ->unique() // لضمان عدم تكرار نفس المستخدم أكثر من مرة في اللجنة
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // تخصص الحكيم (مثال: أدبي، تقني، اجتماعي) لتوزيع المواضيع عليه لاحقاً حسب تخصصه
            $table->string('specialty', 255)->nullable(); 
            
            // نبذة مختصرة عن فكره ورأيه أو سبب ضمه للجنة
            $table->text('bio')->nullable(); 
            
            // حالة الحكيم (مفعل أو معطل) لكي يتمكن المدير من تجميد صلاحياته إذا لزم الأمر
            $table->boolean('is_active')->default(true); 
            
            $table->timestamps(); // تاريخ التعيين في لجنة الحكما


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wise_committees');
    }
};
