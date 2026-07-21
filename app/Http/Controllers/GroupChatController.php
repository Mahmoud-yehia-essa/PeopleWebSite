<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupsRole;
use App\Models\Message;
use App\Models\User;
use App\Events\GroupMessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupChatController extends Controller
{
    /**
     * Fetch all groups the authenticated user belongs to.
     */
    public function fetchGroups()
    {
        $userId = Auth::id();

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
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id'
        ]);

        $creatorId = Auth::id();
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
        $userId = Auth::id();

        // Check if user is a member of the group
        $isMember = GroupMember::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->exists();

        if (!$isMember) {
            return response()->json(['status' => 'error', 'message' => 'غير مسموح لك باستعراض رسائل هذه المجموعة.'], 403);
        }

        // Update user's last_read_at timestamp for this group
        GroupMember::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->update(['last_read_at' => now()]);

        $beforeId = $request->query('before_id');

        $query = Message::with(['sender', 'parent.sender'])
            ->where('group_id', $groupId);

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

    /**
     * Send a new message to a group.
     */
    public function sendGroupMessage(Request $request, $groupId)
    {
        $request->validate([
            'message' => 'required_without_all:image,video,audio|nullable|string',
            'image' => 'nullable|image|max:5120',
            'video' => 'nullable|mimes:mp4,mov,avi,webm,ogg,qt,m4v|max:102400',
            'audio' => 'nullable|file|max:10240',
            'parent_id' => 'nullable|exists:messages,id',
            'trim_start' => 'nullable|numeric|min:0',
            'trim_end' => 'nullable|numeric|min:0',
        ]);

        $userId = Auth::id();

        // Verify membership
        $members = GroupMember::where('group_id', $groupId)
            ->where('is_active', 1)
            ->pluck('user_id')
            ->toArray();

        if (!in_array($userId, $members)) {
            return response()->json(['status' => 'error', 'message' => 'غير مسموح لك بالنشر في هذه المجموعة.'], 403);
        }

        // Upload media (replicated from ChatController)
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = date('YmdHis') . '_group_msg.' . $file->getClientOriginalExtension();
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
                $videoName = date('YmdHis') . '_group_vid.mp4';
                $targetPath = $targetDirectory . '/' . $videoName;
                
                $start = !is_null($trimStart) ? floatval($trimStart) : 0.0;
                $end = !is_null($trimEnd) ? floatval($trimEnd) : min($originalDuration, 120.0);
                
                $duration = $end - $start;
                if ($duration > 120.0 || $duration <= 0) {
                    $duration = min(120.0, $originalDuration);
                }

                $ffmpegPath = '/opt/homebrew/bin/ffmpeg';
                $cmd = "$ffmpegPath -ss $start -i " . escapeshellarg($tempInputPath) . " -t $duration -c:v libx264 -c:a aac -y " . escapeshellarg($targetPath) . " 2>&1";
                shell_exec($cmd);
                
                $videoPath = $videoName;
            } else {
                $videoName = date('YmdHis') . '_group_vid.' . $originalExtension;
                $file->move($targetDirectory, $videoName);
                $videoPath = $videoName;
            }
        }

        $audioPath = null;
        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            $audioName = date('YmdHis') . '_group_audio.' . $file->getClientOriginalExtension();
            $file->move(public_path('new_wiselook/uploads'), $audioName);
            $audioPath = $audioName;
        }

        // Create Group Message
        $message = Message::create([
            'sender_id' => $userId,
            'receiver_id' => null, // null for group chats
            'group_id' => $groupId,
            'message' => $request->message ?? '',
            'image' => $imagePath,
            'video' => $videoPath,
            'audio' => $audioPath,
            'parent_id' => $request->parent_id,
        ]);

        // Load relationships
        $message->load(['sender', 'parent.sender']);

        // Broadcast to other group members in real-time
        broadcast(new GroupMessageSent($message, $members))->toOthers();

        // Prepare return assets URLs
        $message->image_url = $message->image ? asset('new_wiselook/uploads/' . basename($message->image)) : null;
        $message->video_url = $message->video ? asset('new_wiselook/uploads/' . basename($message->video)) : null;
        $message->audio_url = $message->audio ? asset('new_wiselook/uploads/' . basename($message->audio)) : null;

        return response()->json(['status' => 'success', 'message' => $message]);
    }

    /**
     * Fetch full group details including member lists and roles.
     */
    public function getGroupDetails($groupId)
    {
        $userId = Auth::id();
        $group = Group::with(['members.user', 'members.role'])->find($groupId);

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

        return response()->json([
            'status' => 'success',
            'group' => $group,
            'is_creator' => $isCreator,
            'auth_user_id' => $userId
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

        $userId = Auth::id();
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
        $userId = Auth::id();
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
        $userId = Auth::id();
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
}
