<?php

namespace App\Http\Controllers;

use App\Models\WiseCommittee;
use App\Models\User;
use App\Models\Post;
use App\Models\WiseSubjectRating;
use App\Models\WisePointLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WiseCommitteeController extends Controller
{
    /**
     * عرض شاشة غرفة اجتماع لجنة الحكماء وإدارتها
     */
    public function index()
    {
        // جلب الحكماء الحاليين
        $committeeMembers = WiseCommittee::with('user')->latest()->get();

        // جلب الأعضاء العاديين الذين لم يتم تعيينهم حكماء بعد
        $wiseUserIds = WiseCommittee::pluck('user_id')->toArray();
        $availableUsers = User::whereNotIn('id', $wiseUserIds)
                              ->where('role', 'user')
                              ->latest()
                              ->get();

        return view('admin.wise_committees.index', compact('committeeMembers', 'availableUsers'));
    }

    /**
     * تعيين عضو جديد في لجنة الحكماء
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:wise_committees,user_id',
            'specialty' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
        ], [
            'user_id.unique' => 'هذا العضو مسجل بالفعل في لجنة الحكماء.',
            'user_id.required' => 'يرجى اختيار العضو المطلوب.',
        ]);

        WiseCommittee::create([
            'user_id' => $request->user_id,
            'specialty' => $request->specialty,
            'bio' => $request->bio,
            'is_active' => true,
        ]);

        $notification = [
            'message' => 'تم تعيين العضو بنجاح في لجنة الحكماء وهو الآن حاضر في غرفة الاجتماع.',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * تفعيل أو تجميد عضوية حكيم في اللجنة
     */
    public function toggleStatus($id)
    {
        $member = WiseCommittee::findOrFail($id);
        $member->is_active = !$member->is_active;
        $member->save();

        $statusMessage = $member->is_active ? 'تنشيط عضوية الحكيم بنجاح.' : 'تجميد عضوية الحكيم مؤقتاً بنجاح.';
        
        $notification = [
            'message' => $statusMessage,
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * حذف حكيم من اللجنة وإخراجه من غرفة الاجتماع
     */
    public function destroy($id)
    {
        $member = WiseCommittee::findOrFail($id);
        $member->delete();

        $notification = [
            'message' => 'تم إنهاء عضوية الحكيم وإخراجه من لجنة الحكماء بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * عرض شاشة تقييم مواضيع الأعضاء من قبل الحكماء مع خيارات البحث والتصفية
     */
    public function postRatings(Request $request)
    {
        $query = Post::with(['user', 'wiseRatings.user'])->latest();

        // 1. تصفية بالبحث النصي عن موضوع معين
        if ($request->filled('search')) {
            $query->where('content', 'like', '%' . $request->search . '%');
        }

        // 2. تصفية بمستخدم معين لعرض مواضيعه
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 3. تصفية بالحكيم الذي قيم المواضيع
        if ($request->filled('wise_member_id')) {
            $query->whereHas('wiseRatings', function ($q) use ($request) {
                $q->where('user_id', $request->wise_member_id);
            });
        }

        // استخدام التقطيع البسيط (Simple Pagination) لتفادي ثقل تحميل الصفحة وعدم إجراء استعلام COUNT(*) المكلف
        $posts = $query->simplePaginate(10);

        // جلب قائمة الناشرين (المستخدمين الذين لديهم مواضيع منشورة) لفلترة البحث
        $publishers = User::where('role', 'user')
                          ->whereHas('posts')
                          ->get();

        // جلب قائمة الحكماء المضافين في اللجنة لفلترة البحث
        $wiseMembers = WiseCommittee::with('user')->get();

        // التحقق مما إذا كان المستخدم الحالي حكيم نشط في اللجنة
        $isWiseMember = WiseCommittee::where('user_id', Auth::id())
                                     ->where('is_active', true)
                                     ->exists();

        // جلب التقييمات الحالية للمستخدم الحالي لتمريرها للواجهة (إذا كان حكيماً)
        $myRatings = [];
        if ($isWiseMember) {
            $myRatings = WiseSubjectRating::where('user_id', Auth::id())
                                         ->pluck('rating', 'post_id')
                                         ->toArray();
        }

        // الاستجابة لطلبات الـ AJAX لدعم التحميل الكسول (Lazy Loading)
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.wise_committees.partials.post_cards', compact('posts', 'isWiseMember', 'myRatings'))->render(),
                'hasMore' => $posts->hasMorePages()
            ]);
        }

        return view('admin.wise_committees.ratings', compact('posts', 'isWiseMember', 'myRatings', 'publishers', 'wiseMembers'));
    }

    /**
     * حفظ أو تحديث تقييم الحكيم للموضوع
     */
    public function storeRating(Request $request)
    {
        // التحقق من أن المستخدم الحالي حكيم نشط
        $isWiseMember = WiseCommittee::where('user_id', Auth::id())
                                     ->where('is_active', true)
                                     ->exists();

        if (!$isWiseMember) {
            $notification = [
                'message' => 'عذراً، لا تمتلك الصلاحية لتقييم المواضيع. يجب أن تكون عضواً نشطاً في لجنة الحكماء.',
                'alert-type' => 'error'
            ];
            return redirect()->back()->with($notification);
        }

        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'rating' => 'required|numeric|min:1|max:10', // التقييم من 1 إلى 10
            'reason' => 'nullable|string',
        ]);

        // حفظ أو تعديل التقييم
        WiseSubjectRating::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'post_id' => $request->post_id
            ],
            [
                'rating' => $request->rating,
                'reason' => $request->reason
            ]
        );

        // إعادة حساب متوسط تقييمات الحكماء لهذا الموضوع وتحديث الحقل wise_rating في جدول posts
        $averageRating = WiseSubjectRating::where('post_id', $request->post_id)->avg('rating');
        
        $post = Post::findOrFail($request->post_id);
        $post->wise_rating = $averageRating;
        $post->save();

        $notification = [
            'message' => 'تم حفظ تقييمك للموضوع بنجاح وتحديث المعدل العام.',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * عرض سجل نقاط تقييم الأعضاء الممنوحة من لجنة الحكماء وإدارة التقييم المباشر للأعضاء
     */
    public function memberRatings(Request $request)
    {
        // 1. جلب الأعضاء (المستخدمين) مع إمكانية البحث والفلترة
        $membersQuery = User::where('role', 'user')->with('posts')->latest();
        
        if ($request->filled('member_search')) {
            $search = $request->member_search;
            $membersQuery->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $members = $membersQuery->paginate(10, ['*'], 'members_page');

        // 2. جلب سجل التقييمات مع الفلترة
        $logsQuery = WisePointLog::with(['wiseUser', 'recipientUser', 'post'])->latest();

        if ($request->filled('wise_user_id')) {
            $logsQuery->where('wise_user_id', $request->wise_user_id);
        }

        if ($request->filled('recipient_user_id')) {
            $logsQuery->where('recipient_user_id', $request->recipient_user_id);
        }

        $logs = $logsQuery->paginate(10, ['*'], 'logs_page');

        // جلب الحكماء الحاليين لتصفية فلتر البحث
        $wiseMembers = WiseCommittee::with('user')->get();
        
        // جلب جميع المستخدمين للتصفية في فلتر السجلات
        $allUsers = User::where('role', 'user')->get();

        // إحصائيات سريعة للواجهة
        $stats = [
            'total_points' => WisePointLog::sum('points_given'),
            'total_evaluations' => WisePointLog::count(),
            'top_recipient' => User::where('points', '>', 0)->orderBy('points', 'desc')->first(),
        ];

        // التحقق مما إذا كان المستخدم الحالي حكيم نشط في اللجنة
        $isWiseMember = WiseCommittee::where('user_id', Auth::id())
                                     ->where('is_active', true)
                                     ->exists();

        return view('admin.wise_committees.member_ratings', compact('members', 'logs', 'wiseMembers', 'allUsers', 'stats', 'isWiseMember'));
    }

    /**
     * منح نقاط لعضو من قبل لجنة الحكماء وتحديث رصيد نقاطه
     */
    public function storeMemberRating(Request $request)
    {
        // التحقق من أن المستخدم الحالي حكيم نشط أو مسؤول نظام (Admin/Owner)
        $currentUser = Auth::user();
        $isWiseMember = WiseCommittee::where('user_id', $currentUser->id)
                                     ->where('is_active', true)
                                     ->exists();

        $isAdmin = in_array($currentUser->role, ['admin', 'owner']);

        if (!$isWiseMember && !$isAdmin) {
            $notification = [
                'message' => 'عذراً، لا تمتلك الصلاحية لتقييم الأعضاء ومنح النقاط. يجب أن تكون عضواً نشطاً في لجنة الحكماء أو مسؤولاً.',
                'alert-type' => 'error'
            ];
            return redirect()->back()->with($notification);
        }

        $request->validate([
            'recipient_user_id' => 'required|exists:users,id',
            'post_id' => 'nullable|exists:posts,id',
            'points_given' => 'required|integer|min:1|max:100',
            'note' => 'nullable|string|max:255',
        ], [
            'recipient_user_id.required' => 'يجب تحديد العضو المراد تقييمه.',
            'points_given.required' => 'حقل النقاط مطلوب.',
            'points_given.integer' => 'يجب إدخال عدد صحيح للنقاط.',
            'points_given.min' => 'يجب منح نقطة واحدة على الأقل.',
            'points_given.max' => 'أقصى عدد نقاط يمكن منحه في العملية هو 100 نقطة.',
        ]);

        $recipientId = $request->recipient_user_id;

        // منع الحكيم من تقييم نفسه أو منح نقاط لنفسه
        if ($recipientId == $currentUser->id) {
            $notification = [
                'message' => 'عذراً، لا يمكنك منح نقاط أو تقييم نفسك.',
                'alert-type' => 'error'
            ];
            return redirect()->back()->with($notification);
        }

        // إذا تم إدخال منشور، نتأكد أنه ينتمي للمستخدم المتلقي
        if ($request->filled('post_id')) {
            $post = Post::find($request->post_id);
            if ($post && $post->user_id != $recipientId) {
                $notification = [
                    'message' => 'عذراً، هذا المنشور لا ينتمي للعضو المختار.',
                    'alert-type' => 'error'
                ];
                return redirect()->back()->with($notification);
            }
        }

        DB::beginTransaction();
        try {
            // 1. تسجيل السجل في جدول wise_point_logs
            WisePointLog::create([
                'wise_user_id' => $currentUser->id,
                'recipient_user_id' => $recipientId,
                'post_id' => $request->post_id ?: null, // حفظ نل إذا كان فارغاً
                'points_given' => $request->points_given,
                'note' => $request->note,
            ]);

            // 2. تحديث إجمالي نقاط العضو المتلقي في جدول users
            $recipient = User::findOrFail($recipientId);
            $recipient->increment('points', $request->points_given);

            DB::commit();

            $notification = [
                'message' => 'تم منح النقاط بنجاح وتحديث رصيد العضو.',
                'alert-type' => 'success'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $notification = [
                'message' => 'حدث خطأ غير متوقع أثناء منح النقاط: ' . $e->getMessage(),
                'alert-type' => 'error'
            ];
        }

        return redirect()->back()->with($notification);
    }

    /**
     * حذف تقييم نقاط عضو وخصم تلك النقاط من رصيده
     */
    public function destroyMemberRating($id)
    {
        $log = WisePointLog::findOrFail($id);

        DB::beginTransaction();
        try {
            // 1. خصم النقاط من رصيد العضو المتلقي
            $recipient = User::find($log->recipient_user_id);
            if ($recipient) {
                // التأكد من عدم هبوط رصيد النقاط لأقل من صفر
                $newPoints = max(0, $recipient->points - $log->points_given);
                $recipient->points = $newPoints;
                $recipient->save();
            }

            // 2. حذف السجل
            $log->delete();

            DB::commit();

            $notification = [
                'message' => 'تم حذف سجل التقييم بنجاح وخصم النقاط من رصيد العضو.',
                'alert-type' => 'success'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $notification = [
                'message' => 'حدث خطأ غير متوقع أثناء الحذف: ' . $e->getMessage(),
                'alert-type' => 'error'
            ];
        }

        return redirect()->back()->with($notification);
    }
}
