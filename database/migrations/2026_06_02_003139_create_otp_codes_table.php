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
       Schema::create('otp_codes', function (Blueprint $table) {
            $table->id(); // int(11) AUTO_INCREMENT PRIMARY KEY
            $table->string('phone_number', 200);
            $table->string('code', 6);
            $table->dateTime('expires_at');
            $table->tinyInteger('used')->default(0); // 0 غير مستخدم، 1 مستخدم
            $table->timestamps();

            // إضافة الفهرسة لتسريع عمليات البحث برقم الهاتف كالمتواجد في الـ Indexes بملفك
            $table->index('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
