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
        Schema::table('group_site_comments', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->default(0)->after('user_id');
            $table->unsignedInteger('reaction_count')->default(0)->after('content');
            $table->unsignedInteger('reply_count')->default(0)->after('reaction_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_site_comments', function (Blueprint $table) {
            $table->dropColumn(['parent_id', 'reaction_count', 'reply_count']);
        });
    }
};
