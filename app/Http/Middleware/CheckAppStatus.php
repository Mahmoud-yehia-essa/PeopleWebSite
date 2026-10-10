<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SystemSetting;

class CheckAppStatus
{
    /**
     * Handle an incoming API request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. المسارات المستثناة من فحص حالة التطبيق
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        // 2. التحقق من حالة التطبيق
        $isAppActive = SystemSetting::isAppActive();

        if (!$isAppActive) {
            $settings = SystemSetting::getSettings();
            $message = is_object($settings) && !empty($settings->app_maintenance_message)
                ? $settings->app_maintenance_message
                : 'التطبيق متوقف مؤقتاً لأعمال الصيانة والتطوير، يرجى المحاولة لاحقاً.';

            return response()->json([
                'success'        => false,
                'status'         => false,
                'maintenance'    => true,
                'is_maintenance' => true,
                'message'        => $message,
            ], 503);
        }

        return $next($request);
    }

    /**
     * المسارات المستثناة في الـ API
     */
    protected function shouldPassThrough(Request $request): bool
    {
        if ($request->is('api/system/status') || 
            $request->is('api/app-status') || 
            $request->is('api/system-status') || 
            $request->is('api/health') ||
            $request->is('api/broadcasting/*') ||
            $request->is('broadcasting/*')) {
            return true;
        }

        return false;
    }
}
