<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Friendship;
use App\Models\User;
use App\Models\Block;

class FriendApiController extends Controller
{
    /**
     * 5.1 جلب قائمة الأصدقاء أو طلبات الصداقة المعلقة
     */
    public function listFriends(Request $request)
    {
        $currentUser = $request->user();
        
        // دعم استقبال المدخلات الهجينة تماشياً مع كود الـ Native القديم
        $targetUserId = $request->input('person_id', $request->input('id', $currentUser->id));
        $isActive = $request->input('is_active', 1); // 1 للأصدقاء المقبولين، 0 للطلبات المعلقة
        $filterType = $request->input('filter_type'); // 'sent' لمعرفة الطلبات المعلقة التي أرسلتها أنا
        
        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);

        $userIds = [];

        if ($isActive == 1) {
            // أصدقاء حقيقيين ونشطين (تجميع الطرف الآخر في العلاقة)
            $friendships = Friendship::where('is_active', 1)
                ->where(function($q) use ($targetUserId) {
                    $q->where('sender_id', $targetUserId)
                      ->orWhere('receiver_id', $targetUserId);
                })
                ->skip($offset)
                ->take($limit)
                ->get();

            foreach ($friendships as $f) {
                $userIds[] = ($f->sender_id == $targetUserId) ? $f->receiver_id : $f->sender_id;
            }
        } else {
            // طلبات الصداقة المعلقة والمرفوضة قبل القبول
            if ($filterType === 'sent') {
                // الطلبات الصادرة مني للشخص الآخر ولم يُجب عليها بعد
                $userIds = Friendship::where('sender_id', $targetUserId)
                    ->where('is_active', 0)
                    ->skip($offset)
                    ->take($limit)
                    ->pluck('receiver_id')
                    ->toArray();
            } else {
                // الطلبات الواردة إلي من أشخاص آخرين وتنتظر موافقتي
                $userIds = Friendship::where('receiver_id', $targetUserId)
                    ->where('is_active', 0)
                    ->skip($offset)
                    ->take($limit)
                    ->pluck('sender_id')
                    ->toArray();
            }
        }

        // جلب بيانات الحسابات المستهدفة بالـ IDs المستخرجة
        $users = User::whereIn('id', $userIds)->get();

        // صياغة المخرجات بدقة لمطابقة واجهة تطبيق الموبايل
        $formattedData = $users->map(function($user) use ($currentUser) {
            // فحص هل هذا الحساب يعتبر صديقاً فعلياً للمستخدم الحالي (Bearer Token)
            $isFriendCheck = Friendship::where('is_active', 1)
                ->where(function($q) use ($currentUser, $user) {
                    $q->where('sender_id', $currentUser->id)->where('receiver_id', $user->id);
                })->orWhere(function($q) use ($currentUser, $user) {
                    $q->where('sender_id', $user->id)->where('receiver_id', $currentUser->id);
                })->exists();

            return [
                'id'              => (int)$user->id,
                'first_name'      => $user->first_name,
                'last_name'       => $user->last_name,
                'profile_picture' => $user->profile_picture,
                'isFriend'        => $isFriendCheck,
                'type'            => 'friend'
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $formattedData
        ]);
    }

    /**
     * 5.2 محرك إجراءات الصداقة الموحد
     */
    public function friendAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'        => 'required|string|in:add,accept,reject,block,unfriend',
            'receiver_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();
        $receiverId = $request->receiver_id;

        // جودة برمجية: منع المستخدم من التفاعل الاجتماعي مع نفسه
        if ($currentUser->id == $receiverId) {
            return response()->json(['success' => false, 'message' => 'Cannot perform social actions on yourself'], 400);
        }

        switch ($request->type) {
            case 'add':
                // إرسال طلب صداقة جديد (التأكد من عدم وجود علاقة قائمة مسبقاً)
                $exists = Friendship::where(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $currentUser->id)->where('receiver_id', $receiverId);
                })->orWhere(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $receiverId)->where('receiver_id', $currentUser->id);
                })->first();

                if ($exists) {
                    return response()->json(['success' => false, 'message' => 'Friendship relation or request already exists'], 400);
                }

                Friendship::create([
                    'sender_id'   => $currentUser->id,
                    'receiver_id' => $receiverId,
                    'is_active'   => 0 // 0 تعني طلب معلق قيد الانتظار
                ]);
                break;

            case 'accept':
                // قبول طلب صداقة وارد إليك حتماً
                $friendship = Friendship::where('sender_id', $receiverId)
                    ->where('receiver_id', $currentUser->id)
                    ->where('is_active', 0)
                    ->first();

                if (!$friendship) {
                    return response()->json(['success' => false, 'message' => 'No pending friend request found to accept'], 404);
                }

                $friendship->update(['is_active' => 1]);

                // تحديث عدادات حقل الأصدقاء الفوري لكلا الطرفين بجدول users لسرعة استعلامات الـ Profile
                User::where('id', $currentUser->id)->increment('friend_count');
                User::where('id', $receiverId)->increment('friend_count');

                // إضافة إشعار قبول طلب الصداقة للمرسل
                \Illuminate\Support\Facades\DB::table('notifications')->insert([
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'type' => 'App\Notifications\GeneralNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $receiverId,
                    'data' => json_encode([
                        'type' => 'friend_accept',
                        'sender_id' => $currentUser->id,
                        'sender_name' => $currentUser->first_name . ' ' . $currentUser->last_name,
                        'avatar' => $currentUser->profile_picture,
                        'message' => 'وافق ' . $currentUser->first_name . ' ' . $currentUser->last_name . ' على طلب الصداقة الخاص بك.',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                break;

            case 'reject':
                // رفض أو إلغاء طلب صداقة معلق وارد إليك
                $deleted = Friendship::where('sender_id', $receiverId)
                    ->where('receiver_id', $currentUser->id)
                    ->where('is_active', 0)
                    ->delete();

                if (!$deleted) {
                    return response()->json(['success' => false, 'message' => 'No pending request found to reject'], 404);
                }
                break;

            case 'unfriend':
                // إنهاء وإلغاء صداقة نشطة قائمة بين طرفين
                $friendship = Friendship::where(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $currentUser->id)->where('receiver_id', $receiverId);
                })->orWhere(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $receiverId)->where('receiver_id', $currentUser->id);
                })->where('is_active', 1)->first();

                if (!$friendship) {
                    return response()->json(['success' => false, 'message' => 'No active friendship found to terminate'], 404);
                }

                $friendship->delete();

                // إنقاص العدادات الرقمية للأصدقاء
                User::where('id', $currentUser->id)->where('friend_count', '>', 0)->decrement('friend_count');
                User::where('id', $receiverId)->where('friend_count', '>', 0)->decrement('friend_count');
                break;

            case 'block':
                // حظر مستخدم (مسح وحذف الصداقة والطلبات القائمة فوراً إن وجدت وتثبيت الحظر بجدول block بالمفرد)
                Friendship::where(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $currentUser->id)->where('receiver_id', $receiverId);
                })->orWhere(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $receiverId)->where('receiver_id', $currentUser->id);
                })->delete();

                // تسجيل قيد الحظر الصارم بجدول block المعتمد بملف قاعدة بياناتك
                Block::updateOrCreate([
                    'blocker_id' => $currentUser->id,
                    'blocked_id' => $receiverId
                ]);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Friend social action processed successfully'
        ]);
    }
}