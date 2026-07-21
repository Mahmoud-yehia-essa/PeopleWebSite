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
        Schema::create('app_versions', function (Blueprint $table) {
           
            $table->id();
            
            $table->string('version', 255)->nullable(); // رقم الإصدار (مثال: 1.0.2 أو 2.1.0)
            $table->text('des')->nullable(); // وصف التحديث / ما الجديد في هذا الإصدار (What's New)
            
            $table->text('android')->nullable(); // رابط تحميل التطبيق على متجر Google Play
            $table->text('ios')->nullable(); // رابط تحميل التطبيق على متجر App Store
            
            // تحسين: تحويله إلى Boolean لمعرفة هل التحديث إجباري (1) أم اختياري (0)
            // هذا يساعد مطور التطبيق (Flutter / React Native) على إظهار نافذة منبثقة لا يمكن إغلاقها إلا بالتحديث
            $table->boolean('update_required')->default(false); 
            
            $table->text('contact')->nullable(); // بيانات التواصل أو الدعم الفني الخاصة بهذا الإصدار
            
            $table->timestamps(); // تاريخ الإعلان عن التحديث وتاريخ آخر تعديل

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
