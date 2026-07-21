<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    /**
     * عرض قائمة إصدارات التطبيق
     */
    public function index()
    {
        $versions = AppVersion::latest()->paginate(10);
        return view('admin.app_version.index', compact('versions'));
    }

    /**
     * عرض نموذج إضافة إصدار جديد
     */
    public function create()
    {
        return view('admin.app_version.add');
    }

    /**
     * حفظ الإصدار الجديد في قاعدة البيانات
     */
    public function store(Request $request)
    {
        $request->validate([
            'version' => 'required|string|max:255',
            'des' => 'nullable|string',
            'android' => 'nullable|string',
            'ios' => 'nullable|string',
            'update_required' => 'nullable|boolean',
            'contact' => 'nullable|string',
        ], [
            'version.required' => 'يرجى إدخال رقم الإصدار.',
        ]);

        AppVersion::create([
            'version' => $request->version,
            'des' => $request->des,
            'android' => $request->android,
            'ios' => $request->ios,
            'update_required' => $request->has('update_required'),
            'contact' => $request->contact,
        ]);

        $notification = [
            'message' => 'تم إضافة إصدار التطبيق الجديد بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->route('admin.app_versions.index')->with($notification);
    }

    /**
     * عرض نموذج تعديل الإصدار
     */
    public function edit($id)
    {
        $version = AppVersion::findOrFail($id);
        return view('admin.app_version.edit', compact('version'));
    }

    /**
     * تحديث بيانات الإصدار في قاعدة البيانات
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:app_versions,id',
            'version' => 'required|string|max:255',
            'des' => 'nullable|string',
            'android' => 'nullable|string',
            'ios' => 'nullable|string',
            'update_required' => 'nullable|boolean',
            'contact' => 'nullable|string',
        ], [
            'version.required' => 'يرجى إدخال رقم الإصدار.',
        ]);

        $version = AppVersion::findOrFail($request->id);
        $version->update([
            'version' => $request->version,
            'des' => $request->des,
            'android' => $request->android,
            'ios' => $request->ios,
            'update_required' => $request->has('update_required'),
            'contact' => $request->contact,
        ]);

        $notification = [
            'message' => 'تم تحديث بيانات إصدار التطبيق بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->route('admin.app_versions.index')->with($notification);
    }

    /**
     * حذف الإصدار من قاعدة البيانات
     */
    public function destroy($id)
    {
        $version = AppVersion::findOrFail($id);
        $version->delete();

        $notification = [
            'message' => 'تم حذف إصدار التطبيق بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }
}
