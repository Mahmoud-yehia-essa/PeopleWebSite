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
        $existingIndexes = [];
        try {
            $indexes = DB::select("SHOW INDEX FROM `messages`");
            foreach ($indexes as $idx) {
                $existingIndexes[$idx->Key_name] = true;
            }
        } catch (\Throwable $e) {}

        Schema::table('messages', function (Blueprint $table) use ($existingIndexes) {
            if (!isset($existingIndexes['idx_messages_direct'])) {
                $table->index(['sender_id', 'receiver_id', 'id'], 'idx_messages_direct');
            }

            if (!isset($existingIndexes['idx_messages_reverse'])) {
                $table->index(['receiver_id', 'sender_id', 'id'], 'idx_messages_reverse');
            }

            if (Schema::hasColumn('messages', 'group_id') && !isset($existingIndexes['idx_messages_group'])) {
                $table->index(['group_id', 'id'], 'idx_messages_group');
            }

            if (Schema::hasColumn('messages', 'is_read') && !isset($existingIndexes['idx_messages_unread'])) {
                $table->index(['receiver_id', 'is_read'], 'idx_messages_unread');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $existingIndexes = [];
        try {
            $indexes = DB::select("SHOW INDEX FROM `messages`");
            foreach ($indexes as $idx) {
                $existingIndexes[$idx->Key_name] = true;
            }
        } catch (\Throwable $e) {}

        Schema::table('messages', function (Blueprint $table) use ($existingIndexes) {
            if (isset($existingIndexes['idx_messages_direct'])) {
                $table->dropIndex('idx_messages_direct');
            }
            if (isset($existingIndexes['idx_messages_reverse'])) {
                $table->dropIndex('idx_messages_reverse');
            }
            if (isset($existingIndexes['idx_messages_group'])) {
                $table->dropIndex('idx_messages_group');
            }
            if (isset($existingIndexes['idx_messages_unread'])) {
                $table->dropIndex('idx_messages_unread');
            }
        });
    }
};
