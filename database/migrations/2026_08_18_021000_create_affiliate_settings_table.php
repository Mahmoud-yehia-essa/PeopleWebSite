<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('affiliate_settings')) {
            Schema::create('affiliate_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('reward_points_per_referral')->default(50);
                $table->unsignedInteger('min_points_silver_rank')->default(5);
                $table->unsignedInteger('min_points_gold_rank')->default(20);
                $table->boolean('is_affiliate_enabled')->default(true);
                $table->timestamps();
            });

            DB::table('affiliate_settings')->insert([
                'reward_points_per_referral' => 50,
                'min_points_silver_rank' => 5,
                'min_points_gold_rank' => 20,
                'is_affiliate_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_settings');
    }
};
