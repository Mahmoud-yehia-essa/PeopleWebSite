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
      Schema::create('reports', function (Blueprint $table) {
            $table->id(); // int(11) AUTO_INCREMENT PRIMARY KEY
            $table->foreignId('reported_by_id')->constrained('users')->onDelete('cascade'); // المستخدم المبلّغ
            $table->foreignId('report_type_id')->constrained('report_type')->onDelete('cascade');
            $table->foreignId('report_reasons_id')->constrained('report_reasons')->onDelete('cascade');
            $table->integer('target_id'); // معرف الشيء المبلغ عنه (مستخدم، منشور، تعليق)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
