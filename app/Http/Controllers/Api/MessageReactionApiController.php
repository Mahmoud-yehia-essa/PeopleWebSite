<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\GroupMember;
use App\Events\MessageReactionUpdated;

class MessageReactionApiController extends Controller
{
    /**
     * Helper to compute grouped reactions summary for a message
     */
    public static function formatReactionsForMessage($messageId, $currentUserId = null)
    {
        $all = MessageReaction::where('message_id', $messageId)
            ->with('user:id,first_name,last_name,profile_picture')
            ->get();

        $grouped = [];
        $allFormatted = [];

        foreach ($all as $item) {
            $emoji = $item->reaction;
            if (!isset($grouped[$emoji])) {
                $grouped[$emoji] = [
                    'reaction'      => $emoji,
                    'count'         => 0,
                    'user_ids'      => [],
                    'users'         => [],
                    'reacted_by_me' => false,
                ];
            }
            $grouped[$emoji]['count']++;
            $grouped[$emoji]['user_ids'][] = (string)$item->user_id;

            $userName = $item->user ? trim($item->user->first_name . ' ' . $item->user->last_name) : 'مستخدم';
            $userAvatar = $item->user ? ($item->user->profile_picture ? asset('new_wiselook/uploads/' . basename($item->user->profile_picture)) : null) : null;

            $userData = [
                'id'              => (string)$item->user_id,
                'name'            => $userName,
                'profile_picture' => $userAvatar,
                'reaction'        => $emoji,
                'created_at'      => $item->created_at ? $item->created_at->toIso8601String() : null,
            ];

            $grouped[$emoji]['users'][] = $userData;
            $allFormatted[] = $userData;

            if ($currentUserId && (string)$item->user_id === (string)$currentUserId) {
                $grouped[$emoji]['reacted_by_me'] = true;
            }
        }

        return [
            'summary' => array_values($grouped),
            'all'     => $allFormatted,
        ];
    }

    /**
     * Add, toggle, or remove an emoji reaction on a message
     */
    public function toggleReaction(Request $request, $messageId = null)
    {
        $messageId = $messageId 
            ?? $request->input('message_id') 
            ?? $request->input('msg_id');

        $userId = auth('sanctum')->id() 
            ?? auth()->id() 
            ?? $request->header('X-User-Id')
            ?? $request->header('X-Auth-Id')
            ?? $request->input('user_id');

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 401);
        }

        $reaction = $request->input('reaction') ?? $request->input('emoji');
        if (empty($reaction)) {
            return response()->json(['success' => false, 'message' => 'رمز التفاعل (reaction) مطلوب'], 422);
        }

        $message = Message::find($messageId);
        if (!$message) {
            return response()->json(['success' => false, 'message' => 'الرسالة غير موجودة'], 404);
        }

        $existing = MessageReaction::where('message_id', $messageId)
            ->where('user_id', $userId)
            ->first();

        $action = 'added';
        $userReaction = $reaction;

        if ($existing) {
            if ($existing->reaction === $reaction) {
                // If clicked on identical reaction -> remove/toggle off
                $existing->delete();
                $action = 'removed';
                $userReaction = null;
            } else {
                // If clicked on different reaction -> replace
                $existing->reaction = $reaction;
                $existing->save();
                $action = 'updated';
                $userReaction = $reaction;
            }
        } else {
            // New reaction
            MessageReaction::create([
                'message_id' => $messageId,
                'user_id'    => $userId,
                'reaction'   => $reaction,
            ]);
            $action = 'added';
            $userReaction = $reaction;
        }

        // Compute updated summary
        $formatted = self::formatReactionsForMessage($messageId, $userId);
        $summary = $formatted['summary'];
        $all = $formatted['all'];

        // Get group members if group message
        $memberIds = [];
        if (!empty($message->group_id)) {
            $memberIds = GroupMember::where('group_id', $message->group_id)
                ->where(function($q) {
                    $q->whereNull('is_active')->orWhere('is_active', 1);
                })
                ->pluck('user_id')
                ->map(fn($id) => (int)$id)
                ->toArray();
        }

        // Broadcast Reverb WebSockets event in real-time
        try {
            event(new MessageReactionUpdated(
                $message,
                (int)$userId,
                $userReaction,
                $action,
                $summary,
                $all,
                $memberIds
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Reverb Reaction Broadcast Error: ' . $e->getMessage());
        }

        return response()->json([
            'success'           => true,
            'action'            => $action,
            'message_id'        => (int)$messageId,
            'user_reaction'     => $userReaction,
            'reactions'         => $summary,
            'reactions_summary' => $summary,
            'all_reactions'     => $all,
        ]);
    }

    /**
     * Get all reactions for a specific message
     */
    public function getReactions(Request $request, $messageId)
    {
        $userId = auth('sanctum')->id() ?? auth()->id() ?? $request->input('user_id');
        $formatted = self::formatReactionsForMessage($messageId, $userId);

        return response()->json([
            'success'           => true,
            'message_id'        => (int)$messageId,
            'reactions'         => $formatted['summary'],
            'reactions_summary' => $formatted['summary'],
            'all_reactions'     => $formatted['all'],
        ]);
    }
}
