<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AffiliateLink;
use App\Models\AffiliateTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffiliateController extends Controller
{
    /**
     * توجيه الزائر عن طريق كود الإحالة القصير
     */
    public function redirectReferral($code)
    {
        $link = AffiliateLink::where('code', $code)->where('is_active', true)->first();
        if (!$link) {
            abort(404, 'كود الإحالة غير صالح أو غير نشط.');
        }

        session(['affiliate_ref' => $code]);
        
        // زيادة عداد النقرات مرة واحدة لكل جلسة
        $clickedKey = 'affiliate_clicked_' . $link->id;
        if (!session()->has($clickedKey)) {
            $link->increment('clicks');
            session([$clickedKey => true]);
        }

        $marketer = $link->user;

        return view('auth.affiliate_join', compact('link', 'marketer'));
    }

    /**
     * عرض جميع روابط الإحالة
     */
    public function allAffiliates()
    {
        // جلب الروابط مع معلومات المسوق وعدد التسجيلات الفعلي
        $links = AffiliateLink::with(['user'])
            ->withCount('trackings')
            ->latest()
            ->get();

        return view('admin.affiliate.all_affiliates', compact('links'));
    }

    /**
     * شاشة إضافة رابط إحالة جديد
     */
    public function addAffiliate()
    {
        // جلب المستخدمين الذين لا يملكون رابط إحالة حالياً
        $userIdsWithLinks = AffiliateLink::pluck('user_id')->toArray();
        $users = User::whereNotIn('id', $userIdsWithLinks)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        return view('admin.affiliate.add_affiliate', compact('users'));
    }

    /**
     * حفظ رابط الإحالة الجديد
     */
    public function storeAffiliate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:affiliate_links,user_id',
            'code' => 'nullable|string|max:100|unique:affiliate_links,code',
        ], [
            'user_id.required' => 'يرجى اختيار المستخدم المسوق.',
            'user_id.unique' => 'هذا المستخدم يمتلك بالفعل رابط تسويق بالعمولة.',
            'code.unique' => 'كود الإحالة هذا مستخدم بالفعل، يرجى اختيار كود آخر.',
        ]);

        $user = User::findOrFail($request->user_id);
        $code = $request->code;

        if (empty($code)) {
            $code = AffiliateLink::generateUniqueCode($user);
        } else {
            // تنظيف الكود ليكون آمن ومناسب للروابط
            $code = strtolower(trim($code));
            $code = preg_replace('/[^a-z0-9-_]/', '', $code);
            
            // تحقق ثان للتأكد بعد التنظيف
            if (empty($code)) {
                $code = AffiliateLink::generateUniqueCode($user);
            }
        }

        AffiliateLink::create([
            'user_id' => $user->id,
            'code' => $code,
            'is_active' => $request->has('is_active') ? true : false,
            'clicks' => 0
        ]);

        $notification = [
            'message' => 'تم إنشاء رابط التسويق بالعمولة بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.affiliates')->with($notification);
    }

    /**
     * شاشة تعديل رابط الإحالة
     */
    public function editAffiliate($id)
    {
        $link = AffiliateLink::with('user')->findOrFail($id);
        return view('admin.affiliate.edit_affiliate', compact('link'));
    }

    /**
     * تحديث بيانات رابط الإحالة
     */
    public function updateAffiliate(Request $request)
    {
        $id = $request->id;
        $link = AffiliateLink::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:100|unique:affiliate_links,code,' . $link->id,
        ], [
            'code.required' => 'يرجى إدخال كود الإحالة.',
            'code.unique' => 'كود الإحالة هذا مستخدم بالفعل، يرجى اختيار كود آخر.',
        ]);

        // تنظيف الكود
        $code = strtolower(trim($request->code));
        $code = preg_replace('/[^a-z0-9-_]/', '', $code);

        if (empty($code)) {
            return redirect()->back()->withInput()->with([
                'message' => 'كود الإحالة غير صالح بعد تنظيفه من الرموز الخاصة.',
                'alert-type' => 'error'
            ]);
        }

        $link->code = $code;
        $link->is_active = $request->has('is_active') ? true : false;
        $link->save();

        $notification = [
            'message' => 'تم تحديث رابط التسويق بالعمولة بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.affiliates')->with($notification);
    }

    /**
     * حذف رابط الإحالة والتتبعات التابعة له
     */
    public function deleteAffiliate($id)
    {
        $link = AffiliateLink::findOrFail($id);
        $link->delete();

        $notification = [
            'message' => 'تم حذف رابط التسويق بالعمولة وجميع بيانات التتبع التابعة له بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * جلب قائمة المسجلين عبر رابط إحالة معين لعرضهم بالمودال عبر AJAX
     */
    public function getTrackings($id)
    {
        $link = AffiliateLink::findOrFail($id);
        
        $trackings = AffiliateTracking::with(['registeredUser'])
            ->where('affiliate_link_id', $id)
            ->latest()
            ->get();

        $data = $trackings->map(function ($tracking) {
            $user = $tracking->registeredUser;
            return [
                'name' => $user ? ($user->first_name . ' ' . $user->last_name) : 'مستخدم محذوف',
                'email' => $user ? $user->email : '-',
                'ip' => $tracking->ip_address ?? '-',
                'date' => $tracking->created_at->format('Y-m-d H:i:s'),
                'date_human' => $tracking->created_at->diffForHumans()
            ];
        });

        return response()->json([
            'success' => true,
            'code' => $link->code,
            'trackings' => $data
        ]);
    }

    /**
     * عرض سجل الإحالات والتسجيلات بالكامل لمراقبة الـ IP والغش
     */
    public function allTrackings()
    {
        $trackings = AffiliateTracking::with(['link.user', 'registeredUser'])
            ->latest()
            ->get();

        return view('admin.affiliate.all_trackings', compact('trackings'));
    }

    /**
     * حذف سجل تتبع
     */
    public function deleteTracking($id)
    {
        $tracking = AffiliateTracking::findOrFail($id);
        $tracking->delete();

        $notification = [
            'message' => 'تم حذف سجل التتبع بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * عرض صفحة سفراء الحكمة للواجهة الأمامية للمستخدم الحالي
     */
    public function frontendAmbassadors()
    {
        $userId = auth()->id();
        
        // جلب أو إنشاء رابط الإحالة تلقائياً
        $link = AffiliateLink::where('user_id', $userId)->first();
        if (!$link) {
            $code = AffiliateLink::generateUniqueCode(auth()->user());
            $link = AffiliateLink::create([
                'user_id' => $userId,
                'code' => $code,
                'clicks' => 0,
                'is_active' => true
            ]);
        }

        // جلب الإحصائيات
        $clicksCount = $link->clicks ?: 0;
        
        $trackingsQuery = AffiliateTracking::where('affiliate_link_id', $link->id);
        $referralsCount = $trackingsQuery->count();

        // حساب نقاط المكافأة (على سبيل المثال: 50 نقطة لكل دعوة ناجحة)
        $rewardPoints = $referralsCount * 50;

        // رتبة السفير
        if ($referralsCount >= 20) {
            $rank = 'سفير ذهبي';
        } elseif ($referralsCount >= 5) {
            $rank = 'سفير فضي';
        } else {
            $rank = 'سفير برونزي';
        }

        // جلب الأعضاء المنضمين مؤخراً عبر رابط المستخدم
        $recentReferrals = AffiliateTracking::where('affiliate_link_id', $link->id)
            ->with('registeredUser')
            ->latest()
            ->limit(10)
            ->get();

        return view('frontend.wiselook.pages.ambassadors', compact(
            'link',
            'clicksCount',
            'referralsCount',
            'rewardPoints',
            'rank',
            'recentReferrals'
        ));
    }

    /**
     * تحديث كود الإحالة المخصص للمستخدم من الواجهة الأمامية
     */
    public function updateReferralCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:100',
        ], [
            'code.required' => 'يرجى إدخال كود الإحالة المخصص.',
        ]);

        $userId = auth()->id();
        $link = AffiliateLink::where('user_id', $userId)->firstOrFail();

        // تنظيف الكود
        $code = strtolower(trim($request->code));
        $code = preg_replace('/[^a-z0-9-_]/', '', $code);

        if (empty($code)) {
            return redirect()->back()->with([
                'message' => 'الكود المخصص غير صالح بعد تنظيفه من الرموز الخاصة.',
                'alert-type' => 'error'
            ]);
        }

        // التحقق من عدم التكرار
        $exists = AffiliateLink::where('code', $code)->where('id', '!=', $link->id)->exists();
        if ($exists) {
            return redirect()->back()->with([
                'message' => 'كود الإحالة هذا مستخدم بالفعل، يرجى اختيار كود آخر.',
                'alert-type' => 'error'
            ]);
        }

        $link->code = $code;
        $link->save();

        return redirect()->back()->with([
            'message' => 'تم تحديث كود الإحالة المخصص بنجاح.',
            'alert-type' => 'success'
        ]);
    }
}
