<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('posts') && !Schema::hasColumn('posts', 'view_count')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->unsignedBigInteger('view_count')->default(0)->after('share_count');
            });
        }

        if (!Schema::hasTable('post_views')) {
            Schema::create('post_views', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('post_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('viewer_key', 64);
                $table->timestamp('viewed_at')->useCurrent();

                $table->unique(['post_id', 'viewer_key'], 'post_views_post_viewer_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('post_views');

        if (Schema::hasTable('posts') && Schema::hasColumn('posts', 'view_count')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('view_count');
            });
        }
    }
};
