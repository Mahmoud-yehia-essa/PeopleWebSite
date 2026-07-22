<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\SavedPostController;
use App\Http\Controllers\GroupSiteController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\HashtagController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\WiseCommitteeController;
use App\Http\Controllers\AppVersionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\GroupChatController;



Route::middleware(['auth'])->group(function () {
    Route::get('/messages/fetch/{receiverId}', [ChatController::class, 'fetchMessages']);
    Route::get('/messages/search/{receiverId}', [ChatController::class, 'searchMessages']);
    Route::get('/messages/contacts', [ChatController::class, 'fetchContacts']);
    Route::get('/messages/unread-count', [ChatController::class, 'getUnreadCount']);
    Route::post('/messages/mark-all-read', [ChatController::class, 'markAllMessagesRead']);
    Route::delete('/messages/{messageId}', [ChatController::class, 'deleteMessage']);
    Route::post('/messages', [ChatController::class, 'sendMessage']);

    // مسارات المجموعات
    Route::get('/messages/groups/list', [GroupChatController::class, 'fetchGroups']);
    Route::post('/messages/groups/create', [GroupChatController::class, 'createGroup']);
    Route::get('/messages/groups/{groupId}/messages', [GroupChatController::class, 'fetchGroupMessages']);
    Route::post('/messages/groups/{groupId}/messages', [GroupChatController::class, 'sendGroupMessage']);
    Route::get('/messages/groups/{groupId}/details', [GroupChatController::class, 'getGroupDetails']);
    Route::post('/messages/groups/{groupId}/members/remove', [GroupChatController::class, 'removeMember']);
    Route::post('/messages/groups/{groupId}/leave', [GroupChatController::class, 'leaveGroup']);
    Route::delete('/messages/groups/{groupId}/delete', [GroupChatController::class, 'deleteGroup']);

    // مسارات إشارات الاتصال الفوري (Agora)
    Route::post('/messages/call/initiate', [CallController::class, 'initiateCall']);
    Route::post('/messages/call/group/initiate', [CallController::class, 'initiateGroupCall']);
    Route::post('/messages/call/group/join', [CallController::class, 'joinGroupCall']);
    Route::post('/messages/call/accept', [CallController::class, 'acceptCall']);
    Route::post('/messages/call/decline', [CallController::class, 'declineCall']);
    Route::post('/messages/call/end', [CallController::class, 'endCall']);
});

Route::get('/', function () {
    return view('frontend.soon');
})->name('frontend.soon');

Route::get('/dev', [PostController::class, 'indexFrontend'])->name('frontend.home');
Route::get('/trending-issues', [PostController::class, 'trendingIssues'])->name('frontend.trending');

use App\Http\Controllers\Auth\LoginController;

Route::get('/user-login', function () {
    if (!session()->has('url.intended')) {
        $previousUrl = url()->previous();
        if ($previousUrl && $previousUrl !== route('user.login') && $previousUrl !== url('/user-login') && parse_url($previousUrl, PHP_URL_HOST) === request()->getHost()) {
            session(['url.intended' => $previousUrl]);
        }
    }
    $posts = App\Models\Post::with('user')->where('is_active', 1)->orderByRaw('(like_count + comment_count) DESC')->take(20)->get();
    return view('frontend.wiselook.pages.login', compact('posts'));
})->middleware('guest')->name('user.login');

Route::middleware('guest')->group(function () {
    Route::get('/otp-login', [LoginController::class, 'showLoginForm'])->name('otp.login');
    Route::post('/otp/send', [LoginController::class, 'requestOtp'])->name('otp.send');
    Route::post('/otp/verify', [LoginController::class, 'verifyOtp'])->name('otp.verify');
});

// ---------------------------------------------------------------
// Firebase Phone Authentication Routes (NEW – independent feature)
// ---------------------------------------------------------------
use App\Http\Controllers\Auth\FirebaseAuthController;

Route::middleware('guest')->group(function () {
    Route::get('/login/phone/new', [FirebaseAuthController::class, 'showLoginForm'])
        ->name('firebase.phone.login');
    Route::get('/register/phone/new', [FirebaseAuthController::class, 'showRegisterForm'])
        ->name('firebase.phone.register');
});

Route::post('/verify-firebase-token-new', [FirebaseAuthController::class, 'verifyToken'])
    ->name('firebase.phone.verify')
    ->middleware('web');

Route::post('/check-phone-register-new', [FirebaseAuthController::class, 'checkPhoneAvailability'])
    ->name('firebase.phone.check')
    ->middleware('web');

Route::post('/send-whatsapp-otp-new', [FirebaseAuthController::class, 'sendWhatsappOtp'])
    ->name('whatsapp.phone.send')
    ->middleware('web');

Route::post('/verify-whatsapp-otp-new', [FirebaseAuthController::class, 'verifyWhatsappOtp'])
    ->name('whatsapp.phone.verify')
    ->middleware('web');



Route::get('/ref/{code}', [AffiliateController::class, 'redirectReferral'])->name('affiliate.redirect');

Route::get('/dashboard', function () {
    // جلب آخر 5 مستخدمين مسجلين فعلياً لعرضهم في جدول الواجهة
    $users = App\Models\User::latest()->take(5)->get();
    
    // حساب الإحصائيات الحيوية المطلوبة من الجداول الحقيقية
    $postsCount = App\Models\Post::count();
    $usersCount = App\Models\User::count();
    $groupSitesCount = App\Models\GroupSite::count();
    $languagesCount = App\Models\Language::count();
    $storiesCount = App\Models\Story::count();
    $rankingsCount = App\Models\Ranking::count();
    $wiseCommitteeCount = App\Models\WiseCommittee::count();
    $supportTicketsCount = App\Models\SupportTicket::count();

    // جلب المسوق الأكثر جلباً للمستخدمين
    $topAffiliateLink = App\Models\AffiliateLink::with('user')
        ->withCount('trackings')
        ->orderBy('trackings_count', 'desc')
        ->first();

    $topAffiliateUser = $topAffiliateLink && $topAffiliateLink->trackings_count > 0 
        ? $topAffiliateLink->user->first_name . ' ' . $topAffiliateLink->user->last_name 
        : 'لا يوجد حالياً';

    $topAffiliateCount = $topAffiliateLink ? $topAffiliateLink->trackings_count : 0;

    // جلب أكثر 10 مستخدمين مشاركةً (بناءً على عدد المواضيع)
    $topActiveUsers = App\Models\User::select('users.id', 'users.first_name', 'users.last_name', 'users.profile_picture')
        ->selectRaw('COUNT(posts.id) as posts_count')
        ->join('posts', 'posts.user_id', '=', 'users.id')
        ->whereNull('posts.deleted_at')
        ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.profile_picture')
        ->orderByDesc('posts_count')
        ->limit(10)
        ->get();

    return view('admin.index', compact(
        'users',
        'postsCount',
        'usersCount',
        'groupSitesCount',
        'languagesCount',
        'storiesCount',
        'rankingsCount',
        'wiseCommitteeCount',
        'supportTicketsCount',
        'topAffiliateUser',
        'topAffiliateCount',
        'topActiveUsers'
    ));
})->middleware(['auth', 'verified', 'admin'])->name('dashboard');

// مسار تسجيل الخروج الفعلي للوحة التحكم
Route::get('/admin/logout', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('admin.logout');

// تحويل الملف الشخصي للوحة التحكم إلى صفحة تعديل الملف الشخصي الافتراضية
Route::get('/admin/profile', function () {
    return redirect()->route('profile.edit');
})->name('admin.profile');

Route::middleware(['auth', 'admin'])->group(function () {
    // إضافة وعرض المستخدمين والمدراء
    Route::get('/admin/users/add', [UserController::class, 'addUser'])->name('add.user');
    Route::post('/admin/users/add/store', [UserController::class, 'storeUser'])->name('add.user.store');
    Route::get('/admin/users/all', [UserController::class, 'allUsers'])->name('all.users');
    Route::get('/admin/users/all-admin', [UserController::class, 'allAdmin'])->name('all.admin');

    // تفعيل وإيقاف وتعديل وحذف المستخدمين
    Route::get('/admin/users/inactive/{id}', [UserController::class, 'inactiveUser'])->name('inactive.user');
    Route::get('/admin/users/active/{id}', [UserController::class, 'activeUser'])->name('active.user');
    Route::get('/admin/users/edit/{id}', [UserController::class, 'editUser'])->name('edit.user');
    Route::post('/admin/users/edit/store', [UserController::class, 'updateUser'])->name('edit.user.store');
    Route::get('/admin/users/delete/{id}', [UserController::class, 'deleteUser'])->name('delete.user');

    // إدارة المواضيع
    Route::get('/admin/posts/all', [PostController::class, 'allPosts'])->name('all.posts');
    Route::post('/admin/posts/bulk-delete', [PostController::class, 'bulkDeletePosts'])->name('posts.bulk.delete');
    Route::get('/admin/posts/add', [PostController::class, 'addPost'])->name('add.post');
    Route::post('/admin/posts/store', [PostController::class, 'storePost'])->name('store.post');
    Route::get('/admin/posts/edit/{id}', [PostController::class, 'editPost'])->name('edit.post');
    Route::post('/admin/posts/update', [PostController::class, 'updatePost'])->name('update.post');
    Route::get('/admin/posts/delete/{id}', [PostController::class, 'deletePost'])->name('delete.post');
    Route::get('/admin/posts/active/{id}', [PostController::class, 'activePost'])->name('active.post');
    Route::get('/admin/posts/inactive/{id}', [PostController::class, 'inactivePost'])->name('inactive.post');
    Route::get('/admin/posts/{id}/reactions', [PostController::class, 'getPostReactions'])->name('posts.reactions');
    Route::get('/admin/posts/{id}/comments', [PostController::class, 'getPostComments'])->name('posts.comments');
    Route::get('/admin/posts/{id}/details', [PostController::class, 'getPostDetailsJson'])->name('posts.details');
    Route::get('/admin/posts/pin/{id}', [PostController::class, 'pinPostForm'])->name('posts.pin.form');
    Route::post('/admin/posts/pin/store', [PostController::class, 'pinPostStore'])->name('posts.pin.store');
    Route::get('/admin/posts/unpin/{id}', [PostController::class, 'unpinPost'])->name('posts.unpin');

    // المواضيع المحفوظة للمستخدمين
    Route::get('/admin/saved-posts/all', [SavedPostController::class, 'allSavedPosts'])->name('all.saved_posts');
    Route::get('/admin/saved-posts/delete/{id}', [SavedPostController::class, 'deleteSavedPost'])->name('delete.saved_post');

    // إدارة طلبات الصداقة
    Route::get('/admin/friendships/all', [FriendshipController::class, 'allFriendships'])->name('all.friendships');
    Route::get('/admin/friendships/add', [FriendshipController::class, 'addFriendship'])->name('add.friendship');
    Route::post('/admin/friendships/store', [FriendshipController::class, 'storeFriendship'])->name('store.friendship');
    Route::get('/admin/friendships/active/{id}', [FriendshipController::class, 'activeFriendship'])->name('active.friendship');
    Route::get('/admin/friendships/inactive/{id}', [FriendshipController::class, 'inactiveFriendship'])->name('inactive.friendship');
    Route::get('/admin/friendships/delete/{id}', [FriendshipController::class, 'deleteFriendship'])->name('delete.friendship');

    // إدارة المجموعات
    Route::get('/admin/groups/all', [GroupController::class, 'allGroups'])->name('all.groups');
    Route::get('/admin/groups/add', [GroupController::class, 'addGroup'])->name('add.group');
    Route::post('/admin/groups/store', [GroupController::class, 'storeGroup'])->name('store.group');
    Route::get('/admin/groups/edit/{id}', [GroupController::class, 'editGroup'])->name('edit.group');
    Route::post('/admin/groups/update', [GroupController::class, 'updateGroup'])->name('update.group');
    Route::get('/admin/groups/delete/{id}', [GroupController::class, 'deleteGroup'])->name('delete.group');
    Route::get('/admin/groups/{id}/members', [GroupController::class, 'getGroupMembers'])->name('groups.members');

    // إدارة القصص (Stories)
    Route::get('/admin/stories/all', [StoryController::class, 'allStories'])->name('all.stories');
    Route::get('/admin/stories/add', [StoryController::class, 'addStory'])->name('add.story');
    Route::post('/admin/stories/store', [StoryController::class, 'storeStory'])->name('store.story');
    Route::get('/admin/stories/edit/{id}', [StoryController::class, 'editStory'])->name('edit.story');
    Route::post('/admin/stories/update', [StoryController::class, 'updateStory'])->name('update.story');
    Route::get('/admin/stories/delete/{id}', [StoryController::class, 'deleteStory'])->name('delete.story');
    Route::get('/admin/stories/active/{id}', [StoryController::class, 'activeStory'])->name('active.story');
    Route::get('/admin/stories/inactive/{id}', [StoryController::class, 'inactiveStory'])->name('inactive.story');
    Route::get('/admin/stories/{id}/viewers', [StoryController::class, 'getStoryViewers'])->name('stories.viewers');

    // إدارة المجموعات الخاصة والعامة (Group Sites)
    Route::get('/admin/group-sites/all', [GroupSiteController::class, 'allGroupSites'])->name('all.group_sites');
    Route::get('/admin/group-sites/add', [GroupSiteController::class, 'addGroupSite'])->name('add.group_site');
    Route::post('/admin/group-sites/store', [GroupSiteController::class, 'storeGroupSite'])->name('store.group_site');
    Route::get('/admin/group-sites/edit/{id}', [GroupSiteController::class, 'editGroupSite'])->name('edit.group_site');
    Route::post('/admin/group-sites/update', [GroupSiteController::class, 'updateGroupSite'])->name('update.group_site');
    Route::get('/admin/group-sites/delete/{id}', [GroupSiteController::class, 'deleteGroupSite'])->name('delete.group_site');

    // مسارات AJAX للتفاصيل الفرعية للمجموعات الخاصة والعامة
    Route::get('/admin/group-sites/{id}/members', [GroupSiteController::class, 'getMembers'])->name('group_sites.members');
    Route::post('/admin/group-sites/members/kick', [GroupSiteController::class, 'kickMember'])->name('group_sites.kick_member');
    Route::get('/admin/group-sites/{id}/subjects', [GroupSiteController::class, 'getSubjects'])->name('group_sites.subjects');
    Route::get('/admin/group-sites/subjects/delete/{id}', [GroupSiteController::class, 'deleteSubject'])->name('group_sites.delete_subject');
    Route::get('/admin/group-sites/subjects/{subject_id}/comments', [GroupSiteController::class, 'getComments'])->name('group_sites.comments');
    Route::get('/admin/group-sites/comments/delete/{id}', [GroupSiteController::class, 'deleteComment'])->name('group_sites.delete_comment');
    Route::get('/admin/group-sites/subjects/{subject_id}/reactions', [GroupSiteController::class, 'getReactions'])->name('group_sites.reactions');
    Route::get('/admin/group-sites/reactions/delete/{id}', [GroupSiteController::class, 'deleteReaction'])->name('group_sites.delete_reaction');

    // إدارة اللغات (Languages)
    Route::get('/admin/languages/all', [LanguageController::class, 'allLanguages'])->name('all.languages');
    Route::get('/admin/languages/add', [LanguageController::class, 'addLanguage'])->name('add.language');
    Route::post('/admin/languages/store', [LanguageController::class, 'storeLanguage'])->name('store.language');
    Route::get('/admin/languages/edit/{id}', [LanguageController::class, 'editLanguage'])->name('edit.language');
    Route::post('/admin/languages/update', [LanguageController::class, 'updateLanguage'])->name('update.language');
    Route::get('/admin/languages/delete/{id}', [LanguageController::class, 'deleteLanguage'])->name('delete.language');

    // إدارة الترجمات (Translations)
    Route::get('/admin/translations/language/{language_id}', [LanguageController::class, 'allTranslations'])->name('all.translations');
    Route::get('/admin/translations/language/{language_id}/add', [LanguageController::class, 'addTranslation'])->name('add.translation');
    Route::post('/admin/translations/store', [LanguageController::class, 'storeTranslation'])->name('store.translation');
    Route::get('/admin/translations/edit/{id}', [LanguageController::class, 'editTranslation'])->name('edit.translation');
    Route::post('/admin/translations/update', [LanguageController::class, 'updateTranslation'])->name('update.translation');
    Route::get('/admin/translations/delete/{id}', [LanguageController::class, 'deleteTranslation'])->name('delete.translation');

    // إدارة التسويق بالعمولة (Affiliate Links & Trackings)
    Route::get('/admin/affiliate/all', [AffiliateController::class, 'allAffiliates'])->name('all.affiliates');
    Route::get('/admin/affiliate/add', [AffiliateController::class, 'addAffiliate'])->name('add.affiliate');
    Route::post('/admin/affiliate/store', [AffiliateController::class, 'storeAffiliate'])->name('store.affiliate');
    Route::get('/admin/affiliate/edit/{id}', [AffiliateController::class, 'editAffiliate'])->name('edit.affiliate');
    Route::post('/admin/affiliate/update', [AffiliateController::class, 'updateAffiliate'])->name('update.affiliate');
    Route::get('/admin/affiliate/delete/{id}', [AffiliateController::class, 'deleteAffiliate'])->name('delete.affiliate');
    Route::get('/admin/affiliate/{id}/trackings', [AffiliateController::class, 'getTrackings'])->name('affiliate.trackings');
    Route::get('/admin/affiliate-trackings', [AffiliateController::class, 'allTrackings'])->name('all.affiliate_trackings');
    Route::get('/admin/affiliate-trackings/delete/{id}', [AffiliateController::class, 'deleteTracking'])->name('delete.affiliate_tracking');

    // إدارة الرتب والمستويات (Rankings & Levels)
    Route::get('/admin/rankings/all', [RankingController::class, 'allRankings'])->name('all.rankings');
    Route::get('/admin/rankings/add', [RankingController::class, 'addRanking'])->name('add.ranking');
    Route::post('/admin/rankings/store', [RankingController::class, 'storeRanking'])->name('store.ranking');
    Route::get('/admin/rankings/edit/{id}', [RankingController::class, 'editRanking'])->name('edit.ranking');
    Route::post('/admin/rankings/update', [RankingController::class, 'updateRanking'])->name('update.ranking');
    Route::get('/admin/rankings/delete/{id}', [RankingController::class, 'deleteRanking'])->name('delete.ranking');
    
    // رتب ومستويات المستخدمين
    Route::get('/admin/users/rankings', [RankingController::class, 'usersRankings'])->name('users.rankings');

    // إدارة التقارير والمتابعة (Reports & Analytics)
    Route::get('/admin/reports', [ReportController::class, 'reportView'])->name('report.view');
    Route::post('/admin/reports/search-by-date', [ReportController::class, 'searchByDate'])->name('search-by-date');
    Route::post('/admin/reports/search-by-month', [ReportController::class, 'searchByMonth'])->name('search-by-month');
    Route::post('/admin/reports/search-by-year', [ReportController::class, 'searchByYear'])->name('search-by-year');
    Route::post('/admin/reports/search-by-range', [ReportController::class, 'searchByRange'])->name('search-by-range');

    // إرسال الإشعارات (Send Notifications Form)
    Route::get('/admin/notifications/send', [NotificationController::class, 'create'])->name('admin.notifications.create');
    Route::post('/admin/notifications/send', [NotificationController::class, 'store'])->name('admin.notifications.store');

    // البحث العام الشامل (Global Search Dashboard)
    Route::get('/admin/search', [SearchController::class, 'searchForm'])->name('admin.global_search');

    // إدارة تذاكر الدعم الفني للتواصل المباشر
    Route::get('/admin/support-tickets', [SupportTicketController::class, 'index'])->name('admin.support_tickets.index');
    Route::get('/admin/support-tickets/create', [SupportTicketController::class, 'create'])->name('admin.support_tickets.create');
    Route::post('/admin/support-tickets/store', [SupportTicketController::class, 'store'])->name('admin.support_tickets.store');
    Route::get('/admin/support-tickets/{id}', [SupportTicketController::class, 'show'])->name('admin.support_tickets.show');
    Route::post('/admin/support-tickets/{id}/reply', [SupportTicketController::class, 'storeReply'])->name('admin.support_tickets.reply');
    Route::post('/admin/support-tickets/{id}/status', [SupportTicketController::class, 'updateStatus'])->name('admin.support_tickets.status');
    Route::post('/admin/support-tickets/{id}/priority', [SupportTicketController::class, 'updatePriority'])->name('admin.support_tickets.priority');
    Route::get('/admin/support-tickets/delete/{id}', [SupportTicketController::class, 'destroy'])->name('admin.support_tickets.delete');

    // إدارة لجنة الحكماء وغرفة الاجتماعات
    Route::get('/admin/wise-committees', [WiseCommitteeController::class, 'index'])->name('admin.wise_committees.index');
    Route::post('/admin/wise-committees/store', [WiseCommitteeController::class, 'store'])->name('admin.wise_committees.store');
    Route::get('/admin/wise-committees/toggle/{id}', [WiseCommitteeController::class, 'toggleStatus'])->name('admin.wise_committees.toggle');
    Route::get('/admin/wise-committees/delete/{id}', [WiseCommitteeController::class, 'destroy'])->name('admin.wise_committees.delete');
    Route::get('/admin/wise-committees/ratings', [WiseCommitteeController::class, 'postRatings'])->name('admin.wise_committees.ratings');
    Route::post('/admin/wise-committees/ratings/store', [WiseCommitteeController::class, 'storeRating'])->name('admin.wise_committees.store_rating');

    // تقييم الأعضاء (منح النقاط وسجل التقييمات)
    Route::get('/admin/wise-committees/member-ratings', [WiseCommitteeController::class, 'memberRatings'])->name('admin.wise_committees.member_ratings');
    Route::post('/admin/wise-committees/member-ratings/store', [WiseCommitteeController::class, 'storeMemberRating'])->name('admin.wise_committees.store_member_rating');
    Route::get('/admin/wise-committees/member-ratings/delete/{id}', [WiseCommitteeController::class, 'destroyMemberRating'])->name('admin.wise_committees.delete_member_rating');

    // إعدادات إصدارات التطبيق (App Versions)
    Route::get('/admin/app-versions', [AppVersionController::class, 'index'])->name('admin.app_versions.index');
    Route::get('/admin/app-versions/add', [AppVersionController::class, 'create'])->name('admin.app_versions.create');
    Route::post('/admin/app-versions/store', [AppVersionController::class, 'store'])->name('admin.app_versions.store');
    Route::get('/admin/app-versions/edit/{id}', [AppVersionController::class, 'edit'])->name('admin.app_versions.edit');
    Route::post('/admin/app-versions/update', [AppVersionController::class, 'update'])->name('admin.app_versions.update');
    Route::get('/admin/app-versions/delete/{id}', [AppVersionController::class, 'destroy'])->name('admin.app_versions.delete');
});

// تسجيل مسارات تجريبية (Placeholder Routes) للمسارات المستعملة في القائمة الجانبية والشاشات لتجنب الأخطاء
$dummyRoutes = [
    'all.home_sliders', 'add.home_slider', 'all.contact.us', 'all.news', 'add.news', 'add.news.store',
    'admin.ai_news.generator', 'all.news.categories', 'add.news.category', 'all.articles', 'add.article',
    'all.sound.libraries', 'add.sound.library', 'all.sound.categories', 'add.sound.category', 'all.sound.authors',
    'add.sound.author', 'admin.news_eye.index', 'all.help.requests', 'all.help.categories', 'add.help.category',
    'all.category', 'add.category', 'all.question', 'add.question', 'all.gallery', 'add.gallery', 'all.teamworks',
    'add.teamwork', 'contact.info', 'all.social_media', 'add.social_media', 'all.user_services', 'add.user_service',
    'market.main_categories.index', 'market.sub_categories.index', 'market.sub_sub_categories.index',
    'market.items.index', 'all.notification',
    'send.notification', 'all.coupon', 'add.coupon', 'add.versions', 'front.groups.index',
    'admin.change.password', 'notification.read'
];

Route::middleware(['auth', 'admin'])->group(function () use ($dummyRoutes) {
    foreach ($dummyRoutes as $routeName) {
        if (!Route::has($routeName)) {
            Route::get('/admin/dummy/' . str_replace('.', '/', $routeName), function () use ($routeName) {
                return "Placeholder for: " . $routeName;
            })->name($routeName);
        }
    }
});



Route::get('/groups', [GroupSiteController::class, 'indexFrontendGroups'])->name('frontend.groups');
Route::get('/groups/{id}', [GroupSiteController::class, 'showFrontendGroupDetails'])->name('frontend.groups.details');
Route::get('/groups/{id}/subjects-api', [GroupSiteController::class, 'getGroupSubjectsApi'])->name('frontend.groups.subjects.api');

Route::get('/sage-committee', function () {
    $committeeMembers = \App\Models\WiseCommittee::with('user')->where('is_active', true)->latest()->get();
    return view('frontend.wiselook.pages.sage_committee', compact('committeeMembers'));
})->name('frontend.sage_committee');

Route::get('/wise-rated-posts', [PostController::class, 'wiseRatedIndex'])->name('frontend.wise_rated.index');

Route::get('/messages/{receiverId?}', [ChatController::class, 'index'])->name('frontend.messages')->middleware('auth');

Route::get('/search', [SearchController::class, 'searchFrontend'])->name('frontend.search');
Route::get('/tags/{name}', [HashtagController::class, 'show'])->name('frontend.hashtags.show');
Route::get('/post/{id}', [PostController::class, 'showPostPublic'])->name('frontend.posts.show');
Route::get('/language/switch/{code}', [LanguageController::class, 'switchLanguage'])->name('language.switch');

Route::get('/posts/{id}/comments', [PostController::class, 'getPostComments'])->name('frontend.posts.comments');
Route::get('/comments/{id}/reactions', [PostController::class, 'getCommentReactions'])->name('frontend.comments.reactions');
Route::get('/posts/{id}/reactions', [PostController::class, 'getPostReactionsPublic'])->name('frontend.posts.reactions');

Route::middleware('auth')->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'editProfile'])->name('profile.edit_form');
    Route::get('/profile/{id?}', [ProfileController::class, 'showProfile'])->name('profile.edit');
    Route::get('/profile/{id}/friends', [ProfileController::class, 'showFriends'])->name('profile.friends');
    Route::get('/profile-posts/api/{id?}', [ProfileController::class, 'getProfilePostsApi'])->name('profile.posts.api');
    Route::get('/profile/points-details/{id}', [ProfileController::class, 'getPointsDetails'])->name('profile.points_details');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update_form');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Front posts creation
    Route::post('/posts/store', [PostController::class, 'storeFrontendPost'])->name('frontend.posts.store');
    Route::post('/polls/vote', [PostController::class, 'votePoll'])->name('frontend.polls.vote');
    Route::post('/posts/{id}/comments', [PostController::class, 'storeCommentFrontend'])->name('frontend.posts.comments.store');
    Route::post('/comments/{id}/react', [PostController::class, 'reactCommentFrontend'])->name('frontend.comments.react');
    Route::post('/comments/{id}/delete', [PostController::class, 'deleteCommentFrontend'])->name('frontend.comments.delete');
    Route::post('/posts/{id}/react', [PostController::class, 'reactPostFrontend'])->name('frontend.posts.react');
    Route::post('/posts/{id}/delete', [PostController::class, 'deleteFrontendPost'])->name('frontend.posts.delete');
    
    // Notifications
    Route::get('/notifications', [ProfileController::class, 'showNotifications'])->name('frontend.notifications');
    Route::get('/notifications/api', [ProfileController::class, 'getNotificationsApi'])->name('frontend.notifications.api');
    Route::post('/notifications/mark-read', [ProfileController::class, 'markAllRead'])->name('frontend.notifications.mark_read');
    Route::post('/notifications/mark-single-read', [ProfileController::class, 'markSingleRead'])->name('frontend.notifications.mark_single_read');
    
    // Friends search for mention
    Route::get('/friends/search', [FriendshipController::class, 'getFriendsSearch'])->name('frontend.friends.search');
    
    // Send friend request via AJAX
    Route::post('/friendships/request', [FriendshipController::class, 'sendFriendRequest'])->name('frontend.friendships.request');
    
    // Get mutual friends via AJAX
    Route::get('/friends/mutual/{otherUserId}', [FriendshipController::class, 'getMutualFriends'])->name('frontend.friends.mutual');

    // My Network view and actions
    Route::get('/my-network', [FriendshipController::class, 'myNetwork'])->name('frontend.my_network');
    Route::post('/friendships/accept/{id}', [FriendshipController::class, 'activeFriendship'])->name('frontend.friendships.accept');
    Route::post('/friendships/delete/{id}', [FriendshipController::class, 'deleteFriendship'])->name('frontend.friendships.delete');

    // Saved posts
    Route::get('/saved-posts', [SavedPostController::class, 'indexFrontend'])->name('frontend.saved_posts');
    Route::post('/posts/{id}/save', [SavedPostController::class, 'toggleSaveFrontend'])->name('frontend.posts.save');

    // Ambassadors view and actions
    Route::get('/ambassadors', [AffiliateController::class, 'frontendAmbassadors'])->name('frontend.ambassadors');
    Route::post('/ambassadors/update-code', [AffiliateController::class, 'updateReferralCode'])->name('frontend.ambassadors.update_code');

    // Frontend Stories
    Route::post('/stories/store', [StoryController::class, 'storeFrontendStory'])->name('frontend.stories.store');
    Route::post('/stories/{id}/seen', [StoryController::class, 'markFrontendStorySeen'])->name('frontend.stories.seen');
    Route::get('/stories/{id}/viewers', [StoryController::class, 'getStoryViewers'])->name('frontend.stories.viewers');
    Route::post('/stories/{id}/delete', [StoryController::class, 'deleteFrontendStory'])->name('frontend.stories.delete');

    // Frontend Groups actions
    Route::post('/groups/store', [GroupSiteController::class, 'storeFrontendGroupSite'])->name('frontend.groups.store');
    Route::post('/groups/{id}/join', [GroupSiteController::class, 'joinGroupSite'])->name('frontend.groups.join');
    Route::post('/groups/{id}/leave', [GroupSiteController::class, 'leaveGroupSite'])->name('frontend.groups.leave');
    Route::post('/groups/{id}/delete', [GroupSiteController::class, 'deleteGroupSiteFrontend'])->name('frontend.groups.delete');
    Route::post('/groups/{id}/update-api', [GroupSiteController::class, 'updateGroupSiteApi'])->name('frontend.groups.update_api');
    Route::post('/groups/{group_id}/subjects/store', [GroupSiteController::class, 'storeGroupSubject'])->name('frontend.groups.subjects.store');
    Route::post('/groups/subjects/{id}/react', [GroupSiteController::class, 'toggleSubjectReaction'])->name('frontend.groups.subjects.react');
    Route::get('/groups/subjects/{id}/reactions', [GroupSiteController::class, 'getSubjectSupporters'])->name('frontend.groups.subjects.supporters');
    Route::get('/groups/subjects/{id}/comments', [GroupSiteController::class, 'getSubjectComments'])->name('frontend.groups.subjects.comments');
    Route::post('/groups/subjects/{id}/comments', [GroupSiteController::class, 'storeSubjectComment'])->name('frontend.groups.subjects.comments.store');
    Route::post('/groups/subjects/comments/{id}/react', [GroupSiteController::class, 'reactSubjectComment'])->name('frontend.groups.subjects.comments.react');
    Route::get('/groups/subjects/comments/{id}/reactions', [GroupSiteController::class, 'getSubjectCommentReactions'])->name('frontend.groups.subjects.comments.reactions');
    Route::post('/groups/subjects/{id}/delete', [GroupSiteController::class, 'deleteFrontendSubject'])->name('frontend.groups.subjects.delete');
    Route::post('/groups/subjects/comments/{id}/delete', [GroupSiteController::class, 'deleteFrontendComment'])->name('frontend.groups.comments.delete');
    Route::post('/groups/{group_id}/members/{user_id}/kick', [GroupSiteController::class, 'kickFrontendMember'])->name('frontend.groups.members.kick');
});


require __DIR__.'/auth.php';
