<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Group;
use App\Models\Message;
use App\Events\MessageSent;
use App\Events\MessageDeleted;

class ChatApiController extends Controller
{
    /**
     * 3.5 جلب قائمة المحادثات والمجموعات المتزامنة مع Firestore
     */
    public function listChats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids'  => 'nullable|array',  // مصفوفة معرفات المستخدمين المطلوب بياناتهم
            'group_ids' => 'nullable|array', // مصفوفة معرفات المجموعات المطلوب بياناتها
            'search'    => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $formattedData = [];

        // 1. معالجة وجلب بيانات المستخدمين الفرديين (Chats)
        if ($request->has('user_ids') && is_array($request->user_ids)) {
            $usersQuery = User::whereIn('id', $request->user_ids)->where('is_active', 1);
            
            if ($request->has('search')) {
                $usersQuery->where(function($q) use ($request) {
                    $q->where('first_name', 'LIKE', '%' . $request->search . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $request->search . '%');
                });
            }

            $users = $usersQuery->get();

            foreach ($users as $user) {
                $formattedData[] = [
                    'id'              => (string)$user->id,
                    'name'            => $user->first_name . ' ' . $user->last_name,
                    'profile_picture' => $user->profile_picture,
                    'unread_count'    => 0, // تدار القيمة الحية ديناميكياً عبر واجهة الموبايل
                    'type'            => 'user'
                ];
            }
        }

        // 2. معالجة وجلب بيانات المجموعات (Group Chats)
        if ($request->has('group_ids') && is_array($request->group_ids)) {
            $groupsQuery = Group::whereIn('id', $request->group_ids);

            if ($request->has('search')) {
                $groupsQuery->where('name', 'LIKE', '%' . $request->search . '%');
            }

            $groups = $groupsQuery->get();

            foreach ($groups as $group) {
                $formattedData[] = [
                    'id'              => 'group_' . $group->id, // البادئة المتوقعة لتفريق الغرف بالـ Flutter
                    'name'            => $group->name,
                    'profile_picture' => $group->image,
                    'unread_count'    => 0, // تدار عبر كود الموبايل و الكاش المحلي
                    'type'            => 'group'
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $formattedData
        ]);
    }

    /**
     * إرسال رسالة جديدة من تطبيق الموبايل مع بثها فوراً عبر Reverb
     */
    public function sendMessage(Request $request)
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '600');
        @ini_set('max_input_time', '600');

        $senderId = $request->user() ? $request->user()->id : auth()->id();

        $receiverId = $request->input('receiver_id') 
            ?? $request->input('user_id') 
            ?? $request->input('recipient_id') 
            ?? $request->input('to_id') 
            ?? $request->input('person_id')
            ?? $request->input('id');

        if (!$receiverId) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
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
                $imageName = date('YmdHis') . '_api_msg.' . $file->getClientOriginalExtension();
                $file->move(public_path('new_wiselook/uploads'), $imageName);
                $imagePath = $imageName;
            }
        } elseif ($request->filled('image') && is_string($request->input('image'))) {
            $imagePath = $request->input('image');
        } elseif ($request->filled('image_url') && is_string($request->input('image_url'))) {
            $imagePath = $request->input('image_url');
        }

        $videoPath = null;
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $originalExtension = strtolower($file->getClientOriginalExtension());
            if (empty($originalExtension) || $originalExtension === 'tmp') {
                $originalExtension = 'mp4';
            }
            $tempInputPath = $file->getRealPath();
            $targetDirectory = public_path('new_wiselook/uploads');

            if (!file_exists($targetDirectory)) {
                @mkdir($targetDirectory, 0777, true);
            }

            $ffmpegPath = null;
            $ffmpegCandidates = ['/opt/homebrew/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/usr/bin/ffmpeg', 'ffmpeg'];
            foreach ($ffmpegCandidates as $cand) {
                if ($cand === 'ffmpeg' || (file_exists($cand) && is_executable($cand))) {
                    $ffmpegPath = $cand;
                    break;
                }
            }

            $ffprobePath = null;
            $ffprobeCandidates = ['/opt/homebrew/bin/ffprobe', '/usr/local/bin/ffprobe', '/usr/bin/ffprobe', 'ffprobe'];
            foreach ($ffprobeCandidates as $cand) {
                if ($cand === 'ffprobe' || (file_exists($cand) && is_executable($cand))) {
                    $ffprobePath = $cand;
                    break;
                }
            }

            $originalDuration = 0.0;
            if ($ffprobePath && function_exists('shell_exec')) {
                $durationCmd = "$ffprobePath -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($tempInputPath);
                $durationOut = @shell_exec($durationCmd);
                if ($durationOut) {
                    $originalDuration = floatval(trim($durationOut));
                }
            }

            $maxAllowedDuration = 900.0; // 15 minutes = 900 seconds
            $trimStart = $request->input('trim_start');
            $trimEnd = $request->input('trim_end');

            $compressed = false;
            $outputFileName = date('YmdHis') . '_' . uniqid() . '_api_vid_compressed.mp4';
            $outputPath = $targetDirectory . '/' . $outputFileName;

            if ($ffmpegPath && function_exists('shell_exec')) {
                $start = !is_null($trimStart) ? max(0.0, floatval($trimStart)) : 0.0;
                $end = !is_null($trimEnd) ? floatval($trimEnd) : ($originalDuration > 0 ? min($originalDuration, $maxAllowedDuration) : $maxAllowedDuration);
                
                $duration = $end - $start;
                if ($duration > $maxAllowedDuration || $duration <= 0) {
                    $duration = ($originalDuration > 0) ? min($maxAllowedDuration, $originalDuration) : $maxAllowedDuration;
                }

                $cmd = "$ffmpegPath -y -ss $start -i " . escapeshellarg($tempInputPath) . " -t $duration -vcodec libx264 -crf 28 -preset fast -acodec aac -b:a 128k -movflags +faststart " . escapeshellarg($outputPath) . " 2>&1";
                @shell_exec($cmd);

                if (file_exists($outputPath) && filesize($outputPath) > 0) {
                    $compressed = true;
                    $videoPath = $outputFileName;
                }
            }

            if (!$compressed) {
                $videoName = date('YmdHis') . '_' . uniqid() . '_api_vid.' . $originalExtension;
                $file->move($targetDirectory, $videoName);
                $videoPath = $videoName;
            }

            // Generate Video Thumbnail Frame (.jpg)
            if ($ffmpegPath && $videoPath && function_exists('shell_exec')) {
                $savedVideoFullPath = $targetDirectory . '/' . $videoPath;
                $thumbName = pathinfo($videoPath, PATHINFO_FILENAME) . '_thumb.jpg';
                $thumbFullPath = $targetDirectory . '/' . $thumbName;
                $thumbCmd = "$ffmpegPath -y -ss 00:00:01 -i " . escapeshellarg($savedVideoFullPath) . " -vframes 1 -q:v 2 " . escapeshellarg($thumbFullPath) . " 2>&1";
                @shell_exec($thumbCmd);
            }
        }

        $audioPath = null;
        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            $audioName = date('YmdHis') . '_api_audio.' . $file->getClientOriginalExtension();
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

        $messageType = $request->input('type');
        if (empty($messageType)) {
            $messageType = $imagePath ? 'image' : ($videoPath ? 'video' : ($audioPath ? 'voice' : 'text'));
        }
        $message->type = $messageType;

        $message->load(['sender', 'parent.sender']);
        $message->image_url = $message->image ? (str_starts_with($message->image, 'http') ? $message->image : asset('new_wiselook/uploads/' . basename($message->image))) : null;
        $message->video_url = $message->video ? (str_starts_with($message->video, 'http') ? $message->video : asset('new_wiselook/uploads/' . basename($message->video))) : null;
        $message->thumbnail_url = $message->video ? asset('new_wiselook/uploads/' . pathinfo($message->video, PATHINFO_FILENAME) . '_thumb.jpg') : null;
        $message->audio_url = $message->audio ? (str_starts_with($message->audio, 'http') ? $message->audio : asset('new_wiselook/uploads/' . basename($message->audio))) : null;

        // البث الفوري عبر Reverb للتطبيق والويب
        try {
            if ($request->hasHeader('X-Socket-ID') || $request->has('socket_id')) {
                broadcast(new MessageSent($message))->toOthers();
            } else {
                event(new MessageSent($message));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Reverb API Broadcast Error: ' . $e->getMessage());
        }

        // إرسال إشعار FCM سحابي للمستقبل
        try {
            $senderUser = $request->user() ?: \App\Models\User::find($senderId);
            $senderName = $senderUser ? trim($senderUser->first_name . ' ' . $senderUser->last_name) : 'مستخدم';
            $senderPic = $senderUser ? $senderUser->profile_picture : null;
            $senderToken = $senderUser ? $senderUser->token : null;

            $bodyPreview = $messageText;
            if ($messageType === 'sticker') {
                $bodyPreview = '🏷️ أرسل ملصقاً';
            } elseif (empty($bodyPreview)) {
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

        $primaryUrl = $message->image_url ?? $message->video_url ?? $message->audio_url;

        return response()->json([
            'success'   => true,
            'status'    => 'success',
            'is_sent'   => true,
            'temp_id'   => $tempId,
            'client_id' => $tempId,
            'uuid'      => $tempId,
            'local_id'  => $tempId,
            'uri'       => $primaryUrl,
            'url'       => $primaryUrl,
            'file'      => $primaryUrl,
            'path'      => $primaryUrl,
            'media'     => $primaryUrl,
            'data'      => $message,
            'message'   => $message
        ]);
    }

    /**
     * جلب رسائل المحادثة بين المستخدم الحالي ومستخدم آخر
     */
    public function fetchMessages(Request $request, $receiverId = null)
    {
        $userId = $request->user() ? $request->user()->id : auth()->id();
        if (!$userId) {
            $userId = $request->header('X-User-Id')
                ?: $request->header('X-Auth-Id')
                ?: $request->query('user_id')
                ?: $request->query('sender_id')
                ?: $request->input('user_id')
                ?: $request->input('sender_id');
        }

        $targetId = $receiverId 
            ?? $request->input('receiver_id') 
            ?? $request->query('receiver_id') 
            ?? $request->input('user_id') 
            ?? $request->input('recipient_id')
            ?? $request->input('person_id');

        if (!$targetId || !$userId) {
            return response()->json(['success' => true, 'status' => 'success', 'data' => [], 'messages' => []], 200);
        }

        $beforeId = $request->input('before_id') ?? $request->query('before_id');
        $limit = min(50, max(1, (int)($request->input('limit') ?? $request->query('limit', 30))));

        $query = Message::select([
                'id', 'sender_id', 'receiver_id', 'message', 'image', 'video', 'audio',
                'parent_id', 'created_at', 'is_read'
            ])
            ->with([
                'sender:id,first_name,last_name,profile_picture',
                'parent:id,sender_id,message,image,video,audio',
                'parent.sender:id,first_name,last_name'
            ])
            ->where(function($q) use ($userId, $targetId) {
                $q->where(function($sub) use ($userId, $targetId) {
                    $sub->where('sender_id', $userId)->where('receiver_id', $targetId);
                })->orWhere(function($sub) use ($userId, $targetId) {
                    $sub->where('sender_id', $targetId)->where('receiver_id', $userId);
                });
            });

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        $messages = $query->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($msg) {
                $msg->image_url = $msg->image ? (str_starts_with($msg->image, 'http') ? $msg->image : asset('new_wiselook/uploads/' . basename($msg->image))) : null;
                $msg->video_url = $msg->video ? (str_starts_with($msg->video, 'http') ? $msg->video : asset('new_wiselook/uploads/' . basename($msg->video))) : null;
                $msg->thumbnail_url = $msg->video ? asset('new_wiselook/uploads/' . pathinfo($msg->video, PATHINFO_FILENAME) . '_thumb.jpg') : null;
                $msg->audio_url = $msg->audio ? (str_starts_with($msg->audio, 'http') ? $msg->audio : asset('new_wiselook/uploads/' . basename($msg->audio))) : null;
                return $msg;
            })
            ->reverse()
            ->values();

        return response()->json([
            'success'  => true,
            'status'   => 'success',
            'data'     => $messages,
            'messages' => $messages
        ]);
    }

    /**
     * حذف رسالة
     */
    public function deleteMessage(Request $request, $messageId = null)
    {
        $userId = auth('sanctum')->id() ?? ($request->user() ? $request->user()->id : auth()->id());
        if (!$userId) {
            $bearerToken = $request->bearerToken();
            if ($bearerToken) {
                $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($bearerToken);
                if ($pat) {
                    $userId = $pat->tokenable_id;
                }
            }
        }
        if (!$userId) {
            $userId = $request->input('user_id') ?? $request->input('sender_id');
        }

        $targetMsgId = $messageId ?? $request->input('message_id') ?? $request->input('id');

        $message = Message::find($targetMsgId);
        if (!$message) {
            return response()->json(['success' => true, 'status' => 'success', 'message' => 'الرسالة غير موجودة أو تم حذفها مسبقاً.'], 200);
        }

        // Only the sender (or participant) is allowed to delete it
        if ($userId && (int)$message->sender_id !== (int)$userId && (int)$message->receiver_id !== (int)$userId) {
            return response()->json(['success' => false, 'status' => 'error', 'message' => 'غير مسموح لك بحذف هذه الرسالة.'], 403);
        }

        $receiverId = $message->receiver_id ? (int)$message->receiver_id : null;
        $senderId   = (int)$message->sender_id;
        $groupId    = $message->group_id ? (int)$message->group_id : null;

        $memberIds = [];
        if ($groupId) {
            $memberIds = \App\Models\GroupMember::where('group_id', $groupId)
                ->where('is_active', 1)
                ->pluck('user_id')
                ->map(fn($id) => (int)$id)
                ->toArray();
        }

        foreach (['image', 'video', 'audio'] as $field) {
            if ($message->$field) {
                $filePath = public_path('new_wiselook/uploads/' . basename($message->$field));
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        $message->delete();

        try {
            event(new MessageDeleted((int)$targetMsgId, $receiverId, $senderId, $groupId, $memberIds));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Reverb Broadcast Delete Error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'status'  => 'success',
            'message' => 'تم حذف الرسالة بنجاح'
        ]);
    }

    /**
     * رفع وسائط المحادثة (صور، فيديو، صوت)
     */
    public function uploadMedia(Request $request)
    {
        $file = $request->file('media') ?? $request->file('file') ?? $request->file('image') ?? $request->file('audio') ?? $request->file('video');
        if (!$file) {
            return response()->json(['success' => false, 'message' => 'No media file provided'], 422);
        }

        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $fileName = date('YmdHis') . '_' . uniqid() . '.' . $extension;
        $uploadsDir = public_path('new_wiselook/uploads');
        if (!file_exists($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        $file->move($uploadsDir, $fileName);
        $url = asset('new_wiselook/uploads/' . $fileName);

        return response()->json([
            'success' => true,
            'message' => 'Media uploaded successfully',
            'url'     => $url,
            'path'    => $fileName,
            'file'    => $fileName,
            'data'    => [
                'url'      => $url,
                'filename' => $fileName
            ]
        ]);
    }

}
