<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatPoll;
use App\Models\ChatPollOption;
use App\Models\ChatPollVote;
use App\Models\GroupMember;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;
use App\Events\GroupMessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatPollApiController extends Controller
{
    /**
     * Resolve authenticated user across Sanctum tokens, headers, and sessions.
     */
    private function resolveUser(Request $request): ?User
    {
        $user = $request->user('sanctum') ?? auth('sanctum')->user() ?? Auth::user();
        if ($user) {
            return $user;
        }

        $token = $request->bearerToken();
        if ($token) {
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->tokenable) {
                return $accessToken->tokenable;
            }
        }

        $userId = $request->header('X-User-Id')
            ?: $request->header('X-Auth-Id')
            ?: $request->input('user_id')
            ?: $request->input('sender_id')
            ?: $request->query('user_id');

        if ($userId) {
            return User::find($userId);
        }

        return null;
    }

    /**
     * Cast, toggle, or switch vote on a chat poll message.
     */
    public function vote(Request $request, $groupId = null)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'المستخدم غير مصرح له (يرجى تسجيل الدخول).'
            ], 401);
        }

        $messageId = $request->input('message_id');
        $optionId = (string)$request->input('option_id');
        $targetGroupId = $groupId ?: $request->input('group_id');

        if (!$messageId || empty($optionId)) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'بيانات التصويت غير مكتملة (message_id و option_id مطلوبان).'
            ], 422);
        }

        $message = Message::with(['sender', 'chatPoll.options.votes.user'])->find($messageId);
        if (!$message) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'الرسالة غير موجودة.'
            ], 404);
        }

        // If in a group, verify membership
        if ($message->group_id) {
            $isMember = GroupMember::where('group_id', $message->group_id)
                ->where('user_id', $user->id)
                ->where('is_active', 1)
                ->exists();

            if (!$isMember) {
                return response()->json([
                    'success' => false,
                    'status'  => 'error',
                    'message' => 'غير مصرح لك بالتصويت في هذه المجموعة.'
                ], 403);
            }
        }

        // 1. Ensure ChatPoll & Options exist (auto-provision if needed from message JSON)
        $poll = $message->chatPoll;
        if (!$poll) {
            $poll = $this->provisionPollFromMessage($message, $request->input('poll_data'), $user->id);
        }

        if (!$poll) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'فشل في تهيئة استطلاع الرأي لهذه الرسالة.'
            ], 500);
        }

        // 2. Find target option
        $option = $poll->options()->where('option_uid', $optionId)->first();
        if (!$option) {
            $option = $poll->options()->where('id', $optionId)->first();
        }
        if (!$option) {
            // Try matching by index if optionId is numeric (e.g. 0, 1, 2)
            if (is_numeric($optionId)) {
                $allOptions = $poll->options()->orderBy('id')->get();
                $idx = (int)$optionId;
                if (isset($allOptions[$idx])) {
                    $option = $allOptions[$idx];
                }
            }
        }

        if (!$option) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'الخيار المحدد غير موجود في هذا الاستطلاع.'
            ], 404);
        }

        // 3. Process Vote based on is_multiple_choice
        $existingVoteOnThisOption = ChatPollVote::where('chat_poll_id', $poll->id)
            ->where('chat_poll_option_id', $option->id)
            ->where('user_id', $user->id)
            ->first();

        if ($poll->is_multiple_choice) {
            // Multiple choice: toggle vote on this option
            if ($existingVoteOnThisOption) {
                $existingVoteOnThisOption->delete();
            } else {
                ChatPollVote::create([
                    'chat_poll_id' => $poll->id,
                    'chat_poll_option_id' => $option->id,
                    'user_id' => $user->id,
                ]);
            }
        } else {
            // Single choice
            if ($existingVoteOnThisOption) {
                // Clicking voted option toggles it off
                $existingVoteOnThisOption->delete();
            } else {
                // Clear any other votes by this user on this poll
                ChatPollVote::where('chat_poll_id', $poll->id)
                    ->where('user_id', $user->id)
                    ->delete();

                // Cast new vote
                ChatPollVote::create([
                    'chat_poll_id' => $poll->id,
                    'chat_poll_option_id' => $option->id,
                    'user_id' => $user->id,
                ]);
            }
        }

        // 4. Update vote counts for each option and poll total
        $pollOptions = $poll->options()->get();
        foreach ($pollOptions as $opt) {
            $count = ChatPollVote::where('chat_poll_option_id', $opt->id)->count();
            $opt->update(['vote_count' => $count]);
        }

        $totalVotes = ChatPollVote::where('chat_poll_id', $poll->id)
            ->distinct('user_id')
            ->count('user_id');

        $poll->update(['total_votes' => $totalVotes]);

        // 5. Construct updated poll JSON and update messages table
        $poll->load(['options.votes.user']);
        $updatedOptionsList = [];

        foreach ($poll->options as $opt) {
            $voterIds = [];
            $votersDetails = [];

            foreach ($opt->votes as $voteRecord) {
                if ($voteRecord->user) {
                    $vId = (string)$voteRecord->user_id;
                    $voterIds[] = $vId;
                    $vName = $voteRecord->user->name ?: trim(($voteRecord->user->first_name ?? '') . ' ' . ($voteRecord->user->last_name ?? ''));
                    $vAvatar = $voteRecord->user->avatar_url ?? $voteRecord->user->profile_picture;
                    $votersDetails[] = [
                        'user_id' => $vId,
                        'name' => !empty($vName) ? $vName : 'مستخدم',
                        'avatar' => $vAvatar,
                        'time' => $voteRecord->created_at ? $voteRecord->created_at->format('h:i A') : '',
                    ];
                }
            }

            $updatedOptionsList[] = [
                'id' => $opt->option_uid ?: (string)$opt->id,
                'text' => $opt->text,
                'votes' => $voterIds,
                'voters' => $votersDetails,
                'vote_count' => count($voterIds),
            ];
        }

        $updatedPollData = [
            'question' => $poll->question,
            'options' => $updatedOptionsList,
            'allow_multiple_answers' => (bool)$poll->is_multiple_choice,
            'total_votes' => $totalVotes,
        ];

        $pollJson = json_encode($updatedPollData, JSON_UNESCAPED_UNICODE);
        $message->update(['message' => $pollJson]);
        $message->load(['sender', 'parent.sender']);

        // 6. Broadcast Real-Time Update
        try {
            if ($message->group_id) {
                $members = GroupMember::where('group_id', $message->group_id)
                    ->where('is_active', 1)
                    ->pluck('user_id')
                    ->toArray();

                event(new GroupMessageSent($message, $members));
            } else {
                event(new MessageSent($message));
            }
        } catch (\Throwable $e) {
            Log::warning('Reverb Broadcast error in ChatPollApiController: ' . $e->getMessage());
        }

        return response()->json([
            'success'   => true,
            'status'    => 'success',
            'message'   => 'تم تسجيل التصويت بنجاح.',
            'poll_data' => $updatedPollData,
            'data'      => $updatedPollData,
            'message_item' => $message,
        ]);
    }

    /**
     * Auto-create ChatPoll and ChatPollOptions from message content or incoming payload.
     */
    private function provisionPollFromMessage(Message $message, $providedData = null, $votingUserId = null): ?ChatPoll
    {
        $rawText = $message->message;
        $data = null;

        if (is_string($rawText) && str_starts_with(trim($rawText), '{')) {
            try {
                $data = json_decode(trim($rawText), true);
            } catch (\Throwable $e) {}
        }

        if (!is_array($data) && is_array($providedData)) {
            $data = $providedData;
        }

        if (!is_array($data)) {
            $data = [
                'question' => 'استطلاع رأي',
                'options'  => [],
                'allow_multiple_answers' => false,
            ];
        }

        $question = $data['question'] ?? 'استطلاع رأي';
        $allowMultiple = !empty($data['allow_multiple_answers']);
        $rawOptions = isset($data['options']) && is_array($data['options']) ? $data['options'] : [];

        $poll = ChatPoll::create([
            'message_id'         => $message->id,
            'group_id'           => $message->group_id,
            'user_id'            => $message->sender_id ?: 1,
            'question'           => $question,
            'is_multiple_choice' => $allowMultiple,
            'total_votes'        => 0,
        ]);

        if (empty($rawOptions)) {
            $rawOptions = [
                ['id' => 'opt_1', 'text' => 'نعم'],
                ['id' => 'opt_2', 'text' => 'لا'],
            ];
        }

        foreach ($rawOptions as $idx => $opt) {
            $optUid = isset($opt['id']) ? (string)$opt['id'] : 'opt_' . ($idx + 1);
            $optText = isset($opt['text']) ? (string)$opt['text'] : (is_string($opt) ? $opt : 'خيار ' . ($idx + 1));

            $createdOption = ChatPollOption::create([
                'chat_poll_id' => $poll->id,
                'option_uid'   => $optUid,
                'text'         => $optText,
                'vote_count'   => 0,
            ]);

            // If initial voters exist from previous records, seed other users only (not the active voter to prevent auto-toggle cancel)
            if (isset($opt['votes']) && is_array($opt['votes'])) {
                foreach ($opt['votes'] as $vUserId) {
                    if ($vUserId && is_numeric($vUserId) && ($votingUserId === null || (int)$vUserId !== (int)$votingUserId)) {
                        ChatPollVote::firstOrCreate([
                            'chat_poll_id'        => $poll->id,
                            'chat_poll_option_id' => $createdOption->id,
                            'user_id'             => (int)$vUserId,
                        ]);
                    }
                }
            }
        }

        return $poll;
    }
}
