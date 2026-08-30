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
        Schema::table("groups", function (Blueprint $table) {
            if (!Schema::hasColumn("groups", "pinned_message_id")) {
                $table->unsignedBigInteger("pinned_message_id")->nullable()->after("member_count");
            }
            if (!Schema::hasColumn("groups", "pinned_by_user_id")) {
                $table->unsignedBigInteger("pinned_by_user_id")->nullable()->after("pinned_message_id");
            }
            if (!Schema::hasColumn("groups", "pinned_at")) {
                $table->timestamp("pinned_at")->nullable()->after("pinned_by_user_id");
            }
            if (!Schema::hasColumn("groups", "pinned_until")) {
                $table->timestamp("pinned_until")->nullable()->after("pinned_at");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("groups", function (Blueprint $table) {
            $table->dropColumn(["pinned_message_id", "pinned_by_user_id", "pinned_at", "pinned_until"]);
        });
    }
};
