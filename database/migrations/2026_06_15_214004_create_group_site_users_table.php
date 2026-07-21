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
        Schema::create('group_site_users', function (Blueprint $table) {
           $table->id(); // bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT
            
            // ربط المستخدم
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // إذا حُذف المستخدم يخرج تلقائياً من المجموعة
            
            // ربط المجموعة (بجدول groups_site)
            $table->foreignId('group_site_id')
                  ->constrained('group_sites')
                  ->onDelete('cascade'); // إذا حُذفت المجموعة يُحذف سجل الانضمام
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_site_users');
    }
};
