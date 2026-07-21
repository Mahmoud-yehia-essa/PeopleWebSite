<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('facebook_id')->nullable()->after('google_id');
        });

        // تعديل الـ enum لإضافة 'facebook'
        DB::statement("ALTER TABLE users MODIFY COLUMN provider ENUM('normal', 'phone', 'google', 'apple', 'facebook') DEFAULT 'normal'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('facebook_id');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN provider ENUM('normal', 'phone', 'google', 'apple') DEFAULT 'normal'");
    }
};
