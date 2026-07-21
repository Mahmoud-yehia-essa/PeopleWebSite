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
        Schema::create('languages', function (Blueprint $table) {
            $table->id(); 
            $table->string('name', 100); // اسم اللغة (العربية، English)
            $table->string('code', 10)->unique(); // كود اللغة (ar, en)
            $table->string('flag_path')->nullable(); // مسار أيقونة أو صورة العلم (تُعرض في التطبيق والموقع)
            
            // حقول أساسية لتهيئة واجهات التطبيق والموقع معاً
            $table->enum('direction', ['rtl', 'ltr'])->default('ltr'); // اتجاه النص (مهم جداً لقلب تصميم التطبيق والموقع)
            $table->boolean('is_default')->default(false); // اللغة الافتراضية للنظام كاملاً
            $table->boolean('is_active')->default(true); // حالة تفعيل اللغة في (الـ API والـ Web)
            
            $table->timestamps(); // تاريخ الإنشاء والتعليق
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
