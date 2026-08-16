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
        $senderId = $request->user() ? $request->user()->id : auth()->id();

        $receiverId = $request->input('receiver_id') 
            ?? $request->input('user_id') 
            ?? $request->input('recipient_id') 
            ?? $request->input('to_id') 
            ?? $request->input('person_id')
            ?? $request->input('id');

        if (!$receiverId) {
            
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
        }

        $videoPath = null;
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $videoName = date('YmdHis') . '_api_vid.' . $file->getClientOriginalExtension();
            $file->move(public_path('new_wiselook/uploads'), $videoName);
            $videoPath = $videoName;
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
            \Illuminate\Support\Facades\Log::warning('Reverb API Broadcast Error: ' . $e->getMessage());
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
        $targetId = $receiverId 
            ?? $request->input('receiver_id') 
            ?? $request->input('user_id') 
            ?? $request->input('recipient_id')
            ?? $request->input('person_id');

        if (!$targetId) {
            return response()->json(['success' => false, 'message' => 'receiver_id is required'], 422);
        }

        $beforeId = $request->input('before_id');

        $query = Message::with(['sender', 'parent.sender'])
            ->where(function($q) use ($userId, $targetId) {
                $q->where('sender_id', $userId)->where('receiver_id', $targetId);
            })
            ->orWhere(function($q) use ($userId, $targetId) {
                $q->where('sender_id', $targetId)->where('receiver_id', $userId);
            });

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        $messages = $query->orderBy('id', 'desc')
            ->limit($request->input('limit', 30))
            ->get()
            ->map(function($msg) {
                $msg->image_url = $msg->image ? asset('new_wiselook/uploads/' . basename($msg->image)) : null;
                $msg->video_url = $msg->video ? asset('new_wiselook/uploads/' . basename($msg->video)) : null;
                $msg->audio_url = $msg->audio ? asset('new_wiselook/uploads/' . basename($msg->audio)) : null;
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
        $targetMsgId = $messageId ?? $request->input('message_id') ?? $request->input('id');

        $message = Message::find($targetMsgId);
        if (!$message) {
            return response()->json(['success' => false, 'status' => 'error', 'message' => 'الرسالة غير موجودة.'], 404);
        }

        // Only the sender of the message is allowed to delete it
        if ((int)$message->sender_id !== (int)$userId) {
            return response()->json(['success' => false, 'status' => 'error', 'message' => 'غير مسموح لك بحذف هذه الرسالة.'], 403);
        }

        $receiverId = (int)$message->receiver_id;
        $senderId   = (int)$message->sender_id;

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
            event(new MessageDeleted((int)$targetMsgId, $receiverId, $senderId));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Reverb Broadcast Delete Error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'status'  => 'success',
            'message' => 'تم حذف الرسالة بنجاح'
        ]);
    }
}