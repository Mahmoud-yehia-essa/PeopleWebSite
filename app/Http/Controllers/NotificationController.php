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
            ->get(['id', 'first_name', 'last_name', 'email', 'phone_number', 'profile_picture', 'token']);

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
     * مع إرسال دفعي فوري وسريع (Multicast) يمنع استهلاك الذاكرة أو انقطاع الاتصال 500
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

        try {
            $title = $request->title;
            $des = $request->des;
            $link = $request->link ? trim($request->link) : null;
            $currentDate = now()->toDateString();
            $now = now();

            // 1. تحديد المستخدمين المستهدفين المؤهلين لاستقبال الإشعار على هواتفهم
            if ($request->target_type === 'all') {
                $targetUsers = User::whereNotNull('token')
                    ->where('token', '!=', '')
                    ->get(['id', 'token', 'first_name', 'last_name']);
            } else {
                $targetUsers = User::whereIn('id', $request->user_ids)
                    ->get(['id', 'token', 'first_name', 'last_name']);
            }

            if ($targetUsers->isEmpty()) {
                if ($request->target_type === 'all') {
                    // في حال لم يكن هناك أي مستخدم لديه توكن، يتم حفظ إشعار عام broadcast
                    NotificationForApp::create([
                        'user_id'    => null,
                        'title'      => $title,
                        'des'        => $des,
                        'link'       => $link,
                        'user_view'  => 'no',
                        'date'       => $currentDate,
                    ]);

                    return redirect()->back()->with([
                        'message'    => 'تم حفظ الإشعار العام في قاعدة البيانات بنجاح، ولكن لا يوجد حالياً مستخدمون لديهم رمز إشعار (FCM Token) فعال للإرسال السحابي.',
                        'alert-type' => 'warning'
                    ]);
                }

                return redirect()->back()->with([
                    'message'    => 'لم يتم العثور على أي مستخدمين للإرسال إليهم.',
                    'alert-type' => 'warning'
                ])->withInput();
            }

            // 2. تخزين السجلات في جدول notification_for_apps لكل مستخدم مستهدف
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

            foreach (array_chunk($insertData, 500) as $chunk) {
                NotificationForApp::insert($chunk);
            }

            // 3. جمع توكنات الأجهزة وإرسال الإشعار السحابي (FCM) دفعة واحدة Multicast
            $tokens = $targetUsers->pluck('token')
                ->filter(function ($t) {
                    return !empty($t) && is_string($t);
                })
                ->unique()
                ->values()
                ->toArray();

            $successCount = 0;
            $failureCount = 0;

            if (!empty($tokens)) {
                $extraData = [];
                if ($link) {
                    $extraData['link'] = $link;
                    $extraData['url'] = $link;
                }

                // في حالة مستخدم واحد محدد، نحسب عداد البادج الدقيق له
                if ($targetUsers->count() === 1) {
                    $singleUser = $targetUsers->first();
                    $unreadAdminCount = NotificationForApp::where(function ($q) use ($singleUser) {
                        $q->where('user_id', $singleUser->id)->orWhereNull('user_id')->orWhere('user_id', 0);
                    })->where(function ($q) {
                        $q->where('user_view', 'no')->orWhere('user_view', '0')->orWhereNull('user_view');
                    })->count();

                    $unreadDbCount = DB::table('notifications')
                        ->where('notifiable_type', 'App\\Models\\User')
                        ->where('notifiable_id', $singleUser->id)
                        ->whereNull('read_at')
                        ->count();

                    $userTotalUnread = max(1, $unreadAdminCount + $unreadDbCount);
                    $extraData['badge'] = (string)$userTotalUnread;
                    $extraData['unread_count'] = (string)$userTotalUnread;
                    $badgeCount = $userTotalUnread;
                } else {
                    $badgeCount = 1;
                    $extraData['badge'] = '1';
                    $extraData['unread_count'] = '1';
                }

                $fcmResult = $fcmService->sendToTokens($tokens, $title, $des, $extraData, $badgeCount);
                $successCount = $fcmResult['success_count'] ?? 0;
                $failureCount = $fcmResult['failure_count'] ?? 0;
            }

            $recordsCount = count($insertData);
            $msg = "تم تسجيل الإشعار في قاعدة البيانات بنجاح لـ ({$recordsCount}) مستخدم.";
            if ($successCount > 0) {
                $msg .= " وتم إرسال الإشعار السحابي (FCM) بنجاح إلى ({$successCount}) جهاز فورياً.";
            }
            if ($failureCount > 0) {
                $msg .= " (تعذر إرسال FCM إلى {$failureCount} جهاز لعدم صلاحية التوكن لديهم).";
            }

            $notification = [
                'message'    => $msg,
                'alert-type' => 'success'
            ];

            return redirect()->back()->with($notification);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Notification send error: ' . $e->getMessage());
            return redirect()->back()->with([
                'message'    => 'حدث خطأ أثناء إرسال الإشعار: ' . $e->getMessage(),
                'alert-type' => 'error'
            ])->withInput();
        }
    }
}
