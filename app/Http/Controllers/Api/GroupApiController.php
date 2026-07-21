<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Group;
use App\Models\GroupMember;

class GroupApiController extends Controller
{
    /**
     * 3.1 إنشاء مجموعة جديدة (Multipart Request)
     */
    public function addGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'descriptions' => 'nullable|string',
            'members'      => 'nullable|json', // مصفوفة معرفات الأعضاء ["456", "789"]
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();

        DB::beginTransaction();
        try {
            // معالجة رفع صورة المجموعة إن وجدت
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('groups', 'public');
                $imagePath = asset('storage/' . $imagePath);
            }

            // 1. إنشاء المجموعة الأساسية
            $group = Group::create([
                'name'               => $request->name,
                'image'              => $imagePath,
                'descriptions'       => $request->descriptions,
                'created_by_user_id' => $currentUser->id,
                'member_count'       => 1 // يبدأ بـ 1 (المنشئ نفسه)
            ]);

            // 2. إضافة منشئ المجموعة كـ مدير (Role ID = 2 مشرف/مدير بناءً على مستندك)
            GroupMember::create([
                'group_id'         => $group->id,
                'user_id'          => $currentUser->id,
                'role_id'          => 2, 
                'is_active'        => 1
            ]);

            // 3. إضافة الأعضاء المرفقين بالطلب إن وجدوا
            if ($request->has('members') && !empty($request->members)) {
                $memberIds = json_decode($request->members, true);
                if (is_array($memberIds)) {
                    foreach ($memberIds as $memberId) {
                        // حماية لمنع تكرار إضافة المنشئ في المصفوفة
                        if ($memberId == $currentUser->id) continue;

                        GroupMember::create([
                            'group_id'         => $group->id,
                            'user_id'          => $memberId,
                            'role_id'          => 1, // عضو عادي
                            'added_by_user_id' => $currentUser->id,
                            'is_active'        => 1
                        ]);
                        $group->increment('member_count');
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Group created successfully',
                'group_id' => (int)$group->id
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 3.2 تعديل بيانات المجموعة (Multipart Request)
     */
    public function editGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id'     => 'required|integer|exists:groups,id',
            'name'         => 'required|string|max:255',
            'descriptions' => 'nullable|string',
            'members'      => 'nullable|json' // الأعضاء الجدد لإضافتهم إن وجدوا
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $group = Group::find($request->group_id);
        $currentUser = $request->user();

        // التحقق من أن القائم بالتعديل مشرف أو منشئ المجموعة
        $isAuthorized = GroupMember::where('group_id', $group->id)
            ->where('user_id', $currentUser->id)
            ->where('role_id', 2)
            ->exists();

        if (!$isAuthorized && $group->created_by_user_id != $currentUser->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action'], 403);
        }

        DB::beginTransaction();
        try {
            $group->name = $request->name;
            $group->descriptions = $request->descriptions;

            // تحديث الصورة إن رفعت جديدة
            if ($request->hasFile('image')) {
                if ($group->image) {
                    Storage::disk('public')->delete(str_replace(asset('storage/'), '', $group->image));
                }
                $path = $request->file('image')->store('groups', 'public');
                $group->image = asset('storage/' . $path);
            }

            $group->save();

            // إضافة الأعضاء الجدد المرفقين إن وجدوا دون تكرار القائمين مسبقاً
            if ($request->has('members') && !empty($request->members)) {
                $memberIds = json_decode($request->members, true);
                if (is_array($memberIds)) {
                    foreach ($memberIds as $memberId) {
                        $exists = GroupMember::where('group_id', $group->id)->where('user_id', $memberId)->exists();
                        if (!$exists) {
                            GroupMember::create([
                                'group_id'         => $group->id,
                                'user_id'          => $memberId,
                                'role_id'          => 1,
                                'added_by_user_id' => $currentUser->id,
                                'is_active'        => 1
                            ]);
                            $group->increment('member_count');
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Group updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 3.3 إزالة عضو من المجموعة
     */
    public function removeMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id'  => 'required|integer|exists:groups,id',
            'member_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();

        // فحص صلاحية القائم بالإزالة (يجب أن يكون مشرفاً)
        $isSubAdmin = GroupMember::where('group_id', $request->group_id)
            ->where('user_id', $currentUser->id)
            ->where('role_id', 2)
            ->exists();

        if (!$isSubAdmin) {
            return response()->json(['success' => false, 'message' => 'Only moderators can remove members'], 403);
        }

        // تنفيذ الإزالة (تحديث حقل النشاط وتاريخ المغادرة تماشياً مع قاعدة بياناتك المفردة group_member)
        $member = GroupMember::where('group_id', $request->group_id)
            ->where('user_id', $request->member_id)
            ->first();

        if ($member) {
            $member->update([
                'is_active' => 0,
                'left_at'   => now()
            ]);

            // نقص عداد المجموعة
            Group::where('id', $request->group_id)->where('member_count', '>', 0)->decrement('member_count');
        }

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully'
        ]);
    }

    /**
     * 3.4 تغيير رتبة العضو (ترقية أو خفض رتبة)
     */
    public function changeRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id'  => 'required|integer|exists:groups,id',
            'member_id' => 'required|integer',
            'new_role'  => 'required|integer|in:1,2' // 1 لعضو عادي، 2 لمشرف
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();

        // التأكد من أن القائم بالترقية هو منشئ المجموعة أو مشرف مالي رئيسي
        $group = Group::find($request->group_id);
        if ($group->created_by_user_id != $currentUser->id) {
            return response()->json(['success' => false, 'message' => 'Only the group owner can change roles'], 403);
        }

        // تحديث الرتبة داخل جدول group_member بالمفرد المعتمد بملف الـ SQL
        GroupMember::where('group_id', $request->group_id)
            ->where('user_id', $request->member_id)
            ->update(['role_id' => $request->new_role]);

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully'
        ]);
    }
}