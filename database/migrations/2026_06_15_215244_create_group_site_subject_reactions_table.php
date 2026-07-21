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
        Schema::create('group_site_subject_reactions', function (Blueprint $table) {
           

            $table->id(); // bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT
            
            // ربط التفاعل بالموضوع (group_subjects)
            $table->foreignId('group_subject_id')
                  ->constrained('group_subjects')
                  ->onDelete('cascade');
            
            // ربط التفاعل بالمستخدم (users)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            $table->enum('type', ['like', 'dislike']); // enum('like','dislike')
            
            $table->timestamps(); // created_at && updated_at

            // لمسة احترافية: تمنع تكرار تفاعل نفس المستخدم على نفس الموضوع في قاعدة البيانات
            $table->unique(['user_id', 'group_subject_id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_site_subject_reactions');
    }
};
