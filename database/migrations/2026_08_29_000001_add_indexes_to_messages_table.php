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
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            
            // Compound indexes for ultra-fast 1-on-1 and group chat queries
            $table->index(['sender_id', 'receiver_id', 'id'], 'idx_messages_direct');
            $table->index(['receiver_id', 'sender_id', 'id'], 'idx_messages_reverse');
            
            if (Schema::hasColumn('messages', 'group_id')) {
                $table->index(['group_id', 'id'], 'idx_messages_group');
            }
            if (Schema::hasColumn('messages', 'is_read')) {
                $table->index(['receiver_id', 'is_read'], 'idx_messages_unread');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('idx_messages_direct');
            $table->dropIndex('idx_messages_reverse');
            if (Schema::hasColumn('messages', 'group_id')) {
                $table->dropIndex('idx_messages_group');
            }
            if (Schema::hasColumn('messages', 'is_read')) {
                $table->dropIndex('idx_messages_unread');
            }
        });
    }
};
