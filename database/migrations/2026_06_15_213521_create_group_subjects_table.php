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
        Schema::create('group_subjects', function (Blueprint $table) {
           
            $table->id(); // bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT
            
            // ربط الموضوع بالمستخدم الذي كتبه
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // إذا حُذف المستخدم تُحذف مواضيعه
            
            // ربط الموضوع بالمجموعة (groups_site)
            $table->foreignId('group_site_id')
                  ->constrained('group_sites') // تحديد اسم الجدول المخصص بدقة هنا
                  ->onDelete('cascade'); // إذا حُذفت المجموعة تُحذف مواضيعها
            
            $table->string('title', 255); // varchar(255)
            $table->text('description')->nullable(); // text NULL
            
            $table->unsignedInteger('likes')->default(0); // int(10) UNSIGNED DEFAULT 0
            $table->unsignedInteger('dislikes')->default(0); // int(10) UNSIGNED DEFAULT 0
            
            $table->enum('attachment_type', ['image', 'video', 'audio'])->nullable(); // enum(...) NULL
            $table->text('attachment_path')->nullable(); // text NULL
            
            $table->timestamps(); // created_at && updated_at


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_subjects');
    }
};
