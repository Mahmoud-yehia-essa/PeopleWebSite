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
        if (!Schema::hasTable('chat_polls')) {
            Schema::create('chat_polls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained('messages')->onDelete('cascade');
                $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('question');
                $table->boolean('is_multiple_choice')->default(false);
                $table->unsignedInteger('total_votes')->default(0);
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->index('message_id');
                $table->index('group_id');
                $table->index('user_id');
            });
        }

        if (!Schema::hasTable('chat_poll_options')) {
            Schema::create('chat_poll_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_poll_id')->constrained('chat_polls')->onDelete('cascade');
                $table->string('option_uid', 100)->nullable();
                $table->text('text');
                $table->unsignedInteger('vote_count')->default(0);
                $table->timestamps();

                $table->index('chat_poll_id');
                $table->index('option_uid');
            });
        }

        if (!Schema::hasTable('chat_poll_votes')) {
            Schema::create('chat_poll_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_poll_id')->constrained('chat_polls')->onDelete('cascade');
                $table->foreignId('chat_poll_option_id')->constrained('chat_poll_options')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['chat_poll_id', 'chat_poll_option_id', 'user_id'], 'unique_user_chat_poll_option_vote');
                $table->index('chat_poll_id');
                $table->index('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_poll_votes');
        Schema::dropIfExists('chat_poll_options');
        Schema::dropIfExists('chat_polls');
    }
};
