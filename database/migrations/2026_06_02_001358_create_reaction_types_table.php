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
      Schema::create('reaction_type', function (Blueprint $table) {
            $table->tinyIncrements('id'); // tinyint(4) AUTO_INCREMENT PRIMARY KEY
            $table->string('type', 20)->unique(); // مثل: like, love, haha
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reaction_types');
    }
};
