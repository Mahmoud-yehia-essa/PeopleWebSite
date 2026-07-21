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
        Schema::table('messages', function (Blueprint $table) {
            // Add nullable group_id column after receiver_id
            $table->foreignId('group_id')->nullable()->after('receiver_id')->constrained('groups')->onDelete('cascade');
            
            // Make receiver_id nullable since group messages don't have a single receiver
            $table->foreignId('receiver_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
            $table->foreignId('receiver_id')->nullable(false)->change();
        });
    }
};
