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
        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_website_active')->default(true)->comment('حالة الموقع: 1 = يعمل، 0 = متوقف للصيانة');
                $table->text('website_maintenance_message')->nullable()->comment('رسالة صيانة الموقع');
                $table->boolean('is_app_active')->default(true)->comment('حالة التطبيق: 1 = يعمل، 0 = متوقف للصيانة');
                $table->text('app_maintenance_message')->nullable()->comment('رسالة صيانة التطبيق');
                $table->timestamps();
            });

            DB::table('system_settings')->insert([
                'is_website_active'           => true,
                'website_maintenance_message' => 'الموقع متوقف مؤقتاً لأعمال الصيانة والتطوير، وسنعود للعمل قريباً.',
                'is_app_active'               => true,
                'app_maintenance_message'     => 'التطبيق متوقف مؤقتاً لأعمال الصيانة والتطوير، يرجى المحاولة لاحقاً.',
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
