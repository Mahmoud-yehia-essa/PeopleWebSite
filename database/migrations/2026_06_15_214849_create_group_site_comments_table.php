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
        Schema::create('group_site_comments', function (Blueprint $table) {
            $table->id(); // bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT
            
            // ربط التعليق بالموضوع (group_subjects)
            $table->foreignId('group_subject_id')
                  ->constrained('group_subjects')
                  ->onDelete('cascade'); // إذا حُذف الموضوع تُحذف تعليقاته تلقائياً
            
            // ربط التعليق بالمستخدم الذي كتبه (users)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // إذا حُذف المستخدم تُحذف تعليقاته تلقائياً
            
            $table->text('content')->nullable(); // text NULL
            
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
        Schema::dropIfExists('group_site_comments');
    }
};
