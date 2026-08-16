<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\GroupSite;
use App\Models\GroupSiteUser;
use App\Models\GroupSubject;
use App\Models\GroupSiteComment;
use App\Models\GroupSiteSubjectReaction;
use App\Models\Reaction;
use App\Models\User;

class GroupSiteApiController extends Controller
{
    /**
     * 1. جلب المجموعات حسب التاب (my_groups, joined_groups, available_groups)
     */
    public function listGroups(Request $request)
    {
        // المصادقة الاختيارية - يعمل مع وبدون تسجيل الدخول
        $currentUser = null;
        try {
            $currentUser = auth('sanctum')->user();
        } catch (\Exception $e) {}
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        $currentUserId = $currentUser ? (int)$currentUser->id : 0;


        $type = $request->input('type', 'available_groups');
        $limit = (int)$request->input('limit', 20);
        $offset = (int)$request->input('offset', 0);
        $search = $request->input('search');

        $query = GroupSite::with(['admin'])
            ->withCount(['members', 'subjects'])
            ->where('status', '!=', 'archived');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($type === 'my_groups') {
            if (!$currentUserId) {
                return response()->json(['success' => true, 'data' => [], 'groups' => []]);
            }
            $query->where('admin_user_id', $currentUserId);
        } elseif ($type === 'joined_groups') {
            if (!$currentUserId) {
                return response()->json(['success' => true, 'data' => [], 'groups' => []]);
            }
            $query->whereHas('members', fn($q) => $q->where('user_id', $currentUserId))
                  ->where('admin_user_id', '!=', $currentUserId);
        } else {
            // available_groups (الكل)
        }

        $groups = $query->latest()
                        ->skip($offset)
                        ->take($limit)
                        ->get();

        $formattedGroups = $groups->map(function ($g) use ($currentUserId) {
            $admin = $g->admin;
            $adminAvatar = asset('images/default_profile.png');
            if ($admin && $admin->profile_picture && $admin->profile_picture !== 'non') {
                $adminAvatar = filter_var($admin->profile_picture, FILTER_VALIDATE_URL)
                    ? $admin->profile_picture
                    : asset('new_wiselook/uploads/' . $admin->profile_picture);
            }

            $groupImg = asset('images/default_group.png');
            if ($g->image_path) {
                $groupImg = filter_var($g->image_path, FILTER_VALIDATE_URL)
                    ? $g->image_path
                    : asset('upload/group_site_images/' . $g->image_path);
            }

            $logoImg = null;
            if ($g->logo_path) {
                $logoImg = filter_var($g->logo_path, FILTER_VALIDATE_URL)
                    ? $g->logo_path
                    : asset('upload/group_site_logos/' . basename($g->logo_path));
            }

            $isJoined = false;
            if ($currentUserId > 0) {
                $isJoined = GroupSiteUser::where('group_site_id', $g->id)
                    ->where('user_id', $currentUserId)
                    ->exists();
            }

            return [
                'id'             => (int)$g->id,
                'title'          => $g->title ?? '',
                'description'    => $g->description ?? '',
                'image_path'     => $groupImg,
                'logo_path'      => $logoImg,
                'status'         => $g->status ?? 'open',
                'invite_code'    => $g->invite_code ?? '',
                'admin_user_id'  => (int)$g->admin_user_id,
                'admin_name'     => $admin ? trim(($admin->first_name ?? '') . ' ' . ($admin->last_name ?? '')) : 'مشرف المجموعة',
                'admin_avatar'   => $adminAvatar,
                'members_count'  => (int)($g->members_count ?? 0),
                'subjects_count' => (int)($g->subjects_count ?? 0),
                'is_joined'      => $isJoined ? 1 : 0,
                'is_admin'       => ($currentUserId > 0 && $g->admin_user_id == $currentUserId) ? 1 : 0,
                'created_at'     => $g->created_at ? $g->created_at->diffForHumans() : '',
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $formattedGroups,
            'groups'  => $formattedGroups,
        ]);
    }

    /**
     * 2. الإنضمام إلى مجموعة
     */
    public function joinGroup(Request $request)
    {
        $currentUser = $request->user() ?? auth('sanctum')->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $groupId = $request->input('group_id');
        $group = GroupSite::find($groupId);
        if (!$group) {
            return response()->json(['success' => false, 'message' => 'المجموعة غير موجودة'], 404);
        }

        if ($group->status === 'closed' && $group->invite_code) {
            $code = $request->input('invite_code');
            if (trim($code) !== trim($group->invite_code)) {
                return response()->json(['success' => false, 'message' => 'كود الدعوة غير صحيح للمجموعة المغلقة'], 422);
            }
        }

        GroupSiteUser::firstOrCreate([
            'group_site_id' => $group->id,
            'user_id'       => $currentUser->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم الإنضمام إلى المجموعة بنجاح',
        ]);
    }

    /**
     * 3. المغادرة من مجموعة
     */
    public function leaveGroup(Request $request)
    {
        $currentUser = $request->user() ?? auth('sanctum')->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $groupId = $request->input('group_id');
        $group = GroupSite::find($groupId);
        if (!$group) {
            return response()->json(['success' => false, 'message' => 'المجموعة غير موجودة'], 404);
        }

        if ($group->admin_user_id == $currentUser->id) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك مغادرة المجموعة لأنك المشرف الحالي لها'], 422);
        }

        GroupSiteUser::where('group_site_id', $group->id)
            ->where('user_id', $currentUser->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'تمت مغادرة المجموعة بنجاح',
        ]);
    }

    /**
     * 4. إنشاء مجموعة جديدة
     */
    public function createGroup(Request $request)
    {
        $currentUser = $request->user() ?? auth('sanctum')->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:open,closed',
            'invite_code' => 'nullable|string|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        try {
            $group = new GroupSite();
            $group->title = $request->title;
            $group->description = $request->description;
            $group->status = $request->status;
            $group->invite_code = $request->status === 'closed' ? $request->invite_code : null;
            $group->admin_user_id = $currentUser->id;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = date('YmdHis') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('upload/group_site_images'), $filename);
                $group->image_path = $filename;
            }

            if ($request->hasFile('logo')) {
                $logoFile = $request->file('logo');
                $logoName = date('YmdHis') . '_logo_' . uniqid() . '.' . $logoFile->getClientOriginalExtension();
                $logoFile->move(public_path('upload/group_site_logos'), $logoName);
                $group->logo_path = $logoName;
            }

            $group->save();

            GroupSiteUser::firstOrCreate([
                'group_site_id' => $group->id,
                'user_id'       => $currentUser->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء المجموعة بنجاح',
                'group'   => [
                    'id'    => (int)$group->id,
                    'title' => $group->title,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'فشل إنشاء المجموعة: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 4.6 تعديل بيانات المجموعة (متاحة لمنشئ المجموعة فقط)
     */
    public function updateGroup(Request $request)
    {
        $currentUser = $request->user() ?? auth('sanctum')->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'group_id'    => 'required|integer|exists:group_sites,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'nullable|in:open,closed',
            'invite_code' => 'nullable|string|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8192',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8192',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $group = GroupSite::find($request->group_id);
        if (!$group) {
            return response()->json(['success' => false, 'message' => 'المجموعة غير موجودة'], 404);
        }

        if ((int)$group->admin_user_id !== (int)$currentUser->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بتعديل هذه المجموعة'], 403);
        }

        DB::beginTransaction();
        try {
            $group->title = $request->title;
            if ($request->has('description')) {
                $group->description = $request->description;
            }
            if ($request->has('status') && in_array($request->status, ['open', 'closed'])) {
                $group->status = $request->status;
                if ($group->status === 'closed' && $request->has('invite_code')) {
                    $group->invite_code = $request->invite_code;
                }
            }

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = date('YmdHis') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('upload/group_site_images'), $filename);
                $group->image_path = $filename;
            }

            if ($request->hasFile('logo')) {
                $logoFile = $request->file('logo');
                $logoName = date('YmdHis') . '_logo_' . uniqid() . '.' . $logoFile->getClientOriginalExtension();
                $logoFile->move(public_path('upload/group_site_logos'), $logoName);
                $group->logo_path = $logoName;
            }

            $group->save();
            DB::commit();

            $groupImg = asset('images/default_group.png');
            if ($group->image_path) {
                $groupImg = filter_var($group->image_path, FILTER_VALIDATE_URL)
                    ? $group->image_path
                    : asset('upload/group_site_images/' . $group->image_path);
            }

            $logoImg = null;
            if ($group->logo_path) {
                $logoImg = filter_var($group->logo_path, FILTER_VALIDATE_URL)
                    ? $group->logo_path
                    : asset('upload/group_site_logos/' . basename($group->logo_path));
            }

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات المجموعة بنجاح',
                'group'   => [
                    'id'             => (int)$group->id,
                    'title'          => $group->title,
                    'description'    => $group->description ?? '',
                    'image_path'     => $groupImg,
                    'logo_path'      => $logoImg,
                    'status'         => $group->status,
                    'admin_user_id'  => (int)$group->admin_user_id,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'فشل تحديث المجموعة: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 5. تفاصيل المجموعة ومواضيعها
     */
    public function getGroupDetails(Request $request)
    {
        // المصادقة الاختيارية
        $currentUser = null;
        try {
            $currentUser = auth('sanctum')->user();
        } catch (\Exception $e) {}
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        $currentUserId = $currentUser ? (int)$currentUser->id : 0;

        $groupId = $request->input('group_id') ?? $request->route('id');
        $group = GroupSite::with(['admin'])->withCount(['members', 'subjects'])->find($groupId);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'المجموعة غير موجودة'], 404);
        }

        $admin = $group->admin;
        $adminAvatar = asset('images/default_profile.png');
        if ($admin && $admin->profile_picture && $admin->profile_picture !== 'non') {
            $adminAvatar = filter_var($admin->profile_picture, FILTER_VALIDATE_URL)
                ? $admin->profile_picture
                : asset('new_wiselook/uploads/' . $admin->profile_picture);
        }

        $groupImg = asset('images/default_group.png');
        if ($group->image_path) {
            $groupImg = filter_var($group->image_path, FILTER_VALIDATE_URL)
                ? $group->image_path
                : asset('upload/group_site_images/' . $group->image_path);
        }

        $logoImg = null;
        if ($group->logo_path) {
            $logoImg = filter_var($group->logo_path, FILTER_VALIDATE_URL)
                ? $group->logo_path
                : asset('upload/group_site_logos/' . basename($group->logo_path));
        }

        $isJoined = false;
        if ($currentUserId > 0) {
            $isJoined = GroupSiteUser::where('group_site_id', $group->id)
                ->where('user_id', $currentUserId)
                ->exists();
        }

        $limit = (int)$request->input('limit', 20);
        $offset = (int)$request->input('offset', 0);

        $subjects = GroupSubject::with(['user'])
            ->withCount(['comments', 'reactions'])
            ->where('group_site_id', $group->id)
            ->latest()
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($s) use ($currentUserId) {
                $u = $s->user;
                $userPic = asset('images/default_profile.png');
                if ($u && $u->profile_picture && $u->profile_picture !== 'non') {
                    $userPic = filter_var($u->profile_picture, FILTER_VALIDATE_URL)
                        ? $u->profile_picture
                        : asset('new_wiselook/uploads/' . $u->profile_picture);
                }

                $attachUrl = null;
                if ($s->attachment_path) {
                    $attachUrl = filter_var($s->attachment_path, FILTER_VALIDATE_URL)
                        ? $s->attachment_path
                        : asset('upload/group_site_subjects/' . $s->attachment_path);
                }

                return [
                    'id'              => (int)$s->id,
                    'group_site_id'   => (int)$s->group_site_id,
                    'user_id'         => (int)$s->user_id,
                    'user_name'       => $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : 'مستخدم',
                    'user_avatar'     => $userPic,
                    'title'           => $s->title ?? '',
                    'description'     => $s->description ?? '',
                    'attachment_type' => $s->attachment_type ?? 'none',
                    'attachment_path' => $attachUrl,
                    'likes_count'     => (int)($s->likes ?? 0),
                    'comments_count'  => (int)($s->comments_count ?? 0),
                    'is_liked'        => $currentUserId > 0
                        ? GroupSiteSubjectReaction::where('group_subject_id', $s->id)
                            ->where('user_id', $currentUserId)
                            ->where('type', 'like')
                            ->exists() ? 1 : 0
                        : 0,
                    'created_at'      => $s->created_at ? $s->created_at->diffForHumans() : '',
                ];
            });

        return response()->json([
            'success' => true,
            'group'   => [
                'id'             => (int)$group->id,
                'title'          => $group->title,
                'description'    => $group->description ?? '',
                'image_path'     => $groupImg,
                'logo_path'      => $logoImg,
                'status'         => $group->status,
                'admin_user_id'  => (int)$group->admin_user_id,
                'admin_name'     => $admin ? trim(($admin->first_name ?? '') . ' ' . ($admin->last_name ?? '')) : 'مشرف المجموعة',
                'admin_avatar'   => $adminAvatar,
                'members_count'  => (int)$group->members_count,
                'subjects_count' => (int)$group->subjects_count,
                'is_joined'      => $isJoined ? 1 : 0,
                'is_admin'       => ($currentUserId > 0 && $group->admin_user_id == $currentUserId) ? 1 : 0,
            ],
            'subjects' => $subjects,
        ]);
    }

    /**
     * 6. إضافة موضوع جديد داخل مجموعة
     */
    public function addSubject(Request $request)
    {
        $currentUser = $request->user() ?? auth('sanctum')->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'group_site_id' => 'required|integer|exists:group_sites,id',
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'attachment'    => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $subject = new GroupSubject();
        $subject->group_site_id = $request->group_site_id;
        $subject->user_id = $currentUser->id;
        $subject->title = $request->title;
        $subject->description = $request->description;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $ext = strtolower($file->getClientOriginalExtension());
            $filename = date('YmdHis') . '_' . uniqid() . '.' . $ext;
            $file->move(public_path('upload/group_site_subjects'), $filename);

            $subject->attachment_path = $filename;
            $subject->attachment_type = in_array($ext, ['mp4', 'mov', 'avi']) ? 'video' : 'image';
        }

        $subject->save();
        $subject->syncHashtags();

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الموضوع للمجموعة بنجاح',
            'subject' => [
                'id'    => (int)$subject->id,
                'title' => $subject->title,
            ]
        ], 201);
    }

    /**
     * 7. تبديل الإعجاب (like / unlike) بموضوع مجموعة
     */
    public function toggleSubjectReaction(Request $request)
    {
        $currentUser = $request->user() ?? auth('sanctum')->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $subjectId = $request->input('subject_id');
        $reactionType = $request->input('reaction_type', 'like'); // 'like' or 'remove'

        $subject = GroupSubject::find($subjectId);
        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'الموضوع غير موجود'], 404);
        }

        DB::beginTransaction();
        try {
            if ($reactionType === 'like') {
                GroupSiteSubjectReaction::updateOrCreate(
                    [
                        'user_id'          => $currentUser->id,
                        'group_subject_id' => $subjectId,
                    ],
                    ['type' => 'like']
                );
            } else {
                GroupSiteSubjectReaction::where('user_id', $currentUser->id)
                    ->where('group_subject_id', $subjectId)
                    ->delete();
            }

            // تحديث عدد الإعجابات في جدول المواضيع
            $likesCount = GroupSiteSubjectReaction::where('group_subject_id', $subjectId)
                ->where('type', 'like')
                ->count();

            $subject->update(['likes' => $likesCount]);

            DB::commit();

            return response()->json([
                'success'    => true,
                'like_count' => $likesCount,
                'is_liked'   => $reactionType === 'like' ? 1 : 0,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 8. جلب تعليقات موضوع في مجموعة
     */
    public function getSubjectComments(Request $request)
    {
        $currentUser = null;
        try {
            $currentUser = auth('sanctum')->user();
        } catch (\Exception $e) {}
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        $currentUserId = $currentUser ? (int)$currentUser->id : 0;

        $subjectId = $request->input('subject_id') ?? $request->input('group_subject_id');
        if (!$subjectId) {
            return response()->json(['success' => false, 'message' => 'رقم الموضوع مطلوب'], 400);
        }

        $comments = GroupSiteComment::with(['user', 'replies.user'])
            ->where('group_subject_id', $subjectId)
            ->where('parent_id', 0)
            ->latest()
            ->get();

        $formatted = $comments->map(function ($comment) use ($currentUserId) {
            $u = $comment->user;
            $userPic = asset('images/default_profile.png');
            if ($u && $u->profile_picture && $u->profile_picture !== 'non') {
                $userPic = filter_var($u->profile_picture, FILTER_VALIDATE_URL)
                    ? $u->profile_picture
                    : asset('new_wiselook/uploads/' . $u->profile_picture);
            }

            $isLikedComment = $currentUserId > 0
                ? Reaction::where('user_id', $currentUserId)
                    ->where('content_id', $comment->id)
                    ->where('content_type_id', 3)
                    ->where('is_active', 1)
                    ->exists()
                : false;

            return [
                'id'              => (int)$comment->id,
                'group_subject_id'=> (int)$comment->group_subject_id,
                'user_id'         => (int)$comment->user_id,
                'user_name'       => $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : 'مستخدم',
                'user_avatar'     => $userPic,
                'content'         => $comment->content ?? '',
                'parent_id'       => (int)($comment->parent_id ?? 0),
                'likes_count'     => (int)($comment->reaction_count ?? 0),
                'is_liked'        => $isLikedComment ? 1 : 0,
                'created_at'      => $comment->created_at ? $comment->created_at->diffForHumans() : 'الآن',
                'replies'         => ($comment->replies ?? collect())->map(function ($reply) use ($currentUserId) {
                    $ru = $reply->user;
                    $rPic = asset('images/default_profile.png');
                    if ($ru && $ru->profile_picture && $ru->profile_picture !== 'non') {
                        $rPic = filter_var($ru->profile_picture, FILTER_VALIDATE_URL)
                            ? $ru->profile_picture
                            : asset('new_wiselook/uploads/' . $ru->profile_picture);
                    }
                    $isLikedReply = $currentUserId > 0
                        ? Reaction::where('user_id', $currentUserId)
                            ->where('content_id', $reply->id)
                            ->where('content_type_id', 4)
                            ->where('is_active', 1)
                            ->exists()
                        : false;
                    return [
                        'id'              => (int)$reply->id,
                        'group_subject_id'=> (int)$reply->group_subject_id,
                        'user_id'         => (int)$reply->user_id,
                        'user_name'       => $ru ? trim(($ru->first_name ?? '') . ' ' . ($ru->last_name ?? '')) : 'مستخدم',
                        'user_avatar'     => $rPic,
                        'content'         => $reply->content ?? '',
                        'parent_id'       => (int)($reply->parent_id ?? 0),
                        'likes_count'     => (int)($reply->reaction_count ?? 0),
                        'is_liked'        => $isLikedReply ? 1 : 0,
                        'created_at'      => $reply->created_at ? $reply->created_at->diffForHumans() : 'الآن',
                    ];
                })->values()
            ];
        });

        $totalComments = GroupSiteComment::where('group_subject_id', $subjectId)->count();

        return response()->json([
            'success'        => true,
            'comments'       => $formatted,
            'comments_count' => $totalComments,
        ]);
    }


    /**
     * 9. إضافة تعليق موضوع في مجموعة
     */
    public function addSubjectComment(Request $request)
    {
        $currentUser = $request->user() ?? auth('sanctum')->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $subjectId = $request->input('subject_id') ?? $request->input('group_subject_id');
        $content = trim($request->input('content', ''));
        $parentId = (int)$request->input('parent_id', 0);

        if (!$subjectId || empty($content)) {
            return response()->json(['success' => false, 'message' => 'يرجى كتابة نص التعليق'], 422);
        }

        $subject = GroupSubject::find($subjectId);
        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'الموضوع غير موجود'], 404);
        }

        $comment = GroupSiteComment::create([
            'group_subject_id' => (int)$subjectId,
            'user_id'          => $currentUser->id,
            'parent_id'        => $parentId,
            'content'          => $content,
            'reaction_count'   => 0,
            'reply_count'      => 0,
        ]);

        if ($parentId > 0) {
            GroupSiteComment::where('id', $parentId)->increment('reply_count');
        }

        $totalComments = GroupSiteComment::where('group_subject_id', $subjectId)->count();

        $userPic = asset('images/default_profile.png');
        if ($currentUser->profile_picture && $currentUser->profile_picture !== 'non') {
            $userPic = filter_var($currentUser->profile_picture, FILTER_VALIDATE_URL)
                ? $currentUser->profile_picture
                : asset('new_wiselook/uploads/' . $currentUser->profile_picture);
        }

        return response()->json([
            'success'        => true,
            'message'        => 'تم إضافة التعليق بنجاح',
            'comments_count' => $totalComments,
            'comment'        => [
                'id'              => (int)$comment->id,
                'group_subject_id'=> (int)$comment->group_subject_id,
                'user_id'         => (int)$comment->user_id,
                'user_name'       => trim(($currentUser->first_name ?? '') . ' ' . ($currentUser->last_name ?? '')),
                'user_avatar'     => $userPic,
                'content'         => $comment->content,
                'parent_id'       => (int)$comment->parent_id,
                'likes_count'     => 0,
                'is_liked'        => 0,
                'created_at'      => 'الآن',
                'replies'         => [],
            ],
        ], 201);
    }

    /**
     * 10. التفاعل بالإعجاب مع تعليق أو رد
     */
    public function reactSubjectComment(Request $request)
    {
        $currentUser = $request->user() ?? auth('sanctum')->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $commentId = $request->input('comment_id');
        $reactionType = $request->input('reaction_type', 'like'); // 'like' or 'remove'

        $comment = GroupSiteComment::find($commentId);
        if (!$comment) {
            return response()->json(['success' => false, 'message' => 'التعليق غير موجود'], 404);
        }

        $contentTypeId = $comment->parent_id > 0 ? 4 : 3;

        if ($reactionType === 'like') {
            Reaction::updateOrCreate(
                [
                    'user_id'          => $currentUser->id,
                    'content_id'       => $commentId,
                    'content_type_id'  => $contentTypeId,
                    'reaction_type_id' => 1
                ],
                [
                    'is_active' => 1
                ]
            );
        } else {
            Reaction::where('user_id', $currentUser->id)
                ->where('content_id', $commentId)
                ->where('content_type_id', $contentTypeId)
                ->update(['is_active' => 0]);
        }

        $likesCount = Reaction::where('content_id', $commentId)
            ->where('content_type_id', $contentTypeId)
            ->where('is_active', 1)
            ->count();

        $comment->update(['reaction_count' => $likesCount]);

        return response()->json([
            'success'     => true,
            'likes_count' => $likesCount,
            'is_liked'    => $reactionType === 'like' ? 1 : 0,
        ]);
    }

    /**
     * 11. جلب قائمة المعجبين بتعليق أو رد
     */
    public function getCommentReactions(Request $request)
    {
        $commentId = $request->input('comment_id');
        $comment = GroupSiteComment::find($commentId);
        if (!$comment) {
            return response()->json(['success' => false, 'message' => 'التعليق غير موجود'], 404);
        }

        $contentTypeId = $comment->parent_id > 0 ? 4 : 3;

        $reactions = Reaction::with(['user'])
            ->where('content_id', $commentId)
            ->where('content_type_id', $contentTypeId)
            ->where('is_active', 1)
            ->latest()
            ->get();

        $users = $reactions->map(function ($r) {
            $u = $r->user;
            $userPic = asset('images/default_profile.png');
            if ($u && $u->profile_picture && $u->profile_picture !== 'non') {
                $userPic = filter_var($u->profile_picture, FILTER_VALIDATE_URL)
                    ? $u->profile_picture
                    : asset('new_wiselook/uploads/' . $u->profile_picture);
            }
            return [
                'user_id'        => $u ? (int)$u->id : 0,
                'user_name'      => $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : 'مستخدم',
                'user_avatar'    => $userPic,
                'created_at'     => $r->created_at ? $r->created_at->diffForHumans() : '',
            ];
        });

        return response()->json([
            'success' => true,
            'users'   => $users,
        ]);
    }

    /**
     * 12. جلب قائمة المعجبين بموضوع في المجموعة
     */
    public function getSubjectReactions(Request $request)
    {
        $subjectId = $request->input('subject_id');
        $subject = GroupSubject::find($subjectId);
        if (!$subject) {
            return response()->json(['success' => false, 'message' => 'الموضوع غير موجود'], 404);
        }

        $reactions = GroupSiteSubjectReaction::with(['user'])
            ->where('group_subject_id', $subjectId)
            ->where('type', 'like')
            ->latest()
            ->get();

        $users = $reactions->map(function ($r) {
            $u = $r->user;
            $userPic = asset('images/default_profile.png');
            if ($u && $u->profile_picture && $u->profile_picture !== 'non') {
                $userPic = filter_var($u->profile_picture, FILTER_VALIDATE_URL)
                    ? $u->profile_picture
                    : asset('new_wiselook/uploads/' . $u->profile_picture);
            }
            return [
                'user_id'        => $u ? (int)$u->id : 0,
                'user_name'      => $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : 'مستخدم',
                'user_avatar'    => $userPic,
                'created_at'     => $r->created_at ? $r->created_at->diffForHumans() : '',
            ];
        });

        return response()->json([
            'success' => true,
            'users'   => $users,
        ]);
    }

    /**
     * 13. حذف مجموعة بواسطة المؤسس (Admin)
     */
    public function deleteGroup(Request $request)
    {
        $currentUser = $request->user() ?? auth('sanctum')->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $groupId = $request->input('group_id');
        $group = GroupSite::with(['subjects.comments'])->find($groupId);
        if (!$group) {
            return response()->json(['success' => false, 'message' => 'المجموعة غير موجودة'], 404);
        }

        if ($group->admin_user_id != $currentUser->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بحذف هذه المجموعة، يمكنك فقط مغادرتها إذا كنت عضواً.'], 403);
        }

        DB::beginTransaction();
        try {
            // Delete subject attachments & comments attachments
            foreach ($group->subjects as $subject) {
                if ($subject->attachment_path && !filter_var($subject->attachment_path, FILTER_VALIDATE_URL)) {
                    $subjectFile = public_path($subject->attachment_path);
                    if (File::exists($subjectFile)) {
                        File::delete($subjectFile);
                    }
                }
                foreach ($subject->comments as $comment) {
                    if ($comment->attachment_path && !filter_var($comment->attachment_path, FILTER_VALIDATE_URL)) {
                        $commentFile = public_path($comment->attachment_path);
                        if (File::exists($commentFile)) {
                            File::delete($commentFile);
                        }
                    }
                }
            }

            // Delete main group image
            if ($group->image_path && !filter_var($group->image_path, FILTER_VALIDATE_URL)) {
                $groupImg = public_path('upload/group_site_images/' . $group->image_path);
                if (File::exists($groupImg)) {
                    File::delete($groupImg);
                }
            }

            // Delete group members and group
            GroupSiteUser::where('group_site_id', $group->id)->delete();
            $group->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المجموعة بنجاح',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 14. جلب اعضاء المجموعة
     */
    public function getGroupMembers(Request $request)
    {
        $groupId = $request->input('group_id');
        $group = GroupSite::find($groupId);
        if (!$group) {
            return response()->json(['success' => false, 'message' => 'المجموعة غير موجودة'], 404);
        }

        $members = GroupSiteUser::with(['user'])
            ->where('group_site_id', $groupId)
            ->latest()
            ->get();

        $mappedMembers = $members->map(function ($member) use ($group) {
            $user = $member->user;
            if (!$user) return null;

            $isAdmin = ($user->id == $group->admin_user_id);
            $userPic = asset('images/default_profile.png');
            if ($user->profile_picture && $user->profile_picture !== 'non') {
                $userPic = filter_var($user->profile_picture, FILTER_VALIDATE_URL)
                    ? $user->profile_picture
                    : asset('new_wiselook/uploads/' . $user->profile_picture);
            }

            return [
                'member_id'       => (int)$member->id,
                'user_id'         => (int)$user->id,
                'user_name'       => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                'user_avatar'     => $userPic,
                'is_admin'        => $isAdmin,
                'role'            => $isAdmin ? 'مؤسس المجموعة' : 'عضو',
                'joined_at'       => $member->created_at ? $member->created_at->diffForHumans() : '',
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'members' => $mappedMembers,
        ]);
    }

    /**
     * 15. إلغاء انضمام / طرد عضو بواسطة مدير المجموعة
     */
    public function removeGroupMember(Request $request)
    {
        $currentUser = $request->user() ?? auth('sanctum')->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $groupId = $request->input('group_id');
        $targetUserId = $request->input('target_user_id');

        $group = GroupSite::find($groupId);
        if (!$group) {
            return response()->json(['success' => false, 'message' => 'المجموعة غير موجودة'], 404);
        }

        if ($group->admin_user_id != $currentUser->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بإلغاء انضمام الأعضاء، هذه الصلاحية للمدير فقط.'], 403);
        }

        if ($targetUserId == $group->admin_user_id) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك إلغاء انضمام مؤسس المجموعة الرئيسي.'], 422);
        }

        $deleted = GroupSiteUser::where('group_site_id', $groupId)
            ->where('user_id', $targetUserId)
            ->delete();

        if ($deleted) {
            $newCount = GroupSiteUser::where('group_site_id', $groupId)->count();
            $group->update(['members_count' => $newCount]);

            return response()->json([
                'success'       => true,
                'message'       => 'تم إلغاء انضمام العضو بنجاح',
                'members_count' => $newCount,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'العضو غير موجود في المجموعة'], 404);
    }
}


