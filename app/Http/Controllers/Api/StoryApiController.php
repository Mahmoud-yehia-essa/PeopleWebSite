<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Story;
use App\Models\StorySeen;
use App\Models\Friendship;

class StoryApiController extends Controller
{
    /**
     * 4.1 جلب القصص الحالية (قصص المستخدم وأصدقائه النشطين)
     */
    public function listStories(Request $request)
    {
        $currentUser = $request->user();

        // 1. جلب قائمة معرفات الأصدقاء المشتركين والنشطين من جدول friendships
        $friendIds = Friendship::where('is_active', 1)
            ->where(function($q) use ($currentUser) {
                $q->where('sender_id', $currentUser->id)
                  ->orWhere('receiver_id', $currentUser->id);
            })
            ->get()
            ->map(function($f) use ($currentUser) {
                return $f->sender_id == $currentUser->id ? $f->receiver_id : $f->sender_id;
            })
            ->toArray();

        // دمج معرف المستخدم الحالي لعرض قصصه الشخصية ضمن الخلاصة أيضاً
        $allowedUserIds = array_merge([$currentUser->id], $friendIds);

        // 2. جلب القصص النشطة (is_active = 1) والتي لم تنتهِ صلاحيتها الـ 24 ساعة بعد
        $stories = Story::with('user')
            ->whereIn('user_id', $allowedUserIds)
            ->where('is_active', 1)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. إعادة تشكيل البيانات (Mapping) وتجميعها حسب المستخدم لتطابق الـ JSON المطلوبة للـ Flutter
        $grouped = $stories->groupBy('user_id');

        $formattedUsers = [];
        foreach ($grouped as $userId => $userStories) {
            $firstStory = $userStories->first();
            $user = $firstStory->user;

            if (!$user) {
                continue;
            }

            $formattedStories = $userStories->map(function($story) use ($currentUser) {
                $mediaUrl = '';
                $mediaType = 'image';

                // تحديد رابط ونوع الميديا
                if ($story->image) {
                    $mediaUrl = asset('storage/' . $story->image);
                    $mediaType = 'image';
                } elseif ($story->video) {
                    $mediaUrl = asset('storage/' . $story->video);
                    $mediaType = 'video';
                }

                $hasSeen = StorySeen::where('story_id', $story->id)
                    ->where('user_id', $currentUser->id)
                    ->where('is_active', 1)
                    ->exists();

                return [
                    'id'          => (int)$story->id,
                    'media'       => $mediaUrl,
                    'type'        => $mediaType,
                    'created_at'  => $story->created_at ? $story->created_at->toDateTimeString() : '',
                    'is_active'   => (int)$story->is_active,
                    'is_seen'     => $hasSeen ? 1 : 0,
                    'time_ago'    => $story->created_at ? $story->created_at->diffForHumans() : '',
                    'view_count'  => (int)$story->view_count
                ];
            })->values()->toArray();

            $formattedUsers[] = [
                'user_id'         => (int)$user->id,
                'first_name'      => $user->first_name ?? '',
                'last_name'       => $user->last_name ?? '',
                'profile_picture' => $user->profile_picture ?: asset('images/default_profile.png'),
                'stories'         => $formattedStories
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $formattedUsers
        ]);
    }

    /**
     * 4.2 مشاهدة وتسجيل رؤية القصة
     */
    public function markAsSeen(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'story_id' => 'required|integer|exists:stories,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();

        // تسجيل المشاهدة داخل جدول story_seen لمنع التكرار
        $seen = StorySeen::where('story_id', $request->story_id)
            ->where('user_id', $currentUser->id)
            ->first();

        if (!$seen) {
            StorySeen::create([
                'story_id' => $request->story_id,
                'user_id'  => $currentUser->id,
                'viewed_at'=> now(),
                'is_active'=> 1
            ]);

            // زيادة عداد المشاهدات الرقمي بداخل جدول القصص الرئيسي لسرعة العرض الفوري عند وجود مشاهدة جديدة فقط
            Story::where('id', $request->story_id)->increment('view_count');
        } else {
            $seen->update([
                'viewed_at' => now(),
                'is_active' => 1
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Story marked as seen'
        ]);
    }
}