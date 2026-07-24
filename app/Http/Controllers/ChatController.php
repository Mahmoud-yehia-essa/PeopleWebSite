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
            ->limit(20)
            ->get()
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
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required_without_all:image,video,audio|nullable|string',
            'image' => 'nullable|image|max:5120',
            'video' => 'nullable|mimes:mp4,mov,avi,webm,ogg,qt,m4v|max:102400',
            'audio' => 'nullable|file|max:10240',
            'parent_id' => 'nullable|exists:messages,id',
            'trim_start' => 'nullable|numeric|min:0',
            'trim_end' => 'nullable|numeric|min:0',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = date('YmdHis') . '_msg.' . $file->getClientOriginalExtension();
            $file->move(public_path('new_wiselook/uploads'), $imageName);
            $imagePath = $imageName;
        }

        $videoPath = null;
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $originalExtension = strtolower($file->getClientOriginalExtension());
            $tempInputPath = $file->getRealPath();
            $targetDirectory = public_path('new_wiselook/uploads');

            // Use ffprobe to query duration
            $ffprobePath = '/opt/homebrew/bin/ffprobe';
            $durationCmd = "$ffprobePath -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($tempInputPath);
            $originalDuration = floatval(trim(shell_exec($durationCmd)));

            $trimStart = $request->input('trim_start');
            $trimEnd = $request->input('trim_end');

            if ($originalDuration > 120 || !is_null($trimStart) || !is_null($trimEnd)) {
                // Must be trimmed / transcoded
                $videoName = date('YmdHis') . '_msg_vid.mp4';
                $targetPath = $targetDirectory . '/' . $videoName;
                
                $start = !is_null($trimStart) ? floatval($trimStart) : 0.0;
                $end = !is_null($trimEnd) ? floatval($trimEnd) : min($originalDuration, 120.0);
                
                // Enforce maximum 2 minutes (120s)
                $duration = $end - $start;
                if ($duration > 120.0 || $duration <= 0) {
                    $duration = min(120.0, $originalDuration);
                }

                $ffmpegPath = '/opt/homebrew/bin/ffmpeg';
                $cmd = "$ffmpegPath -ss $start -i " . escapeshellarg($tempInputPath) . " -t $duration -c:v libx264 -c:a aac -y " . escapeshellarg($targetPath) . " 2>&1";
                shell_exec($cmd);
                
                $videoPath = $videoName;
            } else {
                // Save original directly
                $videoName = date('YmdHis') . '_msg_vid.' . $originalExtension;
                $file->move($targetDirectory, $videoName);
                $videoPath = $videoName;
            }
        }

        $audioPath = null;
        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            // Browser recorded audios are often webm or ogg, we'll keep the extension
            $audioName = date('YmdHis') . '_msg_audio.' . $file->getClientOriginalExtension();
            $file->move(public_path('new_wiselook/uploads'), $audioName);
            $audioPath = $audioName;
        }

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message ?? '',
            'image' => $imagePath,
            'video' => $videoPath,
            'audio' => $audioPath,
            'parent_id' => $request->parent_id,
        ]);

        // بث الحدث للمستقبل عبر الويب سوكيت
        // استخدام toOthers() يمنع تكرار الرسالة لدى الشخص المرسل نفسه عبر السوكيت لأنه أضافها بالفعل بيده في واجهته
        try {
            broadcast(new MessageSent($message->load(['sender', 'parent.sender'])))->toOthers();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Reverb Broadcast error in sendMessage: ' . $e->getMessage());
        }

        // تحميل علاقة المرسل وتوفير روابط الملفات الكاملة للاستجابة
        $message->load(['sender', 'parent.sender']);
        $message->image_url = $message->image ? asset('new_wiselook/uploads/' . basename($message->image)) : null;
        $message->video_url = $message->video ? asset('new_wiselook/uploads/' . basename($message->video)) : null;
        $message->audio_url = $message->audio ? asset('new_wiselook/uploads/' . basename($message->audio)) : null;

        return response()->json(['status' => 'success', 'message' => $message]);
    }

    // حذف رسالة (من قِبَل المرسل فقط)
    public function deleteMessage(Request $request, $messageId)
    {
        $userId = Auth::id();
        $message = Message::find($messageId);

        if (!$message) {
            return response()->json(['status' => 'error', 'message' => 'الرسالة غير موجودة.'], 404);
        }

        // Only the sender can delete their own message
        if ((int) $message->sender_id !== (int) $userId) {
            return response()->json(['status' => 'error', 'message' => 'غير مسموح لك بحذف هذه الرسالة.'], 403);
        }

        $receiverId = (int) $message->receiver_id;

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

        // Broadcast deletion to receiver in real time safely
        try {
            broadcast(new MessageDeleted((int) $messageId, $receiverId))->toOthers();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast error in deleteMessage: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success']);
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
