<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\RequestGuard;
use App\Services\Auth\HybridSanctumGuard;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('Helpers/helpers.php'))) {
            require_once app_path('Helpers/helpers.php');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // تسجيل حارس المصادقة الهجين لـ Sanctum لدعم التوكنات ومعرفات المستخدمين القدامى معاً
        $requestGuardCreator = function ($config) {
            $auth = auth();
            return new RequestGuard(
                new HybridSanctumGuard($auth, config('sanctum.expiration'), $config['provider'] ?? null),
                request(),
                $auth->createUserProvider($config['provider'] ?? null)
            );
        };

        Auth::extend('sanctum', function ($app, $name, array $config) use ($requestGuardCreator) {
            return tap($requestGuardCreator($config), function ($guard) {
                app()->refresh('request', $guard, 'setRequest');
            });
        });
    }
}
