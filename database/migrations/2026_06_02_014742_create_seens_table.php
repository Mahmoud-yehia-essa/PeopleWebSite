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
       Schema::create('seen', function (Blueprint $table) {
            $table->id(); // int(11) AUTO_INCREMENT PRIMARY KEY
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('notification_id', 255);
            $table->enum('notification_type', ['friend_request', 'post_like', 'post_comment', 'post_share']);
            $table->timestamp('seen_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            // الفهارس المتقدمة لسرعة الاستعلام كما هي بالـ Dump الخاص بك
            $table->unique(['user_id', 'notification_id', 'notification_type'], 'unique_seen');
            $table->index(['user_id', 'notification_id', 'notification_type'], 'idx_notification_lookup');
            $table->index('user_id', 'idx_user_id');
            $table->index('notification_type', 'idx_notification_type');
            $table->index('seen_at', 'idx_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seens');
    }
};
