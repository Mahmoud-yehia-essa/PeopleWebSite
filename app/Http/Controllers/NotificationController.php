<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\NotificationForApp;
use App\Services\FcmNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * عرض نموذج إرسال الإشعارات
     */
    public function create()
    {
        $usersWithToken = User::whereNotNull('token')
            ->where('token', '!=', '')
            ->orderBy('first_name', 'asc')
            ->get();

        $totalUsersCount = User::count();
        $usersWithTokenCount = $usersWithToken->count();

        return view('admin.notifications.create', compact(
            'usersWithToken',
            'totalUsersCount',
            'usersWithTokenCount'
        ));
    }

    /**
     * معالجة وحفظ وإرسال الإشعار للمستخدمين وتخزينه في جدول notification_for_apps
     * مع ضبط بادج الأيقونة الخارجية ليتطابق تماماً مع عدد الإشعارات غير المقروءة
     */
    public function store(Request $request, FcmNotificationService $fcmService)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'des'         => 'required|string',
            'link'        => 'nullable|url|max:2000',
            'target_type' => 'required|string|in:all,specific',
            'user_ids'    => 'required_if:target_type,specific|array',
            'user_ids.*'  => 'exists:users,id',
        ], [
            'title.required'       => 'يرجى إدخال عنوان الإشعار.',
            'title.max'            => 'عنوان الإشعار يجب ألا يتجاوز 255 حرفاً.',
            'des.required'         => 'يرجى كتابة نص وموضوع الإشعار.',
            'link.url'             => 'يرجى إدخال رابط صالح يبدأ بـ http:// أو https://',
            'target_type.required' => 'يرجى تحديد الفئة المستهدفة للإرسال.',
            'user_ids.required_if' => 'يرجى اختيار مستخدم واحد على الأقل عند اختيار مستخدمين محددين.',
        ]);

        // 1. تحديد المستخدمين المستهدفين لتخزين الإشعار في قاعدة البيانات لجميع المستخدمين
        if ($request->target_type === 'all') {
            $targetUsers = User::get(['id', 'token', 'first_name', 'last_name']);
        } else {
            $targetUsers = User::whereIn('id', $request->user_ids)
                ->get(['id', 'token', 'first_name', 'last_name']);
        }

        if ($targetUsers->isEmpty()) {
            return redirect()->back()->with([
                'message'    => 'لم يتم العثور على أي مستخدمين للإرسال إليهم.',
                'alert-type' => 'warning'
            ])->withInput();
        }

        $title = $request->title;
        $des = $request->des;
        $link = $request->link ? trim($request->link) : null;
        $currentDate = now()->toDateString();
        $now = now();

        $insertData = [];

        foreach ($targetUsers as $user) {
            $insertData[] = [
                'user_id'    => $user->id,
                'title'      => $title,
                'des'        => $des,
                'link'       => $link,
                'user_view'  => 'no',
                'date'       => $currentDate,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // إدراج السجلات في جدول notification_for_apps على دفعات
        foreach (array_chunk($insertData, 500) as $chunk) {
            NotificationForApp::insert($chunk);
        }

        // 2. إرسال الإشعار الفعلي عبر خدمة Firebase Cloud Messaging مع حساب البادج الدقيق لكل مستخدم
        $successCount = 0;
        $failureCount = 0;

        foreach ($targetUsers as $user) {
            if (!empty($user->token)) {
                // حساب إجمالي الإشعارات غير المقروءة الدقيقة لهذا المستخدم في النظام
                $unreadAdminCount = NotificationForApp::where(function($q) use ($user) {
                    $q->where('user_id', $user->id)->orWhereNull('user_id')->orWhere('user_id', 0);
                })->where(function($q) {
                    $q->where('user_view', 'no')->orWhere('user_view', '0')->orWhereNull('user_view');
                })->count();

                $unreadDbCount = DB::table('notifications')
                    ->where('notifiable_type', 'App\\Models\\User')
                    ->where('notifiable_id', $user->id)
                    ->whereNull('read_at')
                    ->count();

                $userTotalUnread = $unreadAdminCount + $unreadDbCount;
                if ($userTotalUnread < 1) {
                    $userTotalUnread = 1;
                }

                $extraData = [
                    'badge'        => (string)$userTotalUnread,
                    'unread_count' => (string)$userTotalUnread,
                ];
                if ($link) {
                    $extraData['link'] = $link;
                    $extraData['url'] = $link;
                }

                $fcmResult = $fcmService->sendToTokens([$user->token], $title, $des, $extraData, $userTotalUnread);
                $successCount += $fcmResult['success_count'];
                $failureCount += $fcmResult['failure_count'];
            }
        }

        $recordsCount = count($insertData);
        $msg = "تم تسجيل الإشعار في قاعدة البيانات بنجاح لـ ({$recordsCount}) مستخدم ومطابقة عداد البادج بدقة.";
        if ($successCount > 0) {
            $msg .= " وتم إرسال الإشعار السحابي (FCM) بنجاح إلى ({$successCount}) جهاز مع تحديث أيقونة التطبيق.";
        }
        if ($failureCount > 0) {
            $msg .= " (تعذر إرسال FCM إلى {$failureCount} جهاز لعدم صلاحية التوكن لديهم).";
        }

        $notification = [
            'message'    => $msg,
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }
}
