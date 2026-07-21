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
       Schema::create('dictionary', function (Blueprint $table) {
            $table->id(); // int(11) AUTO_INCREMENT PRIMARY KEY
            $table->string('key', 100)->unique('dictionary_key_unique');
            $table->text('en')->nullable();
            $table->text('ar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dictionary');
    }
};
