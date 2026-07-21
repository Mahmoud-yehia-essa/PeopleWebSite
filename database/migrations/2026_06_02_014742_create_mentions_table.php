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
      Schema::create('mentions', function (Blueprint $table) {
            $table->id(); // int(11) AUTO_INCREMENT PRIMARY KEY
            $table->integer('content_type_id'); // نوع المحتوى المشيد إليه
            $table->integer('content_id');      // معرف المحتوى
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // المستخدم المشار إليه
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentions');
    }
};
