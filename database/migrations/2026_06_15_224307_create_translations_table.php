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
        Schema::create('translations', function (Blueprint $table) {
           

            $table->id();
            
            // ربط الترجمة بلغة معينة من جدول languages
            $table->foreignId('language_id')
                  ->constrained('languages')
                  ->onDelete('cascade'); // إذا حُذفت اللغة، تُحذف ترجماتها تلقائياً
            
            $table->string('key')->index(); // المفتاح البرمجي للنص (مثل: 'welcome_message' أو 'login_btn')
            $table->text('value'); // النص المترجم الفعلي باللغة المحددة
            
            $table->timestamps();

            // منع تكرار نفس المفتاح لنفس اللغة في قاعدة البيانات
            $table->unique(['language_id', 'key']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
