<?php

namespace App\Http\Controllers;

use App\Models\GroupSite;
use App\Models\GroupSubject;
use App\Models\GroupSiteUser;
use App\Models\GroupSiteComment;
use App\Models\GroupSiteSubjectReaction;
use App\Models\User;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class GroupSiteController extends Controller
{
    /**
     * عرض جميع المجموعات الخاصة والعامة
     */
    public function allGroupSites()
    {
        // جلب جميع المجموعات مع المشرف وعدد الأعضاء والمواضيع
        $groups = GroupSite::with(['admin'])
            ->withCount(['members', 'subjects'])
            ->latest()
            ->get();

        return view('admin.group_sites.all_group_sites', compact('groups'));
    }

    /**
     * شاشة إضافة مجموعة جديدة
     */
    public function addGroupSite()
    {
        // جلب المستخدمين النشطين لاختيار مشرف من بينهم
        $users = User::where('is_active', 1)->orderBy('first_name', 'asc')->get();
        return view('admin.group_sites.add_group_site', compact('users'));
    }

    /**
     * حفظ المجموعة الجديدة
     */
    public function storeGroupSite(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:open,closed',
            'invite_code' => 'nullable|string|max:255',
            'admin_user_id' => 'required|exists:users,id',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ], [
            'title.required' => 'يرجى إدخال عنوان المجموعة.',
            'status.required' => 'يرجى تحديد حالة المجموعة.',
            'admin_user_id.required' => 'يرجى تحديد مشرف للمجموعة.',
        ]);

        DB::beginTransaction();
        try {
            $group = new GroupSite();
            $group->title = $request->title;
            $group->description = $request->description;
            $group->status = $request->status;
            $group->invite_code = $request->status === 'closed' ? $request->invite_code : null;
            $group->admin_user_id = $request->admin_user_id;

            // رفع ومعالجة صورة المجموعة
            if ($request->hasFile('image_path')) {
                $image = $request->file('image_path');
                $imageName = date('YmdHis') . '_grpsite.' . $image->getClientOriginalExtension();
                $image->move(public_path('upload/group_site_images'), $imageName);
                $group->image_path = $imageName;
            }

            $group->save();

            // إضافة المشرف تلقائياً كعضو في المجموعة في جدول الوسيط
            GroupSiteUser::firstOrCreate([
                'group_site_id' => $group->id,
                'user_id' => $request->admin_user_id
            ]);

            DB::commit();

            $notification = [
                'message' => 'تم إضافة المجموعة الخاصة/العامة بنجاح.',
                'alert-type' => 'success'
            ];

            return redirect()->route('all.group_sites')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with([
                'message' => 'حدث خطأ أثناء حفظ المجموعة: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * شاشة تعديل بيانات المجموعة
     */
    public function editGroupSite($id)
    {
        $group = GroupSite::findOrFail($id);
        $users = User::where('is_active', 1)->orderBy('first_name', 'asc')->get();
        return view('admin.group_sites.edit_group_site', compact('group', 'users'));
    }

    /**
     * تحديث بيانات المجموعة
     */
    public function updateGroupSite(Request $request)
    {
        $id = $request->id;
        $group = GroupSite::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:open,closed',
            'invite_code' => 'nullable|string|max:255',
            'admin_user_id' => 'required|exists:users,id',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ], [
            'title.required' => 'يرجى إدخال عنوان المجموعة.',
            'status.required' => 'يرجى تحديد حالة المجموعة.',
            'admin_user_id.required' => 'يرجى تحديد مشرف للمجموعة.',
        ]);

        DB::beginTransaction();
        try {
            $oldAdminId = $group->admin_user_id;
            $newAdminId = $request->admin_user_id;

            $group->title = $request->title;
            $group->description = $request->description;
            $group->status = $request->status;
            $group->invite_code = $request->status === 'closed' ? $request->invite_code : null;
            $group->admin_user_id = $newAdminId;

            // رفع الصورة الجديدة وحذف القديمة إن وجدت
            if ($request->hasFile('image_path')) {
                if ($group->image_path && !filter_var($group->image_path, FILTER_VALIDATE_URL)) {
                    $oldPath = public_path('upload/group_site_images/' . $group->image_path);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }

                $image = $request->file('image_path');
                $imageName = date('YmdHis') . '_grpsite.' . $image->getClientOriginalExtension();
                $image->move(public_path('upload/group_site_images'), $imageName);
                $group->image_path = $imageName;
            }

            $group->save();

            // في حال تم تغيير مشرف المجموعة، نتأكد من إضافته كعضو أيضاً
            if ($oldAdminId != $newAdminId) {
                GroupSiteUser::firstOrCreate([
                    'group_site_id' => $group->id,
                    'user_id' => $newAdminId
                ]);
            }

            DB::commit();

            $notification = [
                'message' => 'تم تحديث بيانات المجموعة بنجاح.',
                'alert-type' => 'success'
            ];

            return redirect()->route('all.group_sites')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with([
                'message' => 'حدث خطأ أثناء تعديل بيانات المجموعة: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * حذف المجموعة وحذف صورها وملفات مواضيعها وتعليقاتها
     */
    public function deleteGroupSite($id)
    {
        $group = GroupSite::with(['subjects.comments'])->findOrFail($id);

        DB::beginTransaction();
        try {
            // 1. حذف ملفات مواضيع المجموعة والتعليقات الخاصة بها محلياً
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

            // 2. حذف صورة المجموعة الرئيسية
            if ($group->image_path && !filter_var($group->image_path, FILTER_VALIDATE_URL)) {
                $groupImg = public_path('upload/group_site_images/' . $group->image_path);
                if (File::exists($groupImg)) {
                    File::delete($groupImg);
                }
            }

            // 3. حذف المجموعة (سيتم حذف السجلات المرتبطة تلقائياً بالـ Cascade)
            $group->delete();

            DB::commit();

            $notification = [
                'message' => 'تم حذف المجموعة وكافة سجلاتها وملفاتها بنجاح.',
                'alert-type' => 'success'
            ];

            return redirect()->back()->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'message' => 'حدث خطأ أثناء حذف المجموعة: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * جلب أعضاء المجموعة لعرضهم في النافذة المنبثقة عبر AJAX
     */
    public function getMembers($id)
    {
        $group = GroupSite::findOrFail($id);
        
        $members = GroupSiteUser::with(['user'])
            ->where('group_site_id', $id)
            ->latest()
            ->get();

        $mappedMembers = $members->map(function($member) use ($group) {
            $user = $member->user;
            if (!$user) return null;

            // تحديد دور العضو
            $role = ($user->id == $group->admin_user_id) ? 'مشرف المجموعة' : 'عضو';

            return [
                'member_id' => $member->id,
                'user_id' => $user->id,
                'user_name' => trim($user->first_name . ' ' . $user->last_name),
                'email' => $user->email ?? $user->phone_number,
                'profile_picture' => ($user->profile_picture && $user->profile_picture != 'non')
                    ? (filter_var($user->profile_picture, FILTER_VALIDATE_URL) ? $user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $user->profile_picture)
                    : url('upload/no_image.jpg'),
                'role' => $role,
                'is_active' => $user->is_active,
                'joined_at' => $member->created_at ? $member->created_at->format('Y-m-d H:i') : ''
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'members' => $mappedMembers
        ]);
    }

    /**
     * طرد عضو من المجموعة (حذف من جدول group_site_users)
     */
    public function kickMember(Request $request)
    {
        $memberId = $request->member_id;
        $member = GroupSiteUser::findOrFail($memberId);
        $group = GroupSite::findOrFail($member->group_site_id);

        if ($member->user_id == $group->admin_user_id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن طرد مشرف المجموعة الرئيسي!'
            ]);
        }

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم طرد العضو من المجموعة بنجاح.'
        ]);
    }

    /**
     * جلب مواضيع المجموعة لعرضها في النافذة المنبثقة عبر AJAX
     */
    public function getSubjects($id)
    {
        $subjects = GroupSubject::with(['user'])
            ->where('group_site_id', $id)
            ->latest()
            ->get();

        $mappedSubjects = $subjects->map(function($subject) {
            $attachmentUrl = null;
            if ($subject->attachment_path) {
                $attachmentUrl = filter_var($subject->attachment_path, FILTER_VALIDATE_URL) 
                    ? $subject->attachment_path 
                    : 'http://localhost:8888/new_wiselook/uploads/' . basename($subject->attachment_path);
            }

            return [
                'id' => $subject->id,
                'title' => $subject->title,
                'description' => $subject->description,
                'author_name' => $subject->user ? trim($subject->user->first_name . ' ' . $subject->user->last_name) : 'مستخدم غير معروف',
                'author_picture' => ($subject->user && $subject->user->profile_picture && $subject->user->profile_picture != 'non')
                    ? (filter_var($subject->user->profile_picture, FILTER_VALIDATE_URL) ? $subject->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $subject->user->profile_picture)
                    : url('upload/no_image.jpg'),
                'likes' => $subject->likes,
                'dislikes' => $subject->dislikes,
                'attachment_type' => $subject->attachment_type,
                'attachment_path' => $attachmentUrl,
                'created_at' => $subject->created_at ? $subject->created_at->format('Y-m-d H:i') : ''
            ];
        });

        return response()->json([
            'success' => true,
            'subjects' => $mappedSubjects
        ]);
    }

    /**
     * حذف موضوع من مواضيع المجموعة
     */
    public function deleteSubject($id)
    {
        $subject = GroupSubject::with(['comments'])->findOrFail($id);

        DB::beginTransaction();
        try {
            // 1. حذف المرفق الخاص بالموضوع
            if ($subject->attachment_path && !filter_var($subject->attachment_path, FILTER_VALIDATE_URL)) {
                $subjectFile = public_path($subject->attachment_path);
                if (File::exists($subjectFile)) {
                    File::delete($subjectFile);
                }
            }

            // 2. حذف مرفقات التعليقات المرتبطة به
            foreach ($subject->comments as $comment) {
                if ($comment->attachment_path && !filter_var($comment->attachment_path, FILTER_VALIDATE_URL)) {
                    $commentFile = public_path($comment->attachment_path);
                    if (File::exists($commentFile)) {
                        File::delete($commentFile);
                    }
                }
            }

            // 3. حذف سجل الموضوع
            $subject->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الموضوع وكافة تعليقاته بنجاح.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الموضوع: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب تعليقات موضوع معين لعرضها في النافذة المنبثقة عبر AJAX
     */
    public function getComments($subject_id)
    {
        $comments = GroupSiteComment::with(['user'])
            ->where('group_subject_id', $subject_id)
            ->latest()
            ->get();

        $mappedComments = $comments->map(function($comment) {
            $attachmentUrl = null;
            if ($comment->attachment_path) {
                $attachmentUrl = filter_var($comment->attachment_path, FILTER_VALIDATE_URL) 
                    ? $comment->attachment_path 
                    : 'http://localhost:8888/new_wiselook/uploads/' . basename($comment->attachment_path);
            }

            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'author_name' => $comment->user ? trim($comment->user->first_name . ' ' . $comment->user->last_name) : 'مستخدم غير معروف',
                'author_picture' => ($comment->user && $comment->user->profile_picture && $comment->user->profile_picture != 'non')
                    ? (filter_var($comment->user->profile_picture, FILTER_VALIDATE_URL) ? $comment->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $comment->user->profile_picture)
                    : url('upload/no_image.jpg'),
                'attachment_type' => $comment->attachment_type,
                'attachment_path' => $attachmentUrl,
                'created_at' => $comment->created_at ? $comment->created_at->format('Y-m-d H:i') : ''
            ];
        });

        return response()->json([
            'success' => true,
            'comments' => $mappedComments
        ]);
    }

    /**
     * حذف تعليق
     */
    public function deleteComment($id)
    {
        $comment = GroupSiteComment::findOrFail($id);

        try {
            // حذف مرفق التعليق محلياً إن وجد
            if ($comment->attachment_path && !filter_var($comment->attachment_path, FILTER_VALIDATE_URL)) {
                $commentFile = public_path($comment->attachment_path);
                if (File::exists($commentFile)) {
                    File::delete($commentFile);
                }
            }

            $comment->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف التعليق بنجاح.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف التعليق: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب تفاعلات موضوع معين (Likes/Dislikes) لعرضها في النافذة المنبثقة عبر AJAX
     */
    public function getReactions($subject_id)
    {
        $reactions = GroupSiteSubjectReaction::with(['user'])
            ->where('group_subject_id', $subject_id)
            ->latest()
            ->get();

        $mappedReactions = $reactions->map(function($reaction) {
            return [
                'id' => $reaction->id,
                'type' => $reaction->type, // 'like' or 'dislike'
                'type_ar' => $reaction->type === 'like' ? 'إعجاب' : 'عدم إعجاب',
                'author_name' => $reaction->user ? trim($reaction->user->first_name . ' ' . $reaction->user->last_name) : 'مستخدم غير معروف',
                'author_picture' => ($reaction->user && $reaction->user->profile_picture && $reaction->user->profile_picture != 'non')
                    ? (filter_var($reaction->user->profile_picture, FILTER_VALIDATE_URL) ? $reaction->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $reaction->user->profile_picture)
                    : url('upload/no_image.jpg'),
                'created_at' => $reaction->created_at ? $reaction->created_at->format('Y-m-d H:i') : ''
            ];
        });

        return response()->json([
            'success' => true,
            'reactions' => $mappedReactions
        ]);
    }

    /**
     * حذف تفاعل وتحديث العداد الخاص بالموضوع
     */
    public function deleteReaction($id)
    {
        $reaction = GroupSiteSubjectReaction::with(['subject'])->findOrFail($id);

        DB::beginTransaction();
        try {
            $subject = $reaction->subject;
            
            // إنقاص العداد في جدول المواضيع للحفاظ على تناسق البيانات
            if ($subject) {
                if ($reaction->type === 'like' && $subject->likes > 0) {
                    $subject->decrement('likes');
                } elseif ($reaction->type === 'dislike' && $subject->dislikes > 0) {
                    $subject->decrement('dislikes');
                }
            }

            $reaction->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف التفاعل بنجاح وتحديث العداد.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف التفاعل: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض جميع المجموعات في الواجهة الأمامية
     */
    public function indexFrontendGroups(Request $request)
    {
        $userId = auth()->id();
        $perPage = 6;

        if ($request->ajax()) {
            $type = $request->get('type');
            
            $groupsQuery = GroupSite::with(['admin'])
                ->withCount(['members', 'subjects'])
                ->where('status', '!=', 'archived')
                ->latest();

            if ($type === 'my-groups' && $userId) {
                $groups = $groupsQuery->where('admin_user_id', $userId)->paginate($perPage);
            } elseif ($type === 'joined-groups' && $userId) {
                $groups = $groupsQuery->whereHas('members', fn($q) => $q->where('user_id', $userId))
                    ->where('admin_user_id', '!=', $userId)
                    ->paginate($perPage);
            } else {
                // all-groups
                $groups = $groupsQuery->paginate($perPage);
            }

            $html = '';
            foreach ($groups as $group) {
                $html .= view('frontend.wiselook.partials.group_card', compact('group'))->render();
            }

            return response()->json([
                'html' => $html,
                'hasMore' => $groups->hasMorePages(),
                'nextPage' => $groups->currentPage() + 1
            ]);
        }

        // For non-ajax requests (initial load), load the first page for each tab
        // All groups (Mataha)
        $groups = GroupSite::with(['admin'])
            ->withCount(['members', 'subjects'])
            ->where('status', '!=', 'archived')
            ->latest()
            ->paginate($perPage, ['*'], 'page', 1);

        // My Groups (Majmou'ati)
        $myGroups = $userId
            ? GroupSite::with(['admin'])
                ->withCount(['members', 'subjects'])
                ->where('admin_user_id', $userId)
                ->where('status', '!=', 'archived')
                ->latest()
                ->paginate($perPage, ['*'], 'page', 1)
            : GroupSite::whereNull('id')->paginate($perPage, ['*'], 'page', 1);

        // Joined Groups (Monadamm ilayha)
        $joinedGroups = $userId
            ? GroupSite::with(['admin'])
                ->withCount(['members', 'subjects'])
                ->whereHas('members', fn($q) => $q->where('user_id', $userId))
                ->where('admin_user_id', '!=', $userId)
                ->where('status', '!=', 'archived')
                ->latest()
                ->paginate($perPage, ['*'], 'page', 1)
            : GroupSite::whereNull('id')->paginate($perPage, ['*'], 'page', 1);

        // Also we want to preserve the total count for the tab count badges!
        $totalMyGroupsCount = $userId
            ? GroupSite::where('admin_user_id', $userId)->where('status', '!=', 'archived')->count()
            : 0;

        $totalJoinedGroupsCount = $userId
            ? GroupSite::whereHas('members', fn($q) => $q->where('user_id', $userId))
                ->where('admin_user_id', '!=', $userId)
                ->where('status', '!=', 'archived')
                ->count()
            : 0;

        $totalGroupsCount = GroupSite::where('status', '!=', 'archived')->count();

        return view('frontend.wiselook.pages.groups', compact(
            'groups', 'myGroups', 'joinedGroups',
            'totalMyGroupsCount', 'totalJoinedGroupsCount', 'totalGroupsCount'
        ));
    }

    /**
     * عرض تفاصيل مجموعة محددة في الواجهة الأمامية
     */
    public function showFrontendGroupDetails($id)
    {
        $group = GroupSite::with(['admin', 'members' => function ($q) {
            $q->latest()->take(5);
        }])
            ->withCount(['members', 'subjects'])
            ->findOrFail($id);

        // جلب مواضيع المجموعة مع الكاتب والتعليقات والتفاعلات مرتبة تنازلياً (أول 5 مواضيع)
        $subjects = GroupSubject::with(['user', 'comments.user', 'reactions'])
            ->where('group_site_id', $id)
            ->latest()
            ->paginate(5);

        // التحقق من حالة انضمام المستخدم للمجموعة
        $isMember = false;
        if (auth()->check()) {
            $isMember = GroupSiteUser::where('group_site_id', $id)
                ->where('user_id', auth()->id())
                ->exists();
        }

        return view('frontend.wiselook.pages.group_details', compact('group', 'subjects', 'isMember'));
    }

    /**
     * إنشاء مجموعة جديدة من الواجهة الأمامية
     */
    public function storeFrontendGroupSite(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:open,closed',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ], [
            'title.required' => 'يرجى إدخال اسم المجموعة.',
            'status.required' => 'يرجى تحديد حالة المجموعة.',
        ]);

        DB::beginTransaction();
        try {
            $group = new GroupSite();
            $group->title = $request->title;
            $group->description = $request->description;
            $group->status = $request->status;
            
            // توليد كود انضمام فريد تلقائياً إذا كانت المجموعة مغلقة
            if ($request->status === 'closed') {
                do {
                    $inviteCode = 'WISE-' . strtoupper(\Illuminate\Support\Str::random(6));
                } while (GroupSite::where('invite_code', $inviteCode)->exists());
                $group->invite_code = $inviteCode;
            } else {
                $group->invite_code = null;
            }
            
            $group->admin_user_id = auth()->id();

            // رفع صورة غلاف المجموعة
            if ($request->hasFile('image_path')) {
                $image = $request->file('image_path');
                $imageName = date('YmdHis') . '_grpsite.' . $image->getClientOriginalExtension();
                $image->move(public_path('upload/group_site_images'), $imageName);
                $group->image_path = $imageName;
            }

            // رفع صورة المجموعة/الشعار
            if ($request->hasFile('logo_path')) {
                $logo = $request->file('logo_path');
                $logoName = date('YmdHis') . '_grplogo.' . $logo->getClientOriginalExtension();
                $logo->move(public_path('upload/group_site_logos'), $logoName);
                $group->logo_path = $logoName;
            }

            $group->save();

            // إضافة المستخدم كعضو ومدير للمجموعة تلقائياً
            GroupSiteUser::firstOrCreate([
                'group_site_id' => $group->id,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            $successMsg = 'تم إنشاء المجموعة بنجاح.';
            if ($group->status === 'closed') {
                $successMsg .= ' كود الانضمام المولد تلقائياً هو: ' . $group->invite_code;
            }

            return redirect()->route('frontend.groups.details', $group->id)->with([
                'message' => $successMsg,
                'alert-type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with([
                'message' => 'حدث خطأ أثناء حفظ المجموعة: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * الانضمام لمجموعة عبر الواجهة الأمامية
     */
    public function joinGroupSite(Request $request, $id)
    {
        $group = GroupSite::findOrFail($id);
        $userId = auth()->id();

        // تحقق من الانضمام المسبق
        $alreadyMember = GroupSiteUser::where('group_site_id', $id)
            ->where('user_id', $userId)
            ->exists();

        if ($alreadyMember) {
            return response()->json([
                'success' => false,
                'message' => 'أنت بالفعل عضو في هذه المجموعة.'
            ]);
        }

        // التحقق من كود الانضمام للمجموعات الخاصة
        if ($group->status === 'closed') {
            if (!$request->invite_code || $request->invite_code !== $group->invite_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'كود الانضمام غير صحيح.'
                ]);
            }
        }

        GroupSiteUser::create([
            'group_site_id' => $id,
            'user_id' => $userId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم الانضمام للمجموعة بنجاح.'
        ]);
    }

    /**
     * مغادرة مجموعة عبر الواجهة الأمامية
     */
    /**
     * حذف عضو من المجموعة - من الواجهة الأمامية (مدير المجموعة فقط)
     */
    public function kickFrontendMember($groupId, $userId)
    {
        $group = GroupSite::findOrFail($groupId);

        // التحقق أن المستخدم الحالي هو مدير المجموعة
        if (auth()->id() !== $group->admin_user_id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بهذا الإجراء.'
            ], 403);
        }

        // لا يمكن حذف المدير نفسه
        if ((int)$userId === $group->admin_user_id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف مدير المجموعة.'
            ]);
        }

        GroupSiteUser::where('group_site_id', $groupId)
            ->where('user_id', $userId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف العضو من المجموعة بنجاح.'
        ]);
    }

    /**
     * مغادرة المجموعة
     */
    public function leaveGroupSite($id)
    {
        $group = GroupSite::findOrFail($id);
        $userId = auth()->id();

        if ($group->admin_user_id === $userId) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك مغادرة المجموعة لأنك المدير الحالي لها.'
            ]);
        }

        GroupSiteUser::where('group_site_id', $id)
            ->where('user_id', $userId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم مغادرة المجموعة بنجاح.'
        ]);
    }

    /**
     * نشر موضوع جديد داخل المجموعة
     */
    public function storeGroupSubject(Request $request, $groupId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'media' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi,mp3,wav|max:25600'
        ]);

        // التحقق من العضوية
        $isMember = GroupSiteUser::where('group_site_id', $groupId)
            ->where('user_id', auth()->id())
            ->exists();

        if (!$isMember) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بالنشر لأنك لست عضواً في هذه المجموعة.'
                ], 403);
            }
            return redirect()->back()->with([
                'message' => 'غير مصرح لك بالنشر لأنك لست عضواً في هذه المجموعة.',
                'alert-type' => 'error'
            ]);
        }

        try {
            $subject = new GroupSubject();
            $subject->user_id = auth()->id();
            $subject->group_site_id = $groupId;
            $subject->title = $request->title;
            $subject->description = $request->description;

            // رفع ومعالجة الملف المرفق
            if ($request->hasFile('media')) {
                $file = $request->file('media');
                $filename = date('YmdHis') . '_grpsubj.' . $file->getClientOriginalExtension();
                $file->move(public_path('upload/group_subjects'), $filename);
                $subject->attachment_path = $filename;

                $ext = strtolower($file->getClientOriginalExtension());
                if (in_array($ext, ['jpeg', 'png', 'jpg', 'gif'])) {
                    $subject->attachment_type = 'image';
                } elseif (in_array($ext, ['mp4', 'mov', 'avi'])) {
                    $subject->attachment_type = 'video';
                } elseif (in_array($ext, ['mp3', 'wav'])) {
                    $subject->attachment_type = 'audio';
                }
            }

            $subject->save();
            $subject->syncHashtags();

            if ($request->ajax()) {
                $subject->load(['user', 'comments', 'reactions']);
                $group = GroupSite::find($groupId);
                $html = view('frontend.wiselook.partials.subject_card', compact('subject', 'group', 'isMember'))->render();
                return response()->json([
                    'success' => true,
                    'message' => 'تم نشر الموضوع بنجاح.',
                    'html' => $html
                ]);
            }

            return redirect()->back()->with([
                'message' => 'تم نشر الموضوع بنجاح.',
                'alert-type' => 'success'
            ]);
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء نشر الموضوع: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with([
                'message' => 'حدث خطأ أثناء نشر الموضوع: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * تسجيل التفاعل (الإعجاب) بموضوع نقاشي
     */
    public function toggleSubjectReaction(Request $request, $subjectId)
    {
        $request->validate([
            'reaction_type' => 'required|in:like,remove'
        ]);

        $subject = GroupSubject::findOrFail($subjectId);
        $userId = auth()->id();

        DB::beginTransaction();
        try {
            if ($request->reaction_type === 'like') {
                GroupSiteSubjectReaction::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'group_subject_id' => $subjectId
                    ],
                    [
                        'type' => 'like'
                    ]
                );
            } else {
                GroupSiteSubjectReaction::where('user_id', $userId)
                    ->where('group_subject_id', $subjectId)
                    ->delete();
            }

            // تحديث إجمالي التفاعلات في جدول المواضيع للحفاظ على التزامن
            $likesCount = GroupSiteSubjectReaction::where('group_subject_id', $subjectId)
                ->where('type', 'like')
                ->count();
            $dislikesCount = GroupSiteSubjectReaction::where('group_subject_id', $subjectId)
                ->where('type', 'dislike')
                ->count();

            $subject->update([
                'likes' => $likesCount,
                'dislikes' => $dislikesCount
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'like_count' => $likesCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب قائمة مستخدمين مؤيدين لموضوع محدد
     */
    public function getSubjectSupporters($subjectId)
    {
        $reactions = GroupSiteSubjectReaction::with(['user'])
            ->where('group_subject_id', $subjectId)
            ->where('type', 'like')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'reactions' => $reactions->map(function ($react) {
                return [
                    'user_name' => $react->user ? trim($react->user->first_name . ' ' . $react->user->last_name) : 'مستخدم غير معروف',
                    'profile_picture' => ($react->user && $react->user->profile_picture && $react->user->profile_picture != 'non') 
                        ? (filter_var($react->user->profile_picture, FILTER_VALIDATE_URL) ? $react->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $react->user->profile_picture) 
                        : url('upload/no_image.jpg'),
                    'rank' => __t('intellectual_advisor'),
                    'created_at' => $react->created_at ? $react->created_at->diffForHumans() : ''
                ];
            })
        ]);
    }

    /**
     * جلب قائمة التعليقات والردود لموضوع محدد في المجموعات
     */
    public function getSubjectComments($subjectId)
    {
        // جلب التعليقات الأب (parent_id = 0)
        $comments = GroupSiteComment::with(['user', 'replies.user'])
            ->where('group_subject_id', $subjectId)
            ->where('parent_id', 0)
            ->latest()
            ->get();

        $userId = auth()->id();

        $formatted = $comments->map(function ($comment) use ($userId) {
            // التحقق من إعجاب المستخدم بالتعليق
            $userLikedComment = false;
            if ($userId) {
                $userLikedComment = Reaction::where('user_id', $userId)
                    ->where('content_id', $comment->id)
                    ->where('content_type_id', 3) // 3 لتعليقات المجموعات
                    ->where('is_active', 1)
                    ->exists();
            }

            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'user_name' => $comment->user ? trim($comment->user->first_name . ' ' . $comment->user->last_name) : 'مستخدم غير معروف',
                'profile_picture' => ($comment->user && $comment->user->profile_picture && $comment->user->profile_picture != 'non') 
                    ? (filter_var($comment->user->profile_picture, FILTER_VALIDATE_URL) ? $comment->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $comment->user->profile_picture) 
                    : url('upload/no_image.jpg'),
                'created_at' => $comment->created_at ? $comment->created_at->diffForHumans() : '',
                'reaction_count' => (int)$comment->reaction_count,
                'user_liked' => $userLikedComment,
                'replies' => $comment->replies ? $comment->replies->map(function ($reply) use ($userId) {
                    $userLikedReply = false;
                    if ($userId) {
                        $userLikedReply = Reaction::where('user_id', $userId)
                            ->where('content_id', $reply->id)
                            ->where('content_type_id', 4) // 4 للردود على تعليقات المجموعات
                            ->where('is_active', 1)
                            ->exists();
                    }
                    return [
                        'id' => $reply->id,
                        'content' => $reply->content,
                        'user_name' => $reply->user ? trim($reply->user->first_name . ' ' . $reply->user->last_name) : 'مستخدم غير معروف',
                        'profile_picture' => ($reply->user && $reply->user->profile_picture && $reply->user->profile_picture != 'non') 
                            ? (filter_var($reply->user->profile_picture, FILTER_VALIDATE_URL) ? $reply->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $reply->user->profile_picture) 
                            : url('upload/no_image.jpg'),
                        'created_at' => $reply->created_at ? $reply->created_at->diffForHumans() : '',
                        'reaction_count' => (int)$reply->reaction_count,
                        'user_liked' => $userLikedReply
                    ];
                }) : []
            ];
        });

        return response()->json([
            'success' => true,
            'comments' => $formatted
        ]);
    }

    /**
     * حفظ تعليق جديد أو رد على موضوع نقاشي بالمجموعات
     */
    public function storeSubjectComment(Request $request, $subjectId)
    {
        $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|integer'
        ]);

        $parentId = (int)$request->input('parent_id', 0);

        $comment = GroupSiteComment::create([
            'group_subject_id' => (int)$subjectId,
            'user_id' => auth()->id(),
            'parent_id' => $parentId,
            'content' => $request->content,
            'reaction_count' => 0,
            'reply_count' => 0
        ]);

        if ($parentId > 0) {
            GroupSiteComment::where('id', $parentId)->increment('reply_count');
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة التعليق بنجاح',
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'user_name' => auth()->user() ? trim(auth()->user()->first_name . ' ' . auth()->user()->last_name) : 'مستخدم',
                'profile_picture' => (auth()->user() && auth()->user()->profile_picture && auth()->user()->profile_picture != 'non') 
                    ? (filter_var(auth()->user()->profile_picture, FILTER_VALIDATE_URL) ? auth()->user()->profile_picture : asset('new_wiselook/uploads/' . auth()->user()->profile_picture)) 
                    : asset('upload/no_image.jpg'),
                'created_at' => $comment->created_at ? $comment->created_at->diffForHumans() : 'الآن',
                'reaction_count' => 0,
                'user_liked' => false,
                'replies' => []
            ]
        ]);
    }

    /**
     * التفاعل بالإعجاب مع تعليق أو رد داخل مواضيع المجموعات
     */
    public function reactSubjectComment(Request $request, $id)
    {
        $request->validate([
            'reaction_type' => 'required|string|in:like,remove'
        ]);

        $comment = GroupSiteComment::findOrFail($id);
        $userId = auth()->id();
        
        $contentTypeId = $comment->parent_id > 0 ? 4 : 3; // 3 للتعليق و 4 للرد

        if ($request->reaction_type === 'like') {
            Reaction::updateOrCreate(
                [
                    'user_id' => $userId,
                    'content_id' => $id,
                    'content_type_id' => $contentTypeId,
                    'reaction_type_id' => 1
                ],
                [
                    'is_active' => 1
                ]
            );

            $comment->increment('reaction_count');
        } else {
            Reaction::where('user_id', $userId)
                ->where('content_id', $id)
                ->where('content_type_id', $contentTypeId)
                ->update(['is_active' => 0]);

            if ($comment->reaction_count > 0) {
                $comment->decrement('reaction_count');
            }
        }

        $comment->refresh();

        return response()->json([
            'success' => true,
            'reaction_count' => (int)$comment->reaction_count
        ]);
    }

    /**
     * جلب المعجبين بتعليق أو رد داخل المجموعات
     */
    public function getSubjectCommentReactions($id)
    {
        $comment = GroupSiteComment::findOrFail($id);
        $contentTypeId = $comment->parent_id > 0 ? 4 : 3;

        $reactions = Reaction::with(['user'])
            ->where('content_id', $id)
            ->where('content_type_id', $contentTypeId)
            ->where('is_active', 1)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'reactions' => $reactions->map(function ($reaction) {
                return [
                    'user_name' => $reaction->user ? trim($reaction->user->first_name . ' ' . $reaction->user->last_name) : 'مستخدم غير معروف',
                    'profile_picture' => ($reaction->user && $reaction->user->profile_picture && $reaction->user->profile_picture != 'non') 
                        ? (filter_var($reaction->user->profile_picture, FILTER_VALIDATE_URL) ? $reaction->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $reaction->user->profile_picture) 
                        : url('upload/no_image.jpg'),
                    'rank' => 'مستشار فكري',
                    'created_at' => $reaction->created_at ? $reaction->created_at->diffForHumans() : ''
                ];
            })
        ]);
    }

    /**
     * حذف موضوع من المجموعات للكاتب أو المشرف
     */
    public function deleteFrontendSubject($id)
    {
        $subject = GroupSubject::findOrFail($id);
        $group = $subject->groupSite;

        // التحقق من الصلاحية
        if ($subject->user_id !== auth()->id() && $group->admin_user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذا الموضوع.'
            ], 403);
        }

        try {
            // حذف المرفق المحلي
            if ($subject->attachment_path && !filter_var($subject->attachment_path, FILTER_VALIDATE_URL)) {
                $oldPath = public_path('upload/group_subjects/' . $subject->attachment_path);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            // حذف تفاعلات الموضوع
            GroupSiteSubjectReaction::where('group_subject_id', $id)->delete();
            
            // حذف تفاعلات التعليقات
            $commentIds = GroupSiteComment::where('group_subject_id', $id)->pluck('id');
            Reaction::whereIn('content_id', $commentIds)->whereIn('content_type_id', [3, 4])->delete();
            
            // حذف التعليقات
            GroupSiteComment::where('group_subject_id', $id)->delete();

            $subject->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الموضوع بنجاح.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الموضوع: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف تعليق من موضوع المجموعات للكاتب أو المشرف
     */
    public function deleteFrontendComment($id)
    {
        $comment = GroupSiteComment::findOrFail($id);
        $subject = $comment->subject;
        $group = $subject->groupSite;

        if ($comment->user_id !== auth()->id() && $group->admin_user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذا التعليق.'
            ], 403);
        }

        try {
            if ($comment->parent_id > 0) {
                GroupSiteComment::where('id', $comment->parent_id)->decrement('reply_count');
            }

            $contentTypeId = $comment->parent_id > 0 ? 4 : 3;
            Reaction::where('content_id', $id)->where('content_type_id', $contentTypeId)->delete();

            if ($comment->parent_id == 0) {
                $replyIds = GroupSiteComment::where('parent_id', $id)->pluck('id');
                Reaction::whereIn('content_id', $replyIds)->where('content_type_id', 4)->delete();
                GroupSiteComment::where('parent_id', $id)->delete();
            }

            $comment->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف التعليق بنجاح.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف التعليق: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Frontend action to delete a group site by its admin/creator.
     */
    public function deleteGroupSiteFrontend($id)
    {
        $group = GroupSite::with(['subjects.comments'])->findOrFail($id);

        if ($group->admin_user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإجراء هذه العملية.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // 1. Delete subject attachments and comments attachments
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

            // 2. Delete main group image
            if ($group->image_path && !filter_var($group->image_path, FILTER_VALIDATE_URL)) {
                $groupImg = public_path('upload/group_site_images/' . $group->image_path);
                if (File::exists($groupImg)) {
                    File::delete($groupImg);
                }
            }

            // 3. Delete the group site
            $group->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المجموعة بنجاح.',
                'redirect_url' => route('frontend.groups')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف المجموعة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * get group subjects via ajax for infinite scroll/lazyloading
     */
    public function getGroupSubjectsApi(Request $request, $id)
    {
        $group = GroupSite::findOrFail($id);
        
        $isMember = false;
        if (auth()->check()) {
            $isMember = GroupSiteUser::where('group_site_id', $id)
                ->where('user_id', auth()->id())
                ->exists() || $group->admin_user_id === auth()->id();
        }

        if ($group->status === 'closed' && !$isMember) {
            return response()->json([
                'html' => '',
                'has_more' => false,
                'message' => 'المجموعة مغلقة للأعضاء فقط.'
            ], 403);
        }

        $perPage = $request->input('per_page', 5);
        $subjects = GroupSubject::with(['user', 'comments.user', 'reactions'])
            ->where('group_site_id', $id)
            ->latest()
            ->paginate($perPage);

        $html = '';
        foreach ($subjects as $subject) {
            $html .= view('frontend.wiselook.partials.subject_card', [
                'subject' => $subject,
                'group' => $group,
                'isMember' => $isMember
            ])->render();
        }

        return response()->json([
            'html' => $html,
            'has_more' => $subjects->hasMorePages()
        ]);
    }

    /**
     * تحديث بيانات المجموعة عبر AJAX (للأدمن/المنشئ فقط)
     */
    public function updateGroupSiteApi(Request $request, $id)
    {
        $group = GroupSite::findOrFail($id);

        // التحقق من الصلاحية: فقط منشئ المجموعة يمكنه التعديل
        if ($group->admin_user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل بيانات هذه المجموعة.'
            ], 403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240'
        ]);

        $updatedData = [];

        // 1. تحديث العنوان
        if ($request->has('title')) {
            $group->title = $request->title;
            $updatedData['title'] = $request->title;
        }

        // 2. تحديث الوصف
        if ($request->has('description')) {
            $group->description = $request->description;
            $updatedData['description'] = $request->description;
        }

        // 3. تحديث الشعار
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            
            // حذف الشعار القديم إن وجد محلياً
            if ($group->logo_path && !filter_var($group->logo_path, FILTER_VALIDATE_URL)) {
                $oldPath = public_path('upload/group_site_logos/' . basename($group->logo_path));
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $filename = date('YmdHis') . '_grplogo.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/group_site_logos'), $filename);
            $group->logo_path = 'upload/group_site_logos/' . $filename;
            $updatedData['logo_url'] = asset($group->logo_path);
        }

        // 4. تحديث صورة الغلاف
        if ($request->hasFile('cover')) {
            $file = $request->file('cover');

            // حذف الغلاف القديم إن وجد محلياً
            if ($group->image_path && !filter_var($group->image_path, FILTER_VALIDATE_URL)) {
                $oldPath = public_path('upload/group_site_images/' . basename($group->image_path));
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $filename = date('YmdHis') . '_grpcover.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/group_site_images'), $filename);
            $group->image_path = 'upload/group_site_images/' . $filename;
            $updatedData['cover_url'] = asset($group->image_path);
        }

        $group->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات المجموعة بنجاح.',
            'data' => $updatedData
        ]);
    }
}
