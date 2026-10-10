<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;

class CheckWebsiteStatus
{
    /**
     * Handle an incoming web request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. استثناء مسارات لوحة التحكم وتسجيل الدخول والخروج والمسارات الحيوية
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        // 2. التحقق من حالة الموقع الإلكتروني
        $settings = SystemSetting::getSettings();
        $isWebsiteActive = (bool)($settings->is_website_active ?? true);

        if (!$isWebsiteActive) {
            // السماح لمدير النظام بتصفح ومعاينة الموقع بحرية
            if (Auth::check() && (Auth::user()->role === 'admin' || Auth::user()->role === 'superadmin')) {
                return $next($request);
            }

            $message = !empty($settings->website_maintenance_message)
                ? $settings->website_maintenance_message
                : 'الموقع متوقف مؤقتاً لأعمال الصيانة والتطوير، وسنعود للعمل قريباً.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success'        => false,
                    'status'         => false,
                    'maintenance'    => true,
                    'is_maintenance' => true,
                    'message'        => $message,
                ], 503);
            }

            return response()->view('errors.maintenance', [
                'maintenanceMessage' => $message,
                'isWebsite'          => true,
            ], 503);
        }

        return $next($request);
    }

    /**
     * فحص هل المسار مستثنى من وضع الصيانة
     */
    protected function shouldPassThrough(Request $request): bool
    {
        // مسارات لوحة التحكم
        if ($request->is('admin') || $request->is('admin/*') || $request->is('dashboard') || $request->is('dashboard/*')) {
            return true;
        }

        // مسارات تسجيل الدخول والخروج الخاصة بالإدارة والمصادقة
        if ($request->is('login') || $request->is('admin-login') || $request->is('logout') || $request->is('admin/logout')) {
            return true;
        }

        // مسارات الـ API (يتم فحصها بواسطة CheckAppStatus بشكل مستقل)
        if ($request->is('api') || $request->is('api/*')) {
            return true;
        }

        // مسارات التحقق والتواجد
        if ($request->is('up') || $request->is('live') || $request->is('broadcasting/*')) {
            return true;
        }

        return false;
    }
}
