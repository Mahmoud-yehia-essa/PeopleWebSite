<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfileAuthController;
use App\Http\Controllers\Api\PostApiController;

use App\Http\Controllers\Api\GroupApiController;
use App\Http\Controllers\Api\GroupSiteApiController;
use App\Http\Controllers\Api\ChatApiController;
use App\Http\Controllers\GroupChatController;

use App\Http\Controllers\Api\StoryApiController;
use App\Http\Controllers\Api\FriendApiController;

use App\Http\Controllers\Api\MiscApiController;
use App\Http\Controllers\Api\AgoraCallApiController;

use App\Http\Controllers\Auth\LoginController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// VerifyNow API Routes
Route::prefix('auth/otp')->group(function () {
    Route::post('/send', [LoginController::class, 'requestOtp']);
    Route::post('/verify', [LoginController::class, 'verifyOtp']);
});



// مسارات التوثيق والحساب الشخصي
Route::prefix('profile')->group(function () {
    // مسارات عامة (لا تتطلب Token تسجيل دخول)
    Route::post('login.php', [ProfileAuthController::class, 'login']);
    Route::post('google_login.php', [ProfileAuthController::class, 'googleLogin']);
    Route::post('apple_login.php', [ProfileAuthController::class, 'appleLogin']);
    Route::post('register_v1.php', [ProfileAuthController::class, 'register']);
    Route::post('send_otp.php', [ProfileAuthController::class, 'sendOtp']);
    Route::post('verify_phone_otp.php', [ProfileAuthController::class, 'verifyOtp']);
    Route::post('send_whatsapp_otp.php', [ProfileAuthController::class, 'sendWhatsappOtp']);
    Route::post('verify_whatsapp_otp.php', [ProfileAuthController::class, 'verifyWhatsappOtp']);
    Route::post('send_code.php', [ProfileAuthController::class, 'sendCode']);
    Route::post('forgot_password.php', [ProfileAuthController::class, 'forgotPassword']);
    Route::post('update_token.php', [ProfileAuthController::class, 'updateToken']);
    Route::match(['get', 'post'], 'points_details.php', [ProfileAuthController::class, 'getPointsDetails']);
    Route::match(['get', 'post'], 'rankings.php', [ProfileAuthController::class, 'getAllRankings']);
    Route::post('change_profile.php', [ProfileAuthController::class, 'changeProfile']);
    Route::post('users.php', [ProfileAuthController::class, 'viewProfile']);
    
    // مسارات محمية (تتطلب حتماً Auth Sanctum Token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout.php', [ProfileAuthController::class, 'logout']);
        Route::post('delete_account.php', [ProfileAuthController::class, 'deleteAccount']);
        Route::post('change_password.php', [ProfileAuthController::class, 'changePassword']);
    });
});

Route::match(['get', 'post'], 'rankings.php', [ProfileAuthController::class, 'getAllRankings']);
Route::match(['get', 'post'], 'sage_committee.php', [PostApiController::class, 'getSageCommittee']);
Route::match(['get', 'post'], 'wise_committee.php', [PostApiController::class, 'getSageCommittee']);

Route::prefix('post')->group(function () {
    Route::post('list_v1.php', [PostApiController::class, 'list']);
    Route::match(['get', 'post'], 'hashtags.php', [PostApiController::class, 'listHashtags']);
    Route::match(['get', 'post'], 'wise_ratings.php', [PostApiController::class, 'wiseRatings']);
    Route::match(['get', 'post'], 'list_post_reactions.php', [PostApiController::class, 'listPostReactions']);
    Route::match(['get', 'post'], 'get_voters.php', [PostApiController::class, 'getVoters']);
    Route::match(['get', 'post'], 'edit_post.php', [PostApiController::class, 'update']);
    Route::match(['get', 'post'], 'update_post.php', [PostApiController::class, 'update']);
    Route::match(['get', 'post'], 'edit_comment.php', [PostApiController::class, 'updateComment']);
    Route::match(['get', 'post'], 'update_comment.php', [PostApiController::class, 'updateComment']);
    Route::match(['get', 'post'], 'sage_committee.php', [PostApiController::class, 'getSageCommittee']);
    Route::match(['get', 'post'], 'wise_committee.php', [PostApiController::class, 'getSageCommittee']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('list_saved_v1.php', [PostApiController::class, 'listSaved']);
        Route::post('save_post.php', [PostApiController::class, 'toggleSave']);
        Route::post('pin_post.php', [PostApiController::class, 'togglePin']);
        Route::post('poll_vote.php', [PostApiController::class, 'vote']);
        Route::match(['get', 'post'], 'get_voters.php', [PostApiController::class, 'getVoters']);
        Route::match(['get', 'post'], 'edit_post.php', [PostApiController::class, 'update']);
        Route::match(['get', 'post'], 'update_post.php', [PostApiController::class, 'update']);
        Route::match(['get', 'post'], 'edit_comment.php', [PostApiController::class, 'updateComment']);
        Route::match(['get', 'post'], 'update_comment.php', [PostApiController::class, 'updateComment']);
        Route::post('add_post_v1.php', [PostApiController::class, 'store']);
        Route::match(['get', 'post'], 'add_post.php', [PostApiController::class, 'store']);
        Route::post('delete_item.php', [PostApiController::class, 'destroy']);
        Route::post('reaction_post.php', [PostApiController::class, 'react']);
        Route::post('list_comments.php', [PostApiController::class, 'listComments']);
        Route::post('add_comment.php', [PostApiController::class, 'addComment']);
    });
});



Route::middleware('auth:sanctum')->group(function () {
    // روابط إدارة المجموعات
    Route::post('groups/add_group.php', [GroupApiController::class, 'addGroup']);
    Route::post('groups/edit_group.php', [GroupApiController::class, 'editGroup']);
    Route::delete('groups/remove_member.php', [GroupApiController::class, 'removeMember']);
    Route::put('groups/change_member_role.php', [GroupApiController::class, 'changeRole']);

    // روابط الدردشة والمحادثات
    Route::post('chat/list_v1.php', [ChatApiController::class, 'listChats']);
    Route::match(['get', 'post'], 'chat/send_message.php', [ChatApiController::class, 'sendMessage']);
    Route::match(['get', 'post'], 'chat/send_v1.php', [ChatApiController::class, 'sendMessage']);
    Route::match(['get', 'post'], 'chat/send.php', [ChatApiController::class, 'sendMessage']);
    Route::match(['get', 'post'], 'chat/messages.php', [ChatApiController::class, 'fetchMessages']);
    Route::match(['get', 'post'], 'chat/fetch_messages.php', [ChatApiController::class, 'fetchMessages']);
    Route::match(['get', 'post'], 'chat/delete_message.php', [ChatApiController::class, 'deleteMessage']);

    Route::match(['get', 'post'], 'messages', [ChatApiController::class, 'sendMessage']);
    Route::match(['get', 'post'], 'messages/send', [ChatApiController::class, 'sendMessage']);
    Route::match(['get', 'post'], 'messages/fetch/{receiverId?}', [ChatApiController::class, 'fetchMessages']);
    Route::match(['delete', 'post'], 'messages/{messageId}', [ChatApiController::class, 'deleteMessage']);

    // Group Chat Endpoints
    Route::get('messages/groups/list', [GroupChatController::class, 'fetchGroups']);
    Route::match(['get', 'post'], 'messages/groups/create', [GroupChatController::class, 'createGroup']);
    Route::get('messages/groups/{groupId}/messages', [GroupChatController::class, 'fetchGroupMessages']);
    Route::match(['get', 'post'], 'messages/groups/{groupId}/messages', [GroupChatController::class, 'sendGroupMessage']);
    Route::get('messages/groups/{groupId}/details', [GroupChatController::class, 'getGroupDetails']);
    Route::post('messages/groups/{groupId}/members/remove', [GroupChatController::class, 'removeMember']);
    Route::post('messages/groups/{groupId}/members/add', [GroupChatController::class, 'addMembers']);
    Route::post('messages/groups/{groupId}/leave', [GroupChatController::class, 'leaveGroup']);
    Route::match(['delete', 'post'], 'messages/groups/{groupId}/delete', [GroupChatController::class, 'deleteGroup']);
});



// 4. روابط القصص اليومية (Stories - تدعم التوكن أو معرّف المستخدم)
Route::prefix('story')->group(function () {
    Route::match(['get', 'post'], 'list.php', [StoryApiController::class, 'listStories']);
    Route::match(['get', 'post'], 'list_stories.php', [StoryApiController::class, 'listStories']);
    Route::match(['get', 'post'], 'seen.php', [StoryApiController::class, 'markAsSeen']);
    Route::match(['get', 'post'], 'mark_as_seen.php', [StoryApiController::class, 'markAsSeen']);
    Route::match(['get', 'post'], 'add_story.php', [StoryApiController::class, 'addStory']);
    Route::match(['get', 'post'], 'viewers.php', [StoryApiController::class, 'getStoryViewers']);
    Route::match(['get', 'post'], 'delete_story.php', [StoryApiController::class, 'deleteStory']);
    Route::match(['get', 'post'], 'delete.php', [StoryApiController::class, 'deleteStory']);
});
Route::match(['get', 'post'], 'friend/action.php', [FriendApiController::class, 'friendAction']);

Route::middleware('auth:sanctum')->group(function () {

    // 4.5 روابط مجموعات الموقع - العمليات التي تتطلب مصادقة (Group Sites - Auth Required)
    Route::prefix('group_sites')->group(function () {
        Route::match(['get', 'post'], 'join.php', [GroupSiteApiController::class, 'joinGroup']);
        Route::match(['get', 'post'], 'leave.php', [GroupSiteApiController::class, 'leaveGroup']);
        Route::match(['get', 'post'], 'delete.php', [GroupSiteApiController::class, 'deleteGroup']);
        Route::match(['get', 'post'], 'create.php', [GroupSiteApiController::class, 'createGroup']);
        Route::match(['get', 'post'], 'update.php', [GroupSiteApiController::class, 'updateGroup']);
        Route::match(['get', 'post'], 'add_subject.php', [GroupSiteApiController::class, 'addSubject']);
        Route::match(['get', 'post'], 'delete_subject.php', [GroupSiteApiController::class, 'deleteSubject']);
        Route::match(['get', 'post'], 'update_subject.php', [GroupSiteApiController::class, 'updateSubject']);
        Route::match(['get', 'post'], 'toggle_reaction.php', [GroupSiteApiController::class, 'toggleSubjectReaction']);
        Route::match(['get', 'post'], 'add_comment.php', [GroupSiteApiController::class, 'addSubjectComment']);
        Route::match(['get', 'post'], 'delete_comment.php', [GroupSiteApiController::class, 'deleteSubjectComment']);
        Route::match(['get', 'post'], 'update_comment.php', [GroupSiteApiController::class, 'updateSubjectComment']);
        Route::match(['get', 'post'], 'react_comment.php', [GroupSiteApiController::class, 'reactSubjectComment']);
        Route::match(['get', 'post'], 'remove_member.php', [GroupSiteApiController::class, 'removeGroupMember']);
    });

    // 5. روابط الأصدقاء وإجراءات التفاعل (Friends)
    Route::prefix('friend')->group(function () {
        Route::match(['get', 'post'], 'list.php', [FriendApiController::class, 'listFriends']);
        Route::match(['get', 'post'], 'action.php', [FriendApiController::class, 'friendAction']);
        Route::match(['get', 'post'], 'my_network.php', [FriendApiController::class, 'myNetwork']);
    });
});



// مسارات عرض مجموعات الموقع (لا تتطلب تسجيل دخول، لكن تستفيد من التوكن لمعرفة حالة الإنضمام)
Route::prefix('group_sites')->group(function () {
    Route::match(['get', 'post'], 'list_groups.php', [GroupSiteApiController::class, 'listGroups']);
    Route::match(['get', 'post'], 'details.php', [GroupSiteApiController::class, 'getGroupDetails']);
    Route::match(['get', 'post'], 'comments.php', [GroupSiteApiController::class, 'getSubjectComments']);
    Route::match(['get', 'post'], 'delete_comment.php', [GroupSiteApiController::class, 'deleteSubjectComment']);
    Route::match(['get', 'post'], 'update_comment.php', [GroupSiteApiController::class, 'updateSubjectComment']);
    Route::match(['get', 'post'], 'comment_reactions.php', [GroupSiteApiController::class, 'getCommentReactions']);
    Route::match(['get', 'post'], 'subject_reactions.php', [GroupSiteApiController::class, 'getSubjectReactions']);
    Route::match(['get', 'post'], 'members.php', [GroupSiteApiController::class, 'getGroupMembers']);
    Route::match(['get', 'post'], 'delete_subject.php', [GroupSiteApiController::class, 'deleteSubject']);
    Route::match(['get', 'post'], 'update_subject.php', [GroupSiteApiController::class, 'updateSubject']);
});

// مسار القاموس والترجمات العام (لا يتطلب تسجيل دخول لتمكين Splash Screen والـ Login من الترجمة)
// مسار شبكتي العام (يتيح للزوار التصفح ويسجل الدخول اختيراياً عبر Sanctum)
Route::match(['get', 'post'], 'friend/my_network.php', [FriendApiController::class, 'myNetwork']);
Route::match(['get', 'post'], 'my_network', [FriendApiController::class, 'myNetwork']);

Route::post('misc/dictionary.php', [MiscApiController::class, 'dictionary']);
    Route::match(['get', 'post'], 'misc/pages.php', [MiscApiController::class, 'pages']);
    Route::match(['get', 'post'], 'pages', [MiscApiController::class, 'pages']);
    Route::post('misc/languages.php', [MiscApiController::class, 'languages']);
    Route::get('misc/languages.php', [MiscApiController::class, 'languages']);
    Route::get('languages', [MiscApiController::class, 'languages']);

Route::middleware('auth:sanctum')->group(function () {
    // 6. مسارات الإشعارات والمنوعات
    Route::post('misc/notifications.php', [MiscApiController::class, 'listNotifications']);
    Route::post('misc/mark_notification_seen.php', [MiscApiController::class, 'markSeen']);
    Route::post('misc/delete_notification.php', [MiscApiController::class, 'deleteNotification']);
    Route::post('notifications/delete', [MiscApiController::class, 'deleteNotification']);
    Route::post('misc/search.php', [MiscApiController::class, 'search']);

    // 7. مسارات الاتصال الفوري عبر أغورا (Agora Calls)
    Route::match(['get', 'post'], 'messages/call/initiate', [\App\Http\Controllers\CallController::class, 'initiateCall']);
    Route::match(['get', 'post'], 'chat/call/initiate', [\App\Http\Controllers\CallController::class, 'initiateCall']);
    Route::match(['get', 'post'], 'messages/call/accept', [\App\Http\Controllers\CallController::class, 'acceptCall']);
    Route::match(['get', 'post'], 'messages/call/decline', [\App\Http\Controllers\CallController::class, 'declineCall']);
    Route::match(['get', 'post'], 'messages/call/end', [\App\Http\Controllers\CallController::class, 'endCall']);
    Route::match(['get', 'post'], 'messages/call/group/initiate', [\App\Http\Controllers\CallController::class, 'initiateGroupCall']);
    Route::match(['get', 'post'], 'messages/call/group/join', [\App\Http\Controllers\CallController::class, 'joinGroupCall']);
    Route::match(['get', 'post'], 'messages/call/token', [\App\Http\Controllers\CallController::class, 'generateToken']);
    Route::match(['get', 'post'], 'chat/call/generate_token.php', [\App\Http\Controllers\CallController::class, 'generateToken']);

    // 8. مسارات سفراء الحكمة (Ambassadors)
    Route::match(['get', 'post'], 'ambassadors/data.php', [\App\Http\Controllers\AffiliateController::class, 'getAmbassadorData']);
    Route::match(['get', 'post'], 'ambassadors/update_code.php', [\App\Http\Controllers\AffiliateController::class, 'updateReferralCodeApi']);
});

// مسار مصادقة القنوات الفورية لـ Reverb / WebSockets
Route::match(['get', 'post'], 'broadcasting/auth', function (\Illuminate\Http\Request $request) {
    $user = $request->user();
    if (!$user) {
        $token = $request->bearerToken();
        if ($token) {
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->tokenable) {
                $user = $accessToken->tokenable;
                auth()->setUser($user);
                auth('sanctum')->setUser($user);
                $request->setUserResolver(fn () => $user);
            }
        }
    }
    if (!$user && $request->filled('user_id')) {
        $user = \App\Models\User::find($request->user_id);
        if ($user) {
            auth()->setUser($user);
            auth('sanctum')->setUser($user);
            $request->setUserResolver(fn () => $user);
        }
    }

    try {
        if ($user) {
            return \Illuminate\Support\Facades\Broadcast::auth($request);
        }
    } catch (\Throwable $e) {
        // Fallback to manual signature calculation below
    }

    $socketId = $request->input('socket_id');
    $channelName = $request->input('channel_name');
    $key = config('reverb.apps.apps.0.key', env('REVERB_APP_KEY'));
    $secret = config('reverb.apps.apps.0.secret', env('REVERB_APP_SECRET'));

    if (!$socketId || !$channelName || !$key || !$secret) {
        return response()->json(['error' => 'Invalid parameters'], 400);
    }

    $userId = $user ? $user->id : (int)$request->input('user_id', 0);
    $userName = $user ? ($user->name ?? $user->first_name ?? 'User') : 'User';
    $userAvatar = $user ? ($user->avatar_url ?? $user->profile_picture ?? null) : null;

    if (str_starts_with($channelName, 'presence-')) {
        $channelData = json_encode([
            'user_id' => (string)$userId,
            'user_info' => [
                'id' => (int)$userId,
                'name' => $userName,
                'avatar' => $userAvatar,
            ]
        ]);
        $stringToSign = "{$socketId}:{$channelName}:{$channelData}";
        $signature = hash_hmac('sha256', $stringToSign, $secret);
        return response()->json([
            'auth' => "{$key}:{$signature}",
            'channel_data' => $channelData,
        ]);
    } else {
        $stringToSign = "{$socketId}:{$channelName}";
        $signature = hash_hmac('sha256', $stringToSign, $secret);
        return response()->json([
            'auth' => "{$key}:{$signature}",
        ]);
    }
});
// مسارات الاتصال الاحتياطية العامة (تستخدم resolveCaller داخلياً للتحقق من Sanctum Bearer Token)
Route::match(['get', 'post'], 'messages/call/initiate', [\App\Http\Controllers\CallController::class, 'initiateCall']);
Route::match(['get', 'post'], 'chat/call/initiate', [\App\Http\Controllers\CallController::class, 'initiateCall']);
Route::match(['get', 'post'], 'messages/call/accept', [\App\Http\Controllers\CallController::class, 'acceptCall']);
Route::match(['get', 'post'], 'messages/call/decline', [\App\Http\Controllers\CallController::class, 'declineCall']);
Route::match(['get', 'post'], 'messages/call/end', [\App\Http\Controllers\CallController::class, 'endCall']);
