<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * عرض نموذج إرسال الإشعارات
     */
    public function create()
    {
        // جلب جميع المستخدمين المسجلين بالترتيب الأبجدي لاختيارهم في النموذج
        $users = User::orderBy('first_name', 'asc')->get();
        return view('admin.notifications.create', compact('users'));
    }

    /**
     * محاكاة معالجة وإرسال الإشعار (بدون إرسال فعلي حسب الطلب)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,zip|max:10240', // 10MB limit
            'target_type' => 'required|string|in:all,specific',
            'user_ids' => 'required_if:target_type,specific|array',
            'user_ids.*' => 'exists:users,id',
        ], [
            'title.required' => 'يرجى إدخال عنوان الإشعار.',
            'title.max' => 'عنوان الإشعار يجب ألا يتجاوز 255 حرفاً.',
            'body.required' => 'يرجى كتابة نص وموضوع الإشعار.',
            'attachment.file' => 'الملف المرفق غير صالح.',
            'attachment.mimes' => 'صيغ المرفقات المسموح بها هي: jpg, jpeg, png, pdf, docx, zip.',
            'attachment.max' => 'حجم المرفق يجب ألا يتجاوز 10 ميجابايت.',
            'target_type.required' => 'يرجى تحديد الفئة المستهدفة للإرسال.',
            'user_ids.required_if' => 'يرجى اختيار مستخدم واحد على الأقل عند اختيار مستخدمين محددين.',
        ]);

        // نقوم بمحاكاة النجاح دون أي إرسال فعلي كما طلب المستخدم
        $notification = [
            'message' => 'تمت محاكاة إرسال الإشعار بنجاح! (لم يتم إرساله فعلياً بناءً على طلبك).',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }
}
