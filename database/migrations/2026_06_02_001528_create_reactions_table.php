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
       Schema::create('reactions', function (Blueprint $table) {
            $table->id(); // bigint(20) AUTO_INCREMENT PRIMARY KEY
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->bigInteger('content_id'); // معرف المنشور أو التعليق
            $table->tinyInteger('content_type_id'); // 1 للمنشورات، 2 للتعليقات مثلاً
            // ربط مع جدول أنواع التفاعلات الذي أنشأناه بالأعلى
            $table->unsignedTinyInteger('reaction_type_id');
            $table->foreign('reaction_type_id')->references('id')->on('reaction_type')->onDelete('cascade');
            
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at لدعم الحذف المؤقت المتواجد بملفك
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
