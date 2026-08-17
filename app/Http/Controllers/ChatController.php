<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;
use App\Events\MessageDeleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // عرض صفحة الرسائل وجلب قائمة المحادثات
    public function index($receiverId = null)
    {
        $userId = Auth::id();

        // 1. جلب قائمة الأصدقاء النشطين
        $friendships = \App\Models\Friendship::where('is_active', 1)
            ->where(function($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->get();

        $friendIds = [];
        foreach ($friendships as $fs) {
            if ($fs->sender_id == $userId) {
                $friendIds[] = $fs->receiver_id;
            } else {
                $friendIds[] = $fs->sender_id;
            }
        }

        // 2. جلب المستخدمين الذين لديهم رسائل سابقة مع المستخدم الحالي
        $messagedUserIds = Message::where('sender_id', $userId)
            ->pluck('receiver_id')
            ->merge(
                Message::where('receiver_id', $userId)->pluck('sender_id')
            )
            ->unique()
            ->toArray();

        $allChatUserIds = array_unique(array_merge($friendIds, $messagedUserIds));

        // 3. جلب تفاصيل المستخدمين النشطين مع الترقيم (أول 15 فقط في البداية)
        $allChatUsers = User::whereIn('id', $allChatUserIds)
            ->where('id', '!=', $userId)
            ->where('is_active', 1)
            ->get()
            ->map(function($user) use ($userId) {
                // جلب آخر رسالة بين المستخدم الحالي وهذا المستخدم
                $lastMessage = Message::where(function($q) use ($userId, $user) {
                        $q->where('sender_id', $userId)->where('receiver_id', $user->id);
                    })
                    ->orWhere(function($q) use ($userId, $user) {
                        $q->where('sender_id', $user->id)->where('receiver_id', $userId);
                    })
                    ->latest()
                    ->first();

                $unreadCount = Message::where('sender_id', $user->id)
                    ->where('receiver_id', $userId)
                    ->where('is_read', false)
                    ->count();

                $user->last_message = $lastMessage;
                $user->last_message_time = $lastMessage ? $lastMessage->created_at : null;
                $user->unread_messages_count = $unreadCount;
                return $user;
            })
            // ترتيب المستخدمين حسب توقيت آخر رسالة (الأحدث أولاً)
            ->sortByDesc(function($user) {
                return $user->last_message_time ? $user->last_message_time->timestamp : 0;
            })
            ->values();

        // نمرر أول 15 فقط للـ Blade ونحتفظ بإجمالي العدد
        $totalChatUsers = $allChatUsers->count();
        $chatUsers = $allChatUsers->take(15);

        // 4. تحديد المحادثة النشطة حالياً
        $activeUser = null;
        if ($receiverId) {
            $activeUser = User::find($receiverId);
        } elseif ($chatUsers->isNotEmpty()) {
            $activeUser = $chatUsers->first();
        }

        // 5. إذا كان هناك مستخدم نشط ولكن غير موجود بقائمة المحادثات السابقة (مثلا تم النقر على مراسلة من بروفايله)
        if ($activeUser && !$chatUsers->contains('id', $activeUser->id)) {
            $lastMessage = Message::where(function($q) use ($userId, $activeUser) {
                    $q->where('sender_id', $userId)->where('receiver_id', $activeUser->id);
                })
                ->orWhere(function($q) use ($userId, $activeUser) {
                    $q->where('sender_id', $activeUser->id)->where('receiver_id', $userId);
                })
                ->latest()
                ->first();

            $activeUser->last_message = $lastMessage;
            $activeUser->last_message_time = $lastMessage ? $lastMessage->created_at : null;
            $activeUser->unread_messages_count = Message::where('sender_id', $activeUser->id)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();
            
            $chatUsers->prepend($activeUser);
        }

        return view('frontend.wiselook.pages.messages', compact('chatUsers', 'activeUser', 'totalChatUsers'));
    }

    // جلب الرسائل السابقة بين المستخدم الحالي والمستخدم الآخر
    public function fetchMessages(Request $request, $receiverId)
    {
        $userId = Auth::id();
        $beforeId = $request->query('before_id');

        // Mark incoming messages from this sender as read
        Message::where('sender_id', $receiverId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $query = Message::with(['sender', 'parent.sender'])
            ->where(function($q) use ($userId, $receiverId) {
                $q->where('sender_id', $userId)->where('receiver_id', $receiverId);
            })
            ->orWhere(function($q) use ($userId, $receiverId) {
                $q->where('sender_id', $receiverId)->where('receiver_id', $userId);
            });

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        $messages = $query->orderBy('id', 'desc')
            ->limit(30)
            ->get()
            ->map(function($msg) {
                $msg->image_url = $msg->image_url;
                $msg->video_url = $msg->video_url;
                $msg->audio_url = $msg->audio_url;
                return $msg;
            })
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    // جلب قائمة المحادثات بالتدريج (Lazy Loading Sidebar)
    public function fetchContacts(Request $request)
    {
        $userId = Auth::id();
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 15;

        $friendships = \App\Models\Friendship::where('is_active', 1)
            ->where(function($q) use ($userId) {
                $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
            })
            ->get();

        $friendIds = [];
        foreach ($friendships as $fs) {
            $friendIds[] = ($fs->sender_id == $userId) ? $fs->receiver_id : $fs->sender_id;
        }

        $messagedUserIds = Message::where('sender_id', $userId)
            ->pluck('receiver_id')
            ->merge(Message::where('receiver_id', $userId)->pluck('sender_id'))
            ->unique()
            ->toArray();

        $allChatUserIds = array_unique(array_merge($friendIds, $messagedUserIds));

        $allUsers = User::whereIn('id', $allChatUserIds)
            ->where('id', '!=', $userId)
            ->where('is_active', 1)
            ->get()
            ->map(function($user) use ($userId) {
                $lastMessage = Message::where(function($q) use ($userId, $user) {
                        $q->where('sender_id', $userId)->where('receiver_id', $user->id);
                    })
                    ->orWhere(function($q) use ($userId, $user) {
                        $q->where('sender_id', $user->id)->where('receiver_id', $userId);
                    })
                    ->latest()
                    ->first();

                $unreadCount = Message::where('sender_id', $user->id)
                    ->where('receiver_id', $userId)
                    ->where('is_read', false)
                    ->count();

                $user->last_message = $lastMessage;
                $user->last_message_time = $lastMessage ? $lastMessage->created_at : null;
                $user->unread_messages_count = $unreadCount;
                return $user;
            })
            ->sortByDesc(fn($u) => $u->last_message_time ? $u->last_message_time->timestamp : 0)
            ->values();

        $total = $allUsers->count();
        $paginated = $allUsers->forPage($page, $perPage)->values();
        $hasMore = ($page * $perPage) < $total;

        return response()->json([
            'users'    => $paginated->map(fn($u) => [
                'id'               => $u->id,
                'name'             => $u->first_name . ' ' . $u->last_name,
                'avatar_url'       => $u->avatar_url,
                'last_message'     => $u->last_message ? $u->last_message->message : null,
                'last_message_at'  => $u->last_message ? $u->last_message->created_at : null,
                'unread_count'     => $u->unread_messages_count ?? 0
            ]),
            'has_more' => $hasMore,
            'page'     => $page,
        ]);
    }

    // البحث عن رسائل تحتوي على نص معين في المحادثة
    public function searchMessages(Request $request, $receiverId)
    {
        $userId = Auth::id();
        $query = $request->query('query');

        if (!$query) {
            return response()->json([]);
        }

        $messages = Message::with(['sender'])
            ->where(function($queryGroup) use ($userId, $receiverId) {
                $queryGroup->where(function($q) use ($userId, $receiverId) {
                    $q->where('sender_id', $userId)->where('receiver_id', $receiverId);
                })
                ->orWhere(function($q) use ($userId, $receiverId) {
                    $q->where('sender_id', $receiverId)->where('receiver_id', $userId);
                });
            })
            ->where('message', 'LIKE', '%' . $query . '%')
            ->orderBy('id', 'desc')
            ->limit(30)
            ->get();

        return response()->json($messages);
    }

    // حفظ الرسالة الجديدة وبثها عبر الويب سوكيت فوراً
    public function sendMessage(Request $request)
    {
        $senderId = Auth::id() ?: ($request->user() ? $request->user()->id : null);
        $receiverId = $request->input('receiver_id') 
            ?? $request->input('user_id') 
            ?? $request->input('recipient_id') 
            ?? $request->input('to_id') 
            ?? $request->input('person_id');

        if (!$receiverId) {
            return response()->json([
                'status'  => 'error',
                'success' => false,
                'message' => 'مُعرّف المستقبل (receiver_id) مطلوب.'
            ], 422);
        }

        $messageText = $request->input('message') 
            ?? $request->input('text') 
            ?? $request->input('content') 
            ?? $request->input('body') 
            ?? '';

        $imagePath = null;
        if ($request->hasFile('image') || $request->hasFile('file') || $request->hasFile('media')) {
            $file = $request->file('image') ?? $request->file('file') ?? $request->file('media');
            if (str_starts_with($file->getMimeType(), 'image/')) {
                $imageName = date('YmdHis') . '_msg.' . $file->getClientOriginalExtension();
                $file->move(public_path('new_wiselook/uploads'), $imageName);
                $imagePath = $imageName;
            }
        }

        $videoPath = null;
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $originalExtension = strtolower($file->getClientOriginalExtension());
            $tempInputPath = $file->getRealPath();
            $targetDirectory = public_path('new_wiselook/uploads');

            if (!file_exists($targetDirectory)) {
                mkdir($targetDirectory, 0777, true);
            }

            $outputFileName = date('YmdHis') . '_msg_compressed.mp4';
            $outputPath = $targetDirectory . '/' . $outputFileName;

            $compressed = false;
            if (function_exists('exec')) {
                $cmd = "ffmpeg -y -i " . escapeshellarg($tempInputPath) . " -vcodec libx264 -crf 28 -preset fast -acodec aac -b:a 128k -movflags +faststart " . escapeshellarg($outputPath) . " 2>&1";
                @exec($cmd, $output, $returnCode);
                if ($returnCode === 0 && file_exists($outputPath) && filesize($outputPath) > 0) {
                    $compressed = true;
                    $videoPath = $outputFileName;
                }
            }

            if (!$compressed) {
                $videoName = date('YmdHis') . '_msg.' . $originalExtension;
                $file->move($targetDirectory, $videoName);
                $videoPath = $videoName;
            }
        }

        $audioPath = null;
        if ($request->hasFile('audio') || $request->hasFile('voice')) {
            $file = $request->file('audio') ?? $request->file('voice');
            $audioName = date('YmdHis') . '_msg_audio.m4a';
            $file->move(public_path('new_wiselook/uploads'), $audioName);
            $audioPath = $audioName;
        }

        $message = Message::create([
            'sender_id'   => $senderId,
            'receiver_id' => $receiverId,
            'message'     => $messageText,
            'image'       => $imagePath,
            'video'       => $videoPath,
            'audio'       => $audioPath,
            'parent_id'   => $request->input('parent_id') ?? $request->input('reply_to_id'),
        ]);

        $message->load(['sender', 'parent.sender']);
        $message->image_url = $message->image ? (str_starts_with($message->image, 'http') ? $message->image : asset('new_wiselook/uploads/' . basename($message->image))) : null;
        $message->video_url = $message->video ? (str_starts_with($message->video, 'http') ? $message->video : asset('new_wiselook/uploads/' . basename($message->video))) : null;
        $message->audio_url = $message->audio ? (str_starts_with($message->audio, 'http') ? $message->audio : asset('new_wiselook/uploads/' . basename($message->audio))) : null;

        // البث الفوري عبر Reverb للتطبيق والويب
        try {
            if ($request->hasHeader('X-Socket-ID') || $request->has('socket_id')) {
                broadcast(new MessageSent($message))->toOthers();
            } else {
                event(new MessageSent($message));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Reverb Broadcast Error: ' . $e->getMessage());
        }

        // إرسال إشعار FCM سحابي للمستقبل
        try {
            $senderUser = $request->user() ?: \App\Models\User::find($senderId);
            $senderName = $senderUser ? trim($senderUser->first_name . ' ' . $senderUser->last_name) : 'مستخدم';
            $senderPic = $senderUser ? $senderUser->profile_picture : null;
            $senderToken = $senderUser ? $senderUser->token : null;

            $bodyPreview = $messageText;
            if (empty($bodyPreview)) {
                if ($imagePath) $bodyPreview = '📷 أرسل صورة';
                elseif ($videoPath) $bodyPreview = '🎥 أرسل فيديو';
                elseif ($audioPath) $bodyPreview = '🎤 أرسل تسجيلاً صوتياً';
                else $bodyPreview = 'أرسل رسالة جديدة';
            }

            app(\App\Services\FcmNotificationService::class)->sendChatNotification(
                $receiverId,
                $senderName,
                $bodyPreview,
                (int)$senderId,
                $senderPic,
                $senderToken
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('FCM Chat send error: ' . $e->getMessage());
        }

        $tempId = $request->input('temp_id') 
            ?? $request->input('client_id') 
            ?? $request->input('uuid') 
            ?? $request->input('local_id') 
            ?? $request->input('temporary_id') 
            ?? $request->input('request_id');

        $message->temp_id = $tempId;
        $message->client_id = $tempId;
        $message->uuid = $tempId;
        $message->local_id = $tempId;

        // بث الحدث فورياً لكلا الطرفين
        try {
            if ($request->hasHeader('X-Socket-ID') || $request->has('socket_id')) {
                broadcast(new MessageSent($message))->toOthers();
            } else {
                event(new MessageSent($message));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Reverb Broadcast error in sendMessage: ' . $e->getMessage());
        }

        // توفير روابط الملفات الكاملة للاستجابة
        $message->image_url = $message->image ? (str_starts_with($message->image, 'http') ? $message->image : asset('new_wiselook/uploads/' . basename($message->image))) : null;
        $message->video_url = $message->video ? (str_starts_with($message->video, 'http') ? $message->video : asset('new_wiselook/uploads/' . basename($message->video))) : null;
        $message->audio_url = $message->audio ? (str_starts_with($message->audio, 'http') ? $message->audio : asset('new_wiselook/uploads/' . basename($message->audio))) : null;

        $primaryUrl = $message->image_url ?? $message->video_url ?? $message->audio_url;

        return response()->json([
            'status' => 'success',
            'success' => true,
            'is_sent' => true,
            'temp_id' => $tempId,
            'client_id' => $tempId,
            'uuid' => $tempId,
            'local_id' => $tempId,
            'uri' => $primaryUrl,
            'url' => $primaryUrl,
            'file' => $primaryUrl,
            'path' => $primaryUrl,
            'media' => $primaryUrl,
            'message' => $message,
            'data' => $message
        ]);
    }

    // حذف رسالة (من قِبَل المرسل فقط)
    public function deleteMessage(Request $request, $messageId)
    {
        $userId = auth('sanctum')->id() ?? ($request->user() ? $request->user()->id : Auth::id());
        if (!$userId) {
            $bearerToken = $request->bearerToken();
            if ($bearerToken) {
                $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($bearerToken);
                if ($pat) {
                    $userId = $pat->tokenable_id;
                }
            }
        }

        $message = Message::find($messageId);

        if (!$message) {
            return response()->json(['status' => 'success', 'success' => true, 'message' => 'تم حذف الرسالة بنجاح']);
        }

        // Only the sender of the message is allowed to delete it
        if ((int) $message->sender_id !== (int) $userId) {
            return response()->json(['status' => 'error', 'message' => 'غير مسموح لك بحذف هذه الرسالة.'], 403);
        }

        $receiverId = $message->receiver_id ? (int) $message->receiver_id : null;
        $senderId   = (int) $message->sender_id;
        $groupId    = $message->group_id ? (int) $message->group_id : null;

        $memberIds = [];
        if ($groupId) {
            $memberIds = \App\Models\GroupMember::where('group_id', $groupId)
                ->where('is_active', 1)
                ->pluck('user_id')
                ->map(fn($id) => (int)$id)
                ->toArray();
        }

        // Delete attached media files from disk
        foreach (['image', 'video', 'audio'] as $field) {
            if ($message->$field) {
                $filePath = public_path('new_wiselook/uploads/' . basename($message->$field));
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        $message->delete();

        // Broadcast deletion in real time safely
        try {
            event(new MessageDeleted((int) $messageId, $receiverId, $senderId, $groupId, $memberIds));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast error in deleteMessage: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success', 'success' => true, 'message' => 'تم حذف الرسالة بنجاح']);
    }

    /**
     * Get the count of unread messages for the logged-in user.
     */
    public function getUnreadCount()
    {
        $userId = Auth::id();
        $unreadCount = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Mark all received messages for the logged-in user as read.
     */
    public function markAllMessagesRead()
    {
        $userId = Auth::id();
        Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديد جميع الرسائل كمقروءة.'
        ]);
    }
}
