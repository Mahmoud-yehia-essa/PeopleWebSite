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
        Schema::create('group_sites', function (Blueprint $table) {
          $table->id(); 
            $table->string('title', 255); 
            $table->text('description')->nullable(); 
            $table->text('image_path')->nullable(); 
            $table->enum('status', ['open', 'closed'])->default('open'); 
            $table->string('invite_code', 255)->nullable(); 
            
            // ربط معرف المشرف بجدول المستخدمين
            $table->foreignId('admin_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null'); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_sites');
    }
};
