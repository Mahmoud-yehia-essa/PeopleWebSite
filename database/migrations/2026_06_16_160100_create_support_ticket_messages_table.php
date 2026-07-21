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
        Schema::create('support_ticket_messages', function (Blueprint $table) {
        $table->id();
            
            // ربط الرسالة بالتذكرة الرئيسية
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            
            // الشخص الذي كتب الرسالة الحالية
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            
            // نوع المرسل لتسهيل التلوين والتصميم في التطبيق والموقع (هل هو العضو أم الإدارة؟)
            $table->enum('sender_type', ['user', 'admin'])->default('user');
            
            $table->text('message'); // نص الرسالة أو الرد
            
            $table->text('attachment_path')->nullable(); // مسار لملف أو صورة مرفقة مع الرد (كإثبات مشكلة)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_messages');
    }
};
