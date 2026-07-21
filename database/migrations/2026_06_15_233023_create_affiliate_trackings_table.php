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
        Schema::create('affiliate_trackings', function (Blueprint $table) {
           $table->id();
            
            // ربطه بمعرف رابط الأفيليت
            $table->foreignId('affiliate_link_id')->constrained('affiliate_links')->onDelete('cascade');
            
            // المستخدم الجديد الذي سجل في الموقع عن طريق الرابط
            $table->foreignId('registered_user_id')->constrained('users')->onDelete('cascade');
            
            // عنوان الـ IP الخاص بالمسجل الجديد لحمايتك من الغش والتسجيلات المتكررة
            $table->string('ip_address')->nullable(); 
            
            $table->timestamps(); // يتضمن تاريخ وقت التسجيل بدقة
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_trackings');
    }
};
