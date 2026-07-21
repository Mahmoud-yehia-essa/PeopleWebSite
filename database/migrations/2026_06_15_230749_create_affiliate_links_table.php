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
        Schema::create('affiliate_links', function (Blueprint $table) {
           $table->id();
            
            // المستخدم (المسوق) صاحب الرابط
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // كود الإحالة الفريد (مثال: alex77)
            $table->string('code')->unique(); 
            
            // عداد النقرات على الرابط
            $table->unsignedInteger('clicks')->default(0); 
            
            // حالة الرابط (مفعل أو معطل)
            $table->boolean('is_active')->default(true); 
            
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_links');
    }
};
