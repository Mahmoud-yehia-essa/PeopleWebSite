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
       Schema::create('hashtag_links', function (Blueprint $table) {
            $table->id(); // int(11) AUTO_INCREMENT PRIMARY KEY
            $table->foreignId('hashtag_id')->constrained('hashtags')->onDelete('cascade');
            $table->integer('content_id'); // معرف المحتوى (مثل id المنشور)
            $table->integer('content_type_id'); // نوع المحتوى
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hashtag_links');
    }
};
