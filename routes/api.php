<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfileAuthController;
use App\Http\Controllers\Api\PostApiController;

use App\Http\Controllers\Api\GroupApiController;
use App\Http\Controllers\Api\ChatApiController;

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
    Route::post('register_v1.php', [ProfileAuthController::class, 'register']);
    Route::post('send_otp.php', [ProfileAuthController::class, 'sendOtp']);
    Route::post('verify_phone_otp.php', [ProfileAuthController::class, 'verifyOtp']);
    Route::post('send_code.php', [ProfileAuthController::class, 'sendCode']);
    Route::post('forgot_password.php', [ProfileAuthController::class, 'forgotPassword']);
    
    // مسارات محمية (تتطلب حتماً Auth Sanctum Token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout.php', [ProfileAuthController::class, 'logout']);
        Route::post('delete_account.php', [ProfileAuthController::class, 'deleteAccount']);
        Route::post('change_password.php', [ProfileAuthController::class, 'changePassword']);
        Route::post('change_profile.php', [ProfileAuthController::class, 'changeProfile']);
        Route::post('users.php', [ProfileAuthController::class, 'viewProfile']);
    });
});



Route::prefix('post')->middleware('auth:sanctum')->group(function () {
    Route::post('list_v1.php', [PostApiController::class, 'list']);
    Route::post('list_saved_v1.php', [PostApiController::class, 'listSaved']);
    Route::post('save_post.php', [PostApiController::class, 'toggleSave']);
    Route::post('pin_post.php', [PostApiController::class, 'togglePin']);
    Route::post('poll_vote.php', [PostApiController::class, 'vote']);
    Route::post('add_post_v1.php', [PostApiController::class, 'store']);
    Route::post('delete_item.php', [PostApiController::class, 'destroy']);
    Route::post('reaction_post.php', [PostApiController::class, 'react']);
    Route::post('list_comments.php', [PostApiController::class, 'listComments']);
    Route::post('add_comment.php', [PostApiController::class, 'addComment']);
});



Route::middleware('auth:sanctum')->group(function () {
    // روابط إدارة المجموعات
    Route::post('groups/add_group.php', [GroupApiController::class, 'addGroup']);
    Route::post('groups/edit_group.php', [GroupApiController::class, 'editGroup']);
    Route::delete('groups/remove_member.php', [GroupApiController::class, 'removeMember']);
    Route::put('groups/change_member_role.php', [GroupApiController::class, 'changeRole']);

    // روابط الدردشة والمحادثات
    Route::post('chat/list_v1.php', [ChatApiController::class, 'listChats']);
});



Route::middleware('auth:sanctum')->group(function () {
    // 4. روابط القصص اليومية (Stories)
    Route::prefix('story')->group(function () {
        Route::post('list.php', [StoryApiController::class, 'listStories']);
        Route::post('seen.php', [StoryApiController::class, 'markAsSeen']);
    });

    // 5. روابط الأصدقاء وإجراءات التفاعل (Friends)
    Route::prefix('friend')->group(function () {
        Route::post('list.php', [FriendApiController::class, 'listFriends']);
        Route::post('action.php', [FriendApiController::class, 'friendAction']);
    });
});



// مسار القاموس والترجمات العام (لا يتطلب تسجيل دخول لتمكين Splash Screen والـ Login من الترجمة)
Route::post('misc/dictionary.php', [MiscApiController::class, 'dictionary']);

Route::middleware('auth:sanctum')->group(function () {
    // 6. مسارات الإشعارات والمنوعات
    Route::post('misc/notifications.php', [MiscApiController::class, 'listNotifications']);
    Route::post('misc/mark_notification_seen.php', [MiscApiController::class, 'markSeen']);
    Route::post('misc/search.php', [MiscApiController::class, 'search']);

    // 7. مسار توليد رموز اتصال أغورا
    Route::get('chat/call/generate_token.php', [AgoraCallApiController::class, 'generateToken']);
});