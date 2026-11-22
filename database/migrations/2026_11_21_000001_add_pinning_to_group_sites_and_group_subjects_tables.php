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
        Schema::table('group_sites', function (Blueprint $table) {
            if (!Schema::hasColumn('group_sites', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->after('status');
            }
            if (!Schema::hasColumn('group_sites', 'pin_order')) {
                $table->integer('pin_order')->default(0)->after('is_pinned');
            }
            if (!Schema::hasColumn('group_sites', 'pinned_at')) {
                $table->timestamp('pinned_at')->nullable()->after('pin_order');
            }
            $table->index(['is_pinned', 'pin_order', 'pinned_at'], 'idx_group_sites_pinning');
        });

        Schema::table('group_subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('group_subjects', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->after('attachment_path');
            }
            if (!Schema::hasColumn('group_subjects', 'pin_order')) {
                $table->integer('pin_order')->default(0)->after('is_pinned');
            }
            if (!Schema::hasColumn('group_subjects', 'pinned_at')) {
                $table->timestamp('pinned_at')->nullable()->after('pin_order');
            }
            $table->index(['group_site_id', 'is_pinned', 'pin_order', 'pinned_at'], 'idx_group_subjects_pinning');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_sites', function (Blueprint $table) {
            $table->dropIndex('idx_group_sites_pinning');
            $table->dropColumn(['is_pinned', 'pin_order', 'pinned_at']);
        });

        Schema::table('group_subjects', function (Blueprint $table) {
            $table->dropIndex('idx_group_subjects_pinning');
            $table->dropColumn(['is_pinned', 'pin_order', 'pinned_at']);
        });
    }
};
