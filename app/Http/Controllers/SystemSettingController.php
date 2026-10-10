<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemSetting;

class SystemSettingController extends Controller
{
    /**
     * عرض شاشة التحكم بحالة الموقع والتطبيق
     */
    public function index()
    {
        $settings = SystemSetting::getSettings();
        return view('admin.settings.system_settings', compact('settings'));
    }

    /**
     * حفظ وتحديث إعدادات حالة الموقع والتطبيق ورسائل الصيانة
     */
    public function update(Request $request)
    {
        $request->validate([
            'website_maintenance_message' => 'nullable|string|max:2000',
            'app_maintenance_message'     => 'nullable|string|max:2000',
        ]);

        $settings = SystemSetting::first();
        if (!$settings) {
            $settings = new SystemSetting();
        }

        $settings->is_website_active = $request->has('is_website_active');
        $settings->is_app_active     = $request->has('is_app_active');

        if ($request->filled('website_maintenance_message')) {
            $settings->website_maintenance_message = $request->website_maintenance_message;
        }

        if ($request->filled('app_maintenance_message')) {
            $settings->app_maintenance_message = $request->app_maintenance_message;
        }

        $settings->save();
        SystemSetting::clearCache();

        $notification = [
            'message'    => 'تم حفظ وتحديث إعدادات حالة الموقع والتطبيق بنجاح',
            'alert-type' => 'success'
        ];

        return redirect()->route('admin.system_settings.index')->with($notification);
    }

    /**
     * تبديل سريع لحالة الموقع عبر AJAX
     */
    public function toggleWebsite(Request $request)
    {
        $settings = SystemSetting::first();
        if (!$settings) {
            $settings = new SystemSetting();
            $settings->is_app_active = true;
        }

        $status = $request->has('status') ? filter_var($request->status, FILTER_VALIDATE_BOOLEAN) : !$settings->is_website_active;
        $settings->is_website_active = $status;
        $settings->save();
        SystemSetting::clearCache();

        return response()->json([
            'success'           => true,
            'is_website_active' => $settings->is_website_active,
            'status'            => $settings->is_website_active ? 'active' : 'maintenance',
            'message'           => $settings->is_website_active ? 'تم تشغيل الموقع بنجاح' : 'تم إيقاف الموقع مؤقتاً للصيانة'
        ]);
    }

    /**
     * تبديل سريع لحالة التطبيق عبر AJAX
     */
    public function toggleApp(Request $request)
    {
        $settings = SystemSetting::first();
        if (!$settings) {
            $settings = new SystemSetting();
            $settings->is_website_active = true;
        }

        $status = $request->has('status') ? filter_var($request->status, FILTER_VALIDATE_BOOLEAN) : !$settings->is_app_active;
        $settings->is_app_active = $status;
        $settings->save();
        SystemSetting::clearCache();

        return response()->json([
            'success'       => true,
            'is_app_active' => $settings->is_app_active,
            'status'        => $settings->is_app_active ? 'active' : 'maintenance',
            'message'       => $settings->is_app_active ? 'تم تشغيل التطبيق بنجاح' : 'تم إيقاف التطبيق مؤقتاً للصيانة'
        ]);
    }

    /**
     * API عام لفحص حالة الموقع والتطبيق من تطبيقات الموبايل والمستخدمين
     */
    public function statusApi()
    {
        $settings = SystemSetting::getSettings();

        return response()->json([
            'success' => true,
            'website' => [
                'is_active'           => (bool)($settings->is_website_active ?? true),
                'status'              => ($settings->is_website_active ?? true) ? 'active' : 'maintenance',
                'maintenance_message' => $settings->website_maintenance_message ?? 'الموقع متوقف مؤقتاً لأعمال الصيانة والتطوير، وسنعود للعمل قريباً.',
            ],
            'app' => [
                'is_active'           => (bool)($settings->is_app_active ?? true),
                'status'              => ($settings->is_app_active ?? true) ? 'active' : 'maintenance',
                'maintenance_message' => $settings->app_maintenance_message ?? 'التطبيق متوقف مؤقتاً لأعمال الصيانة والتطوير، يرجى المحاولة لاحقاً.',
            ],
        ]);
    }
}
