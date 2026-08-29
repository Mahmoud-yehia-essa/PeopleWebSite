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
            try {
                if (method_exists(Schema::class, 'hasIndex') && !Schema::hasIndex('messages', 'idx_messages_direct')) {
                    $table->index(['sender_id', 'receiver_id', 'id'], 'idx_messages_direct');
                } elseif (!method_exists(Schema::class, 'hasIndex')) {
                    $table->index(['sender_id', 'receiver_id', 'id'], 'idx_messages_direct');
                }
            } catch (\Throwable $e) {}

            try {
                if (method_exists(Schema::class, 'hasIndex') && !Schema::hasIndex('messages', 'idx_messages_reverse')) {
                    $table->index(['receiver_id', 'sender_id', 'id'], 'idx_messages_reverse');
                } elseif (!method_exists(Schema::class, 'hasIndex')) {
                    $table->index(['receiver_id', 'sender_id', 'id'], 'idx_messages_reverse');
                }
            } catch (\Throwable $e) {}

            try {
                if (Schema::hasColumn('messages', 'group_id')) {
                    if (method_exists(Schema::class, 'hasIndex') && !Schema::hasIndex('messages', 'idx_messages_group')) {
                        $table->index(['group_id', 'id'], 'idx_messages_group');
                    } elseif (!method_exists(Schema::class, 'hasIndex')) {
                        $table->index(['group_id', 'id'], 'idx_messages_group');
                    }
                }
            } catch (\Throwable $e) {}

            try {
                if (Schema::hasColumn('messages', 'is_read')) {
                    if (method_exists(Schema::class, 'hasIndex') && !Schema::hasIndex('messages', 'idx_messages_unread')) {
                        $table->index(['receiver_id', 'is_read'], 'idx_messages_unread');
                    } elseif (!method_exists(Schema::class, 'hasIndex')) {
                        $table->index(['receiver_id', 'is_read'], 'idx_messages_unread');
                    }
                }
            } catch (\Throwable $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            try {
                $table->dropIndex('idx_messages_direct');
            } catch (\Throwable $e) {}
            try {
                $table->dropIndex('idx_messages_reverse');
            } catch (\Throwable $e) {}
            try {
                if (Schema::hasColumn('messages', 'group_id')) {
                    $table->dropIndex('idx_messages_group');
                }
            } catch (\Throwable $e) {}
            try {
                if (Schema::hasColumn('messages', 'is_read')) {
                    $table->dropIndex('idx_messages_unread');
                }
            } catch (\Throwable $e) {}
        });
    }
};
