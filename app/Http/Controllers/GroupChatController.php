<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupsRole;
use App\Models\Message;
use App\Models\User;
use App\Events\GroupMessageSent;
use App\Events\GroupMessagePinned;
use App\Events\GroupMessageUnpinned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupChatController extends Controller
{
    /**
     * Fetch all groups the authenticated user belongs to.
     */
    public function fetchGroups()
    {
        $userId = auth('sanctum')->id() ?? Auth::id();

        // Get groups where the user is the creator OR an active member
        $groups = Group::where(function ($query) use ($userId) {
            $query->where('created_by_user_id', $userId)
                  ->orWhereHas('members', function ($q) use ($userId) {
                      $q->where('user_id', $userId)->where('is_active', 1);
                  });
        })
        ->with(['members.user'])
        ->get()
        ->map(function ($group) use ($userId) {
            // Get latest message in this group
            $latestMessage = Message::where('group_id', $group->id)
                ->with('sender')
                ->latest()
                ->first();

            if ($latestMessage) {
                $senderName = $latestMessage->sender->first_name . ' ' . $latestMessage->sender->last_name;
                $group->latest_message = $senderName . ': ' . $latestMessage->message;
                $group->latest_message_time = $latestMessage->created_at->diffForHumans();
                $group->latest_message_timestamp = $latestMessage->created_at->toIso8601String();
            } else {
                $group->latest_message = 'لا توجد رسائل بعد';
                $group->latest_message_time = '';
                $group->latest_message_timestamp = null;
            }

            $group->avatar_url = $group->image ? asset('new_wiselook/uploads/' . $group->image) : asset('upload/no_image.jpg');

            // Calculate unread group messages count
            $member = GroupMember::where('group_id', $group->id)
                ->where('user_id', $userId)
                ->where('is_active', 1)
                ->first();

            $unreadCount = 0;
            if ($member) {
                $lastReadAt = $member->last_read_at;
                $unreadQuery = Message::where('group_id', $group->id)
                    ->where('sender_id', '!=', $userId);
                
                if ($lastReadAt) {
                    $unreadQuery->where('created_at', '>', $lastReadAt);
                }
                
                $unreadCount = $unreadQuery->count();
            }
            $group->unread_count = $unreadCount;

            return $group;
        })
        // Sort groups by latest message time
        ->sortByDesc(function ($group) {
            return $group->latest_message_timestamp ? strtotime($group->latest_message_timestamp) : 0;
        })
        ->values();

        return response()->json([
            'status' => 'success',
            'groups' => $groups
        ]);
    }

    /**
     * Create a new group and add selected members.
     */
    public function createGroup(Request $request)
    {
        if (is_string($request->members)) {
            $decoded = json_decode($request->members, true);
            if (is_array($decoded)) {
                $request->merge(['members' => $decoded]);
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id'
        ]);

        $creatorId = auth('sanctum')->id() ?? Auth::id();
        $memberIds = array_map('intval', $request->members);

        // Ensure creator is not in members array to avoid duplicates
        $memberIds = array_unique(array_filter($memberIds, function ($id) use ($creatorId) {
            return $id !== $creatorId;
        }));

        // Create the group
        $group = Group::create([
            'name' => $request->name,
            'descriptions' => $request->descriptions ?? '',
            'created_by_user_id' => $creatorId,
            'member_count' => count($memberIds) + 1,
        ]);

        // Handle group image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = date('YmdHis') . '_group.' . $file->getClientOriginalExtension();
            $file->move(public_path('new_wiselook/uploads'), $imageName);
            $group->image = $imageName;
            $group->save();
        }

        // Find or create default Roles
        $adminRole = GroupsRole::firstOrCreate(['name' => 'Admin']);
        $memberRole = GroupsRole::firstOrCreate(['name' => 'Member']);

        // Add creator as Admin member
        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $creatorId,
            'role_id' => $adminRole->id,
            'joined_at' => now(),
            'is_active' => 1
        ]);

        // Add selected friends as standard members
        foreach ($memberIds as $memberId) {
            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => $memberId,
                'role_id' => $memberRole->id,
                'joined_at' => now(),
                'added_by_user_id' => $creatorId,
                'is_active' => 1
            ]);
        }

        // Prepare return object
        $group->avatar_url = $group->image ? asset('new_wiselook/uploads/' . $group->image) : asset('upload/no_image.jpg');
        $group->latest_message = 'لا توجد رسائل بعد';
        $group->latest_message_time = '';
        $group->load('members.user');

        return response()->json([
            'status' => 'success',
            'group' => $group
        ]);
    }

    /**
     * Fetch messages for a specific group.
     */
    public function fetchGroupMessages(Request $request, $groupId)
    {
        $userId = auth('sanctum')->id() ?? Auth::id();
        if (!$userId) {
            $userId = $request->header('X-User-Id')
                ?: $request->header('X-Auth-Id')
                ?: $request->query('user_id')
                ?: $request->query('sender_id')
                ?: $request->input('user_id')
                ?: $request->input('sender_id');
        }

        if ($userId) {
            $isMember = GroupMember::where('group_id', $groupId)
                ->where('user_id', $userId)
                ->where('is_active', 1)
                ->exists();

            if (!$isMember) {
                return response()->json(['status' => 'error', 'message' => 'غير مسموح لك باستعراض رسائل هذه المجموعة.'], 403);
            }

            try {
                GroupMember::where('group_id', $groupId)
                    ->where('user_id', $userId)
                    ->where('is_active', 1)
                    ->update(['last_read_at' => now()]);
            } catch (\Throwable $e) {}
        }

        $beforeId = $request->query('before_id') ?? $request->input('before_id');
        $limit = min(50, max(1, (int)($request->query('limit') ?? $request->input('limit', 30))));

        $query = Message::select([
                'id', 'sender_id', 'group_id', 'message', 'image', 'video', 'audio',
                'parent_id', 'created_at'
            ])
            ->with([
                'sender:id,first_name,last_name,profile_picture',
                'parent:id,sender_id,message,image,video,audio',
                'parent.sender:id,first_name,last_name',
                'reactions.user:id,first_name,last_name,profile_picture'
            ])
            ->where('group_id', $groupId);

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        $messages = $query->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($msg) use ($userId) {
                $isSticker = false;
                $isDocument = false;
                $docExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'csv', 'tar', 'gz'];
                if ($msg->image) {
                    if (str_contains($msg->image, 'Animated-Fluent-Emojis') || str_contains($msg->image, '_stk_') || str_contains($msg->image, 'stickers/') || str_contains($msg->image, 'githubusercontent.com') || str_contains($msg->image, 'giphy.com') || str_contains($msg->image, 'tenor.com') || str_ends_with(strtolower($msg->image), '.gif')) {
                        $isSticker = true;
                    } else {
                        $ext = strtolower(pathinfo($msg->image, PATHINFO_EXTENSION));
                        if (in_array($ext, $docExtensions) || str_contains($msg->image, '_doc.') || str_contains($msg->image, '_file.')) {
                            $isDocument = true;
                        }
                    }
                }
                $isLocation = false;
                if ($msg->message && (
                    str_contains($msg->message, 'maps.google.com') ||
                    str_contains($msg->message, 'google.com/maps') ||
                    str_contains($msg->message, 'geo:') ||
                    (str_contains($msg->message, '"latitude"') && str_contains($msg->message, '"longitude"'))
                )) {
                    $isLocation = true;
                }
                $isPoll = false;
                if ($msg->message && (
                    (str_starts_with(trim($msg->message), '{') && str_contains($msg->message, '"question"') && str_contains($msg->message, '"options"'))
                )) {
                    $isPoll = true;
                }
                $msg->type = $isPoll ? 'poll' : ($isLocation ? 'location' : ($isSticker ? 'sticker' : ($isDocument ? 'document' : ($msg->image ? 'image' : ($msg->video ? 'video' : ($msg->audio ? 'voice' : 'text'))))));
                $msg->image_url = $msg->image ? (str_starts_with($msg->image, 'http') ? $msg->image : asset('new_wiselook/uploads/' . basename($msg->image))) : null;
                $msg->file_url = $msg->image_url;
                $msg->video_url = $msg->video ? (str_starts_with($msg->video, 'http') ? $msg->video : asset('new_wiselook/uploads/' . basename($msg->video))) : null;
                $msg->thumbnail_url = $msg->video ? asset('new_wiselook/uploads/' . pathinfo($msg->video, PATHINFO_FILENAME) . '_thumb.jpg') : null;
                $msg->audio_url = $msg->audio ? (str_starts_with($msg->audio, 'http') ? $msg->audio : asset('new_wiselook/uploads/' . basename($msg->audio))) : null;
                $reactionsSummary = [];
                if ($msg->relationLoaded('reactions')) {
                    $grouped = [];
                    foreach ($msg->reactions as $r) {
                        $em = $r->reaction;
                        if (!isset($grouped[$em])) {
                            $grouped[$em] = ['reaction' => $em, 'count' => 0, 'user_ids' => [], 'reacted_by_me' => false];
                        }
                        $grouped[$em]['count']++;
                        $grouped[$em]['user_ids'][] = (string)$r->user_id;
                        if ((string)$r->user_id === (string)$userId) {
                            $grouped[$em]['reacted_by_me'] = true;
                        }
                    }
                    $reactionsSummary = array_values($grouped);
                }
                $msg->reactions = $reactionsSummary;
                $msg->reactions_summary = $reactionsSummary;
                return $msg;
            })
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    /**
     * Send a new message to a group.
     */
    public function sendGroupMessage(Request $request, $groupId)
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '600');
        @ini_set('max_input_time', '600');

        $userId = auth('sanctum')->id() ?? Auth::id();
        if (!$userId) {
            $userId = $request->header('X-User-Id')
                ?: $request->header('X-Auth-Id')
                ?: $request->input('user_id')
                ?: $request->input('sender_id');
        }

        $messageText = $request->input('message') 
            ?? $request->input('text') 
            ?? $request->input('content') 
            ?? $request->input('body') 
            ?? '';

        $hasMedia = $request->hasFile('image') 
            || $request->hasFile('video') 
            || $request->hasFile('audio') 
            || $request->hasFile('document') 
            || $request->hasFile('file') 
            || $request->filled('image') 
            || $request->filled('image_url')
            || $request->filled('video')
            || $request->filled('audio')
            || $request->filled('document')
            || $request->filled('file');

        if (empty(trim($messageText)) && !$hasMedia) {
            return response()->json([
                'status' => 'error',
                'message' => 'محتوى الرسالة أو ملف المرفق مطلوب.'
            ], 422);
        }

        // Verify membership
        $members = GroupMember::where('group_id', $groupId)
            ->where('is_active', 1)
            ->pluck('user_id')
            ->toArray();

        if ($userId && !in_array($userId, $members)) {
            return response()->json(['status' => 'error', 'message' => 'غير مسموح لك بالنشر في هذه المجموعة.'], 403);
        }

        // Upload media (replicated from ChatController)
        $imagePath = null;
        $isDocument = false;
        $docOriginalName = null;

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $ext = strtolower($file->getClientOriginalExtension());
            $docOriginalName = $file->getClientOriginalName();
            $docName = date('YmdHis') . '_' . uniqid() . '_group_doc.' . ($ext ?: 'pdf');
            $file->move(public_path('new_wiselook/uploads'), $docName);
            $imagePath = $docName;
            $isDocument = true;
        } elseif ($request->hasFile('image') || $request->hasFile('file')) {
            $file = $request->file('image') ?? $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());
            $mime = $file->getMimeType();
            $docExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'csv', 'tar', 'gz'];

            if (in_array($ext, $docExtensions) || $request->input('type') === 'document') {
                $docOriginalName = $file->getClientOriginalName();
                $docName = date('YmdHis') . '_' . uniqid() . '_group_doc.' . ($ext ?: 'pdf');
                $file->move(public_path('new_wiselook/uploads'), $docName);
                $imagePath = $docName;
                $isDocument = true;
            } elseif (str_starts_with($mime, 'image/')) {
                if (empty($ext) || $ext === 'tmp') {
                    $ext = 'jpg';
                }
                $imageName = date('YmdHis') . '_' . uniqid() . '_group_msg.' . $ext;
                $file->move(public_path('new_wiselook/uploads'), $imageName);
                $imagePath = $imageName;
            } else {
                $docOriginalName = $file->getClientOriginalName();
                $docName = date('YmdHis') . '_' . uniqid() . '_group_doc.' . ($ext ?: 'bin');
                $file->move(public_path('new_wiselook/uploads'), $docName);
                $imagePath = $docName;
                $isDocument = true;
            }
        } elseif ($request->filled('image') && is_string($request->input('image'))) {
            $rawImg = $request->input('image');
            $imagePath = $rawImg;
        } elseif ($request->filled('image_url') && is_string($request->input('image_url'))) {
            $rawImg = $request->input('image_url');
            $imagePath = $rawImg;
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

            // Find ffmpeg & ffprobe dynamically across system paths
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
            $videoName = date('YmdHis') . '_' . uniqid() . '_group_vid.mp4';
            $targetPath = $targetDirectory . '/' . $videoName;

            if ($ffmpegPath && function_exists('shell_exec')) {
                $start = !is_null($trimStart) ? max(0.0, floatval($trimStart)) : 0.0;
                $end = !is_null($trimEnd) ? floatval($trimEnd) : ($originalDuration > 0 ? min($originalDuration, $maxAllowedDuration) : $maxAllowedDuration);
                
                $duration = $end - $start;
                if ($duration > $maxAllowedDuration || $duration <= 0) {
                    $duration = ($originalDuration > 0) ? min($maxAllowedDuration, $originalDuration) : $maxAllowedDuration;
                }

                // Compress with libx264 CRF 28 and AAC audio, fast preset + trim to 15 min max
                $cmd = "$ffmpegPath -y -ss $start -i " . escapeshellarg($tempInputPath) . " -t $duration -vcodec libx264 -crf 28 -preset fast -acodec aac -b:a 128k -movflags +faststart " . escapeshellarg($targetPath) . " 2>&1";
                @shell_exec($cmd);

                if (file_exists($targetPath) && filesize($targetPath) > 0) {
                    $compressed = true;
                    $videoPath = $videoName;
                }
            }

            // Safe fallback if ffmpeg not available or compression failed
            if (!$compressed) {
                $videoName = date('YmdHis') . '_' . uniqid() . '_group_vid.' . $originalExtension;
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
        } elseif ($request->filled('video')) {
            $videoPath = basename($request->input('video'));
        }

        $audioPath = null;
        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            $ext = strtolower($file->getClientOriginalExtension());
            if (empty($ext) || $ext === 'tmp') {
                $ext = 'aac';
            }
            $audioName = date('YmdHis') . '_group_audio.' . $ext;
            $file->move(public_path('new_wiselook/uploads'), $audioName);
            $audioPath = $audioName;
        } elseif ($request->filled('audio')) {
            $audioPath = basename($request->input('audio'));
        }

        // Create Group Message
        $message = Message::create([
            'sender_id' => $userId,
            'receiver_id' => null, // null for group chats
            'group_id' => $groupId,
            'message' => $messageText ?? '',
            'image' => $imagePath,
            'video' => $videoPath,
            'audio' => $audioPath,
            'parent_id' => $request->parent_id,
        ]);

        // Load relationships
        $message->load(['sender', 'parent.sender']);

        // Prepare return assets URLs
        $messageType = $request->input('type');
        if (empty($messageType)) {
            if ($isDocument) {
                $messageType = 'document';
            } elseif ($imagePath && (str_contains($imagePath, 'Animated-Fluent-Emojis') || str_contains($imagePath, '_stk_') || str_contains($imagePath, 'stickers/') || str_contains($imagePath, 'githubusercontent.com') || str_contains($imagePath, 'giphy.com') || str_contains($imagePath, 'tenor.com') || str_ends_with(strtolower($imagePath), '.gif'))) {
                $messageType = 'sticker';
            } else {
                $messageType = $imagePath ? 'image' : ($videoPath ? 'video' : ($audioPath ? 'voice' : 'text'));
            }
        }
        $message->type = $messageType;

        $message->image_url = $message->image ? ((str_starts_with($message->image, 'http://') || str_starts_with($message->image, 'https://')) ? $message->image : asset('new_wiselook/uploads/' . basename($message->image))) : null;
        $message->file_url = $message->image_url;
        $message->video_url = $message->video ? asset('new_wiselook/uploads/' . basename($message->video)) : null;
        $message->thumbnail_url = $message->video ? asset('new_wiselook/uploads/' . pathinfo($message->video, PATHINFO_FILENAME) . '_thumb.jpg') : null;
        $message->audio_url = $message->audio ? asset('new_wiselook/uploads/' . basename($message->audio)) : null;

        // Broadcast to all group members in real-time
        try {
            event(new GroupMessageSent($message, $members));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Reverb Broadcast error in sendGroupMessage: ' . $e->getMessage());
        }

        
        // إرسال إشعار FCM سحابي لجميع أعضاء المجموعة المشتركين
        try {
            $group = Group::find($groupId);
            $senderUser = User::find($userId);
            $senderName = $senderUser ? trim($senderUser->first_name . ' ' . $senderUser->last_name) : 'عضو';

            $bodyPreview = $request->message;
            if ($messageType === 'poll' || ($bodyPreview && str_starts_with(trim($bodyPreview), '{') && str_contains($bodyPreview, '"question"'))) {
                $bodyPreview = '📊 أرسل استطلاع رأي';
            } elseif ($messageType === 'sticker') {
                $bodyPreview = '🏷️ أرسل ملصقاً';
            } elseif ($messageType === 'document') {
                $bodyPreview = '📄 أرسل مستنداً: ' . ($request->message ?: 'ملف');
            } elseif ($messageType === 'location' || str_contains($bodyPreview ?? '', 'maps.google.com') || str_contains($bodyPreview ?? '', '"latitude"')) {
                $bodyPreview = '📍 أرسل موقعاً جغرافياً';
            } elseif (empty($bodyPreview)) {
                if ($imagePath) $bodyPreview = '📷 أرسل صورة';
                elseif ($videoPath) $bodyPreview = '🎥 أرسل فيديو';
                elseif ($audioPath) $bodyPreview = '🎤 أرسل مقطعاً صوتياً';
                else $bodyPreview = 'أرسل رسالة جديدة';
            }

            $otherMembers = array_values(array_diff($members, [$userId]));
            if ($group && !empty($otherMembers)) {
                app(\App\Services\FcmNotificationService::class)->sendGroupChatNotification(
                    $otherMembers,
                    $group->name,
                    $senderName,
                    $bodyPreview,
                    (int)$group->id,
                    $group->image,
                    (int)$userId
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('FCM GroupChat send error: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success', 'message' => $message]);
    }

    /**
     * Fetch full group details including member lists and roles.
     */
    public function getGroupDetails($groupId)
    {
        $userId = auth('sanctum')->id() ?? Auth::id();
        $group = Group::with(['members.user', 'members.role', 'pinnedMessage.sender'])->find($groupId);

        if (!$group) {
            return response()->json(['status' => 'error', 'message' => 'المجموعة غير موجودة.'], 404);
        }

        // Verify membership
        $isMember = $group->members()->where('user_id', $userId)->where('is_active', 1)->first();
        if (!$isMember) {
            return response()->json(['status' => 'error', 'message' => 'غير مسموح لك بعرض تفاصيل هذه المجموعة.'], 403);
        }

        $group->avatar_url = $group->image ? asset('new_wiselook/uploads/' . $group->image) : asset('upload/no_image.jpg');
        $isCreator = ((int)$group->created_by_user_id === (int)$userId);

        if ($group->pinnedMessage) {
            $pinned = $group->pinnedMessage;
            $pinned->image_url = $pinned->image ? (str_starts_with($pinned->image, 'http') ? $pinned->image : asset('new_wiselook/uploads/' . basename($pinned->image))) : null;
            $pinned->video_url = $pinned->video ? (str_starts_with($pinned->video, 'http') ? $pinned->video : asset('new_wiselook/uploads/' . basename($pinned->video))) : null;
            $pinned->audio_url = $pinned->audio ? (str_starts_with($pinned->audio, 'http') ? $pinned->audio : asset('new_wiselook/uploads/' . basename($pinned->audio))) : null;
        }

        return response()->json([
            'status' => 'success',
            'group' => $group,
            'is_creator' => $isCreator,
            'auth_user_id' => $userId,
            'pinned_message_id' => $group->pinned_message_id,
            'pinned_message' => $group->pinnedMessage,
        ]);
    }

    /**
     * Remove a member from the group. (Only group creator/admin allowed)
     */
    public function removeMember(Request $request, $groupId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $userId = auth('sanctum')->id() ?? Auth::id();
        $targetUserId = (int)$request->user_id;

        $group = Group::find($groupId);
        if (!$group) {
            return response()->json(['status' => 'error', 'message' => 'المجموعة غير موجودة.'], 404);
        }

        // Check if current user is the creator/admin
        if ((int)$group->created_by_user_id !== (int)$userId) {
            return response()->json(['status' => 'error', 'message' => 'غير مسموح لك بإزالة الأعضاء.'], 403);
        }

        // Prevent removing the creator
        if ($targetUserId === (int)$group->created_by_user_id) {
            return response()->json(['status' => 'error', 'message' => 'لا يمكن إزالة منشئ المجموعة.'], 400);
        }

        // Remove the member
        $member = GroupMember::where('group_id', $groupId)->where('user_id', $targetUserId)->first();
        if ($member) {
            $member->delete();
            $group->decrement('member_count');
        }

        return response()->json(['status' => 'success', 'message' => 'تم إزالة العضو بنجاح.']);
    }

    /**
     * Leave the group. (Standard members only)
     */
    public function leaveGroup($groupId)
    {
        $userId = auth('sanctum')->id() ?? Auth::id();
        $group = Group::find($groupId);
        if (!$group) {
            return response()->json(['status' => 'error', 'message' => 'المجموعة غير موجودة.'], 404);
        }

        // Prevent creator from leaving
        if ((int)$group->created_by_user_id === (int)$userId) {
            return response()->json(['status' => 'error', 'message' => 'لا يمكنك مغادرة المجموعة لأنك المنشئ. يمكنك حذف المجموعة بدلاً من ذلك.'], 400);
        }

        $member = GroupMember::where('group_id', $groupId)->where('user_id', $userId)->first();
        if ($member) {
            $member->delete();
            $group->decrement('member_count');
        }

        return response()->json(['status' => 'success', 'message' => 'لقد غادرت المجموعة بنجاح.']);
    }

    /**
     * Delete the group entirely. (Only group creator allowed)
     */
    public function deleteGroup($groupId)
    {
        $userId = auth('sanctum')->id() ?? Auth::id();
        $group = Group::find($groupId);
        if (!$group) {
            return response()->json(['status' => 'error', 'message' => 'المجموعة غير موجودة.'], 404);
        }

        if ((int)$group->created_by_user_id !== (int)$userId) {
            return response()->json(['status' => 'error', 'message' => 'غير مسموح لك بحذف هذه المجموعة.'], 403);
        }

        // Delete members, messages, and the group model
        $group->members()->delete();
        Message::where('group_id', $group->id)->delete();
        $group->delete();

        return response()->json(['status' => 'success', 'message' => 'تم حذف المجموعة نهائياً.']);
    }

    /**
     * Add new members to the group. (Only group creator/admin allowed)
     */
    public function addMembers(Request $request, $groupId)
    {
        if (is_string($request->members)) {
            $decoded = json_decode($request->members, true);
            if (is_array($decoded)) {
                $request->merge(['members' => $decoded]);
            }
        }

        $request->validate([
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id'
        ]);

        $userId = auth('sanctum')->id() ?? Auth::id();
        $group = Group::find($groupId);
        if (!$group) {
            return response()->json(['status' => 'error', 'message' => 'المجموعة غير موجودة.'], 404);
        }

        // Check if current user is creator/admin
        if ((int)$group->created_by_user_id !== (int)$userId) {
            return response()->json(['status' => 'error', 'message' => 'غير مسموح لك بإضافة أعضاء.'], 403);
        }

        $memberRole = GroupsRole::firstOrCreate(['name' => 'Member']);
        $addedCount = 0;
        $memberIds = array_map('intval', $request->members);

        foreach ($memberIds as $mId) {
            $existing = GroupMember::where('group_id', $groupId)->where('user_id', $mId)->first();
            if ($existing) {
                if ((int)$existing->is_active !== 1) {
                    $existing->update(['is_active' => 1]);
                    $addedCount++;
                }
            } else {
                GroupMember::create([
                    'group_id' => $groupId,
                    'user_id' => $mId,
                    'role_id' => $memberRole->id,
                    'is_active' => 1,
                    'joined_at' => now(),
                ]);
                $addedCount++;
            }
        }

        if ($addedCount > 0) {
            $group->increment('member_count', $addedCount);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم إضافة الأعضاء بنجاح.',
            'added_count' => $addedCount
        ]);
    }
    /**
     * Pin a group message (Creator / Admin only)
     */
    public function pinMessage(Request $request, $groupId)
    {
        $request->validate([
            'message_id' => 'required',
            'duration'   => 'nullable|string|in:24h,7d,30d,forever',
        ]);

        $userId = auth('sanctum')->id() ?? Auth::id();
        $group = Group::findOrFail($groupId);

        // Verify creator or admin permissions
        $isCreator = ((int)$group->created_by_user_id === (int)$userId);
        $isAdmin = GroupMember::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->whereIn('role_id', [1, 2])
            ->exists();

        if (!$isCreator && !$isAdmin) {
            return response()->json([
                'status' => 'error',
                'message' => 'عذراً، فقط منشئ المجموعة أو المسؤول يمكنه تثبيت الرسائل.'
            ], 403);
        }

        $duration = $request->input('duration', '24h');
        $pinnedUntil = match ($duration) {
            '24h' => now()->addHours(24),
            '7d'  => now()->addDays(7),
            '30d' => now()->addDays(30),
            default => null,
        };

        $messageId = $request->input('message_id');
        $group->pinned_message_id = $messageId;
        $group->pinned_by_user_id = $userId;
        $group->pinned_at = now();
        $group->pinned_until = $pinnedUntil;
        $group->save();

        $message = Message::with(['sender', 'parent.sender'])->find($messageId);
        if ($message) {
            $message->image_url = $message->image ? (str_starts_with($message->image, 'http') ? $message->image : asset('new_wiselook/uploads/' . basename($message->image))) : null;
            $message->video_url = $message->video ? (str_starts_with($message->video, 'http') ? $message->video : asset('new_wiselook/uploads/' . basename($message->video))) : null;
            $message->audio_url = $message->audio ? (str_starts_with($message->audio, 'http') ? $message->audio : asset('new_wiselook/uploads/' . basename($message->audio))) : null;
        }

        $memberIds = GroupMember::where('group_id', $groupId)
            ->where('is_active', 1)
            ->pluck('user_id')
            ->toArray();

        try {
            broadcast(new GroupMessagePinned(
                groupId: $groupId,
                pinnedMessageId: $messageId,
                pinnedMessage: $message,
                pinnedBy: $userId,
                memberIds: $memberIds
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Broadcast error in pinMessage: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم تثبيت الرسالة بنجاح',
            'pinned_message_id' => $messageId,
            'pinned_message' => $message,
            'pinned_until' => $pinnedUntil,
        ]);
    }

    /**
     * Unpin a group message (Creator / Admin only)
     */
    public function unpinMessage(Request $request, $groupId)
    {
        $userId = auth('sanctum')->id() ?? Auth::id();
        $group = Group::findOrFail($groupId);

        $isCreator = ((int)$group->created_by_user_id === (int)$userId);
        $isAdmin = GroupMember::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->whereIn('role_id', [1, 2])
            ->exists();

        if (!$isCreator && !$isAdmin) {
            return response()->json([
                'status' => 'error',
                'message' => 'عذراً، فقط منشئ المجموعة أو المسؤول يمكنه إلغاء التثبيت.'
            ], 403);
        }

        $group->pinned_message_id = null;
        $group->pinned_by_user_id = null;
        $group->pinned_at = null;
        $group->pinned_until = null;
        $group->save();

        $memberIds = GroupMember::where('group_id', $groupId)
            ->where('is_active', 1)
            ->pluck('user_id')
            ->toArray();

        try {
            broadcast(new GroupMessageUnpinned(
                groupId: $groupId,
                memberIds: $memberIds
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Broadcast error in unpinMessage: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم إلغاء تثبيت الرسالة بنجاح',
        ]);
    }
}
