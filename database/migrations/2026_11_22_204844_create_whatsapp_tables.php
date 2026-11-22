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
        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('engine', 50)->default('local'); // 'local' or 'textmebot'
            $table->string('api_key', 100)->unique();
            $table->string('local_server_url', 255)->default('http://127.0.0.1:3001');
            $table->string('textmebot_api_key', 255)->nullable();
            $table->string('textmebot_phone', 50)->nullable();
            $table->boolean('is_connected')->default(false);
            $table->string('connected_phone', 50)->nullable();
            $table->string('connected_name', 255)->nullable();
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamps();
        });

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_phone', 50)->index();
            $table->text('message');
            $table->string('status', 20)->default('pending')->index(); // 'sent', 'failed'
            $table->string('engine_used', 50)->default('local');
            $table->string('message_id', 150)->nullable();
            $table->text('error_message')->nullable();
            $table->text('response_data')->nullable();
            $table->string('sender_type', 50)->default('admin'); // 'admin', 'api', 'system'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_settings');
    }
};
