<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Models\GroupsRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    /**
     * عرض كل المجموعات
     */
    public function allGroups()
    {
        $groups = Group::with(['creator'])->latest()->get();
        return view('admin.groups.all_groups', compact('groups'));
    }

    /**
     * شاشة إضافة مجموعة جديدة
     */
    public function addGroup()
    {
        $users = User::where('is_active', 1)->orderBy('first_name', 'asc')->get();
        return view('admin.groups.add_group', compact('users'));
    }

    /**
     * حفظ مجموعة جديدة في قاعدة البيانات
     */
    public function storeGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'descriptions' => 'nullable|string',
            'created_by_user_id' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ], [
            'name.required' => 'يرجى إدخال اسم المجموعة.',
            'created_by_user_id.required' => 'يرجى تحديد منشئ المجموعة.',
        ]);

        DB::beginTransaction();
        try {
            $group = new Group();
            $group->name = $request->name;
            $group->descriptions = $request->descriptions;
            $group->created_by_user_id = $request->created_by_user_id;
            $group->member_count = 1; // يبدأ بـ 1 (المنشئ نفسه)

            // معالجة رفع صورة المجموعة
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = date('YmdHis') . '_grp.' . $image->getClientOriginalExtension();
                $image->move(public_path('upload/group_images'), $imageName);
                $group->image = $imageName;
            }

            $group->save();

            // إضافة منشئ المجموعة كـ مدير (Role ID = 2 مشرف/مدير تماشياً مع الـ API)
            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => $request->created_by_user_id,
                'role_id' => 2,
                'is_active' => 1,
                'joined_at' => now(),
            ]);

            DB::commit();

            $notification = [
                'message' => 'تم إضافة المجموعة بنجاح.',
                'alert-type' => 'success'
            ];

            return redirect()->route('all.groups')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with([
                'message' => 'حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * شاشة تعديل مجموعة
     */
    public function editGroup($id)
    {
        $group = Group::findOrFail($id);
        $users = User::where('is_active', 1)->orderBy('first_name', 'asc')->get();
        return view('admin.groups.edit_group', compact('group', 'users'));
    }

    /**
     * تحديث بيانات مجموعة
     */
    public function updateGroup(Request $request)
    {
        $id = $request->id;
        $group = Group::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'descriptions' => 'nullable|string',
            'created_by_user_id' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ], [
            'name.required' => 'يرجى إدخال اسم المجموعة.',
            'created_by_user_id.required' => 'يرجى تحديد منشئ المجموعة.',
        ]);

        DB::beginTransaction();
        try {
            $oldCreatorId = $group->created_by_user_id;
            $newCreatorId = $request->created_by_user_id;

            $group->name = $request->name;
            $group->descriptions = $request->descriptions;
            $group->created_by_user_id = $newCreatorId;

            // تحديث الصورة إن رفعت جديدة
            if ($request->hasFile('image')) {
                // حذف الصورة القديمة إذا كانت محفوظة محلياً
                if ($group->image && !filter_var($group->image, FILTER_VALIDATE_URL)) {
                    $oldPath = public_path('upload/group_images/' . $group->image);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }
                
                $image = $request->file('image');
                $imageName = date('YmdHis') . '_grp.' . $image->getClientOriginalExtension();
                $image->move(public_path('upload/group_images'), $imageName);
                $group->image = $imageName;
            }

            $group->save();

            // في حال تم تغيير منشئ المجموعة، نتأكد من إضافته وترقيته لمدير
            if ($oldCreatorId != $newCreatorId) {
                // فحص هل المنشئ الجديد عضو بالفعل
                $member = GroupMember::where('group_id', $group->id)->where('user_id', $newCreatorId)->first();
                if ($member) {
                    $member->role_id = 2; // ترقية لمدير
                    $member->is_active = 1;
                    $member->save();
                } else {
                    GroupMember::create([
                        'group_id' => $group->id,
                        'user_id' => $newCreatorId,
                        'role_id' => 2,
                        'is_active' => 1,
                        'joined_at' => now(),
                    ]);
                    $group->increment('member_count');
                }
            }

            DB::commit();

            $notification = [
                'message' => 'تم تحديث المجموعة بنجاح.',
                'alert-type' => 'success'
            ];

            return redirect()->route('all.groups')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with([
                'message' => 'حدث خطأ أثناء تعديل البيانات: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * حذف مجموعة
     */
    public function deleteGroup($id)
    {
        $group = Group::findOrFail($id);

        DB::beginTransaction();
        try {
            // حذف الصورة إذا كانت موجودة محلياً
            if ($group->image && !filter_var($group->image, FILTER_VALIDATE_URL)) {
                $oldPath = public_path('upload/group_images/' . $group->image);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            // حذف سجلات الأعضاء
            GroupMember::where('group_id', $group->id)->delete();

            // حذف المجموعة
            $group->delete();

            DB::commit();

            $notification = [
                'message' => 'تم حذف المجموعة وكافة سجلاتها بنجاح.',
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
     * جلب أعضاء المجموعة لعرضهم في المودال عبر AJAX
     */
    public function getGroupMembers($id)
    {
        $members = GroupMember::with(['user', 'role'])
            ->where('group_id', $id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'members' => $members->map(function($member) {
                // ترجمة رتبة العضو للعربية
                $roleName = 'عضو';
                if ($member->role) {
                    if ($member->role->name === 'Owner') {
                        $roleName = 'مالك المجموعة';
                    } elseif ($member->role->name === 'Admin') {
                        $roleName = 'مشرف المجموعة';
                    }
                }

                return [
                    'user_name' => $member->user ? trim($member->user->first_name . ' ' . $member->user->last_name) : 'مستخدم غير معروف',
                    'profile_picture' => ($member->user && $member->user->profile_picture && $member->user->profile_picture != 'non') 
                        ? (filter_var($member->user->profile_picture, FILTER_VALIDATE_URL) ? $member->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $member->user->profile_picture) 
                        : url('upload/no_image.jpg'),
                    'role_name' => $roleName,
                    'joined_at' => $member->joined_at ? date('Y-m-d H:i', strtotime($member->joined_at)) : '',
                    'is_active' => $member->is_active
                ];
            })
        ]);
    }
}
