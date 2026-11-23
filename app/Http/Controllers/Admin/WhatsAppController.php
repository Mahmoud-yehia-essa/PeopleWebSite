<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * عرض الصفحة الرئيسية لقسم الواتساب
     */
    public function index()
    {
        $settings = $this->whatsappService->getSettings();
        $status = $this->whatsappService->getStatus();

        $messages = WhatsAppMessage::latest()->paginate(15);

        $totalSent = WhatsAppMessage::where('status', 'sent')->count();
        $totalFailed = WhatsAppMessage::where('status', 'failed')->count();
        $todaySent = WhatsAppMessage::where('status', 'sent')->whereDate('created_at', today())->count();

        return view('admin.whatsapp.index', compact(
            'settings',
            'status',
            'messages',
            'totalSent',
            'totalFailed',
            'todaySent'
        ));
    }

    /**
     * فحص الحالة الحية بشكل متكرر عبر AJAX
     */
    public function ajaxStatus()
    {
        $status = $this->whatsappService->getStatus();
        return response()->json($status);
    }

    /**
     * طلب كود الاقتران السريع (Pairing Code)
     */
    public function requestPairingCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ], [
            'phone.required' => 'يرجى إدخال رقم الهاتف مع كود الدولة.',
        ]);

        $result = $this->whatsappService->requestPairingCode($request->phone);
        return response()->json($result);
    }

    /**
     * إرسال رسالة تجريبية من لوحة التحكم
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'phone'   => 'required|string',
            'message' => 'required|string',
        ], [
            'phone.required'   => 'يرجى إدخال رقم هاتف المستلم مع رمز الدولة.',
            'message.required' => 'يرجى كتابة نص الرسالة.',
        ]);

        $result = $this->whatsappService->sendMessage($request->phone, $request->message, 'admin');

        if ($result['success']) {
            return redirect()->back()->with([
                'message'    => $result['message'],
                'alert-type' => 'success',
                'active_tab' => 'send_message',
            ]);
        }

        return redirect()->back()->with([
            'message'    => 'خطأ أثناء الإرسال: ' . $result['message'],
            'alert-type' => 'error',
            'active_tab' => 'send_message',
        ])->withInput();
    }

    /**
     * تشغيل خادم الواتساب يدوياً من لوحة التحكم
     */
    public function startService(Request $request)
    {
        $result = $this->whatsappService->startLocalService();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->back()->with([
            'message'    => $result['message'],
            'alert-type' => $result['success'] ? 'success' : 'error',
            'active_tab' => 'connect',
        ]);
    }

    /**
     * قطع الاتصال وإعادة ضبط الجلسة
     */
    public function disconnect()
    {
        $result = $this->whatsappService->disconnect();

        return redirect()->back()->with([
            'message'    => $result['message'],
            'alert-type' => 'success',
            'active_tab' => 'connect',
        ]);
    }

    /**
     * تحديث إعدادات المحرك
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'engine'             => 'required|in:local,textmebot',
            'local_server_url'   => 'nullable|url',
            'textmebot_api_key'  => 'nullable|string',
            'textmebot_phone'    => 'nullable|string',
        ]);

        $settings = $this->whatsappService->getSettings();
        $settings->update([
            'engine'            => $request->engine,
            'local_server_url'  => $request->local_server_url ?: 'http://127.0.0.1:3001',
            'textmebot_api_key' => $request->textmebot_api_key,
            'textmebot_phone'   => $request->textmebot_phone,
        ]);

        return redirect()->back()->with([
            'message'    => 'تم حفظ إعدادات الواتساب بنجاح.',
            'alert-type' => 'success',
            'active_tab' => 'settings',
        ]);
    }

    /**
     * تجديد مفتاح الـ API
     */
    public function regenerateApiKey()
    {
        $newKey = $this->whatsappService->regenerateApiKey();

        return redirect()->back()->with([
            'message'    => 'تم إنشاء مفتاح API جديد بنجاح: ' . $newKey,
            'alert-type' => 'success',
            'active_tab' => 'api_docs',
        ]);
    }

    /**
     * مسح سجل الرسائل القديمة
     */
    public function clearLogs()
    {
        WhatsAppMessage::truncate();

        return redirect()->back()->with([
            'message'    => 'تم مسح سجل رسائل الواتساب بالكامل.',
            'alert-type' => 'success',
            'active_tab' => 'logs',
        ]);
    }
}
