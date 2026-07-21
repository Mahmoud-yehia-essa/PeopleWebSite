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
       Schema::create('phone_verifications', function (Blueprint $table) {
            $table->id(); // int(11) AUTO_INCREMENT PRIMARY KEY
            $table->string('verification_id', 255)->unique();
            $table->string('phone_number', 255);
            $table->string('otp_code', 6);
            $table->dateTime('expires_at');
            $table->tinyInteger('used')->default(0);
            $table->tinyInteger('verified')->default(0); // 0 لم يوثق، 1 تم التوثيق بنجاح
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();

            // الفهارس المخصصة (Indexes) بناءً على بنية قاعدة بياناتك القديمة لسرعة الفحص الصارم
            $table->index('verification_id', 'idx_verification_id');
            $table->index('phone_number', 'idx_phone_number');
            $table->index('expires_at', 'idx_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_verifications');
    }
};
