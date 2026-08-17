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
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = \App\Models\User::find($request->input('user_id'));
        }

        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

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
                $thumbnailUrl = '';
                $mediaType = 'text';

                // تحديد رابط ونوع الميديا
                if ($story->video) {
                    if (filter_var($story->video, FILTER_VALIDATE_URL)) {
                        $mediaUrl = $story->video;
                    } elseif (str_starts_with($story->video, 'upload/')) {
                        $mediaUrl = asset($story->video);
                    } elseif (str_contains($story->video, 'stories/')) {
                        $mediaUrl = asset('upload/' . ltrim($story->video, '/'));
                    } else {
                        $mediaUrl = asset('upload/stories/' . $story->video);
                    }
                    $mediaType = 'video';

                    if ($story->image) {
                        $thumbnailUrl = filter_var($story->image, FILTER_VALIDATE_URL)
                            ? $story->image
                            : asset('upload/stories/' . basename($story->image));
                    }
                } elseif ($story->image) {
                    if (filter_var($story->image, FILTER_VALIDATE_URL)) {
                        $mediaUrl = $story->image;
                    } elseif (str_starts_with($story->image, 'upload/')) {
                        $mediaUrl = asset($story->image);
                    } elseif (str_contains($story->image, 'stories/')) {
                        $mediaUrl = asset('upload/' . ltrim($story->image, '/'));
                    } else {
                        $mediaUrl = asset('upload/stories/' . $story->image);
                    }
                    $mediaType = 'image';
                    $thumbnailUrl = $mediaUrl;
                }

                $hasSeen = StorySeen::where('story_id', $story->id)
                    ->where('user_id', $currentUser->id)
                    ->where('is_active', 1)
                    ->exists();

                return [
                    'id'          => (int)$story->id,
                    'media'       => $mediaUrl,
                    'thumbnail'   => $thumbnailUrl,
                    'type'        => $mediaType,
                    'content'     => $story->content ?? '',
                    'created_at'  => $story->created_at ? $story->created_at->toDateTimeString() : '',
                    'is_active'   => (int)$story->is_active,
                    'is_seen'     => $hasSeen ? 1 : 0,
                    'time_ago'    => $story->created_at ? $story->created_at->diffForHumans() : '',
                    'view_count'  => (int)$story->view_count
                ];
            })->values()->toArray();

            $profilePic = '';
            if ($user && $user->profile_picture && $user->profile_picture !== 'non') {
                $profilePic = filter_var($user->profile_picture, FILTER_VALIDATE_URL)
                    ? $user->profile_picture
                    : asset('new_wiselook/uploads/' . basename($user->profile_picture));
            } else {
                $profilePic = asset('images/default_profile.png');
            }

            $formattedUsers[] = [
                'user_id'         => (int)$user->id,
                'first_name'      => $user->first_name ?? '',
                'last_name'       => $user->last_name ?? '',
                'profile_picture' => $profilePic,
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
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = \App\Models\User::find($request->input('user_id'));
        }

        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

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

    /**
     * 4.3 إضافة قصة جديدة (نص اختياري + صورة أو فيديو)
     */
    public function addStory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string|max:500',
            'media'   => 'nullable|file|max:51200',
            'image'   => 'nullable|file|max:51200',
            'video'   => 'nullable|file|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = \App\Models\User::find($request->input('user_id'));
        }

        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        if (empty($request->content) && !$request->hasFile('media') && !$request->hasFile('image') && !$request->hasFile('video')) {
            return response()->json(['success' => false, 'message' => 'يجب إدخال نص أو رفع صورة/فيديو للقصة.'], 422);
        }

        try {
            $story = new Story();
            $story->user_id = $currentUser->id;
            $story->content = $request->content;
            $story->view_count = 0;
            $story->is_active = 1;
            $story->expires_at = now()->addDay();

            $file = $request->file('media') ?? $request->file('image') ?? $request->file('video');
            if ($file) {
                $mimeType = $file->getMimeType() ?: '';
                $clientExt = strtolower($file->getClientOriginalExtension() ?: '');
                $isVideo = str_contains($mimeType, 'video') || in_array($clientExt, ['mp4', 'mov', 'avi', 'mkv', 'webm', '3gp', 'm4v']);

                $destinationPath = public_path('upload/stories');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $extension = $clientExt ?: ($isVideo ? 'mp4' : 'jpg');
                $filename = date('YmdHis') . '_' . uniqid() . '.' . $extension;
                $file->move($destinationPath, $filename);

                if ($isVideo) {
                    $story->video = $filename;

                    // Generate thumbnail from first part of video (fail-safe)
                    $thumbFilename = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg';
                    $videoPath = $destinationPath . '/' . $filename;
                    $thumbPath = $destinationPath . '/' . $thumbFilename;
                    
                    if (function_exists('exec')) {
                        $ffmpegPaths = ['/opt/homebrew/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/usr/bin/ffmpeg', 'ffmpeg'];
                        foreach ($ffmpegPaths as $ffmpeg) {
                            try {
                                if ($ffmpeg === 'ffmpeg' || (file_exists($ffmpeg) && is_executable($ffmpeg))) {
                                    @\exec("{$ffmpeg} -y -i " . escapeshellarg($videoPath) . " -ss 00:00:00.200 -vframes 1 -q:v 2 " . escapeshellarg($thumbPath) . " 2>&1");
                                    if (!file_exists($thumbPath) || filesize($thumbPath) === 0) {
                                        @\exec("{$ffmpeg} -y -i " . escapeshellarg($videoPath) . " -vframes 1 -q:v 2 " . escapeshellarg($thumbPath) . " 2>&1");
                                    }
                                    if (file_exists($thumbPath) && filesize($thumbPath) > 0) {
                                        $story->image = $thumbFilename;
                                        break;
                                    }
                                }
                            } catch (\Throwable $th) {
                                // ignore thumbnail generation errors safely
                            }
                        }
                    }
                } else {
                    $story->image = $filename;
                }
            }

            $story->save();

            $mediaUrl = '';
            $mediaType = 'text';
            if ($story->image) {
                $mediaUrl = asset('upload/stories/' . $story->image);
                $mediaType = 'image';
            } elseif ($story->video) {
                $mediaUrl = asset('upload/stories/' . $story->video);
                $mediaType = 'video';
            }

            return response()->json([
                'success' => true,
                'message' => 'Story added successfully',
                'story'   => [
                    'id'          => (int)$story->id,
                    'media'       => $mediaUrl,
                    'type'        => $mediaType,
                    'content'     => $story->content ?? '',
                    'created_at'  => $story->created_at ? $story->created_at->toDateTimeString() : '',
                    'is_active'   => (int)$story->is_active,
                    'is_seen'     => 0,
                    'time_ago'    => $story->created_at ? $story->created_at->diffForHumans() : '',
                    'view_count'  => 0
                ]
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to save story: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Failed to save story: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 4.4 جلب قائمة مشاهدي القصة
     */
    public function getStoryViewers(Request $request)
    {
        $storyId = $request->input('story_id') ?? $request->route('id');

        $validator = Validator::make(['story_id' => $storyId], [
            'story_id' => 'required|integer|exists:stories,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $viewers = StorySeen::with(['user'])
            ->where('story_id', $storyId)
            ->latest('viewed_at')
            ->get();

        $formattedViewers = $viewers->map(function($view) {
            $user = $view->user;
            $profilePic = asset('images/default_profile.png');
            if ($user && $user->profile_picture && $user->profile_picture !== 'non') {
                $profilePic = filter_var($user->profile_picture, FILTER_VALIDATE_URL)
                    ? $user->profile_picture
                    : asset('new_wiselook/uploads/' . $user->profile_picture);
            }

            return [
                'user_id'         => $user ? (int)$user->id : 0,
                'user_name'       => $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : 'مستخدم غير معروف',
                'first_name'      => $user->first_name ?? '',
                'last_name'       => $user->last_name ?? '',
                'profile_picture' => $profilePic,
                'viewed_at'       => $view->viewed_at ? $view->viewed_at->diffForHumans() : ''
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $formattedViewers,
            'viewers' => $formattedViewers
        ]);
    }

    /**
     * 4.5 حذف قصة
     */
    public function deleteStory(Request $request)
    {
        $storyId = $request->input('story_id') ?? $request->route('id');

        $validator = Validator::make(['story_id' => $storyId], [
            'story_id' => 'required|integer|exists:stories,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = \App\Models\User::find($request->input('user_id'));
        }

        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $story = Story::find($storyId);
        if (!$story) {
            return response()->json(['success' => false, 'message' => 'Story not found'], 404);
        }

        if ($story->user_id != $currentUser->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            if ($story->image && !filter_var($story->image, FILTER_VALIDATE_URL)) {
                $imgPath = public_path('upload/stories/' . $story->image);
                if (file_exists($imgPath)) {
                    @unlink($imgPath);
                }
                \Illuminate\Support\Facades\Storage::disk('public')->delete($story->image);
            }

            if ($story->video && !filter_var($story->video, FILTER_VALIDATE_URL)) {
                $vidPath = public_path('upload/stories/' . $story->video);
                if (file_exists($vidPath)) {
                    @unlink($vidPath);
                }
                \Illuminate\Support\Facades\Storage::disk('public')->delete($story->video);
            }

            StorySeen::where('story_id', $story->id)->delete();
            $story->delete();

            return response()->json([
                'success' => true,
                'message' => 'Story deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete story: ' . $e->getMessage()], 500);
        }
    }
}