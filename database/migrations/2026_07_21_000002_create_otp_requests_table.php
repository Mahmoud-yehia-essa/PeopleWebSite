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
        Schema::create('otp_requests', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 10)->default('+965');
            $table->string('phone_number', 30);
            $table->string('verification_id', 255)->unique();
            $table->string('flow_type', 20)->default('SMS');
            $table->string('status', 20)->default('PENDING'); // PENDING, VERIFIED, EXPIRED, FAILED
            $table->string('ip_address', 45)->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['country_code', 'phone_number'], 'idx_otp_country_phone');
            $table->index('verification_id', 'idx_otp_verification_id');
            $table->index('expires_at', 'idx_otp_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_requests');
    }
};
