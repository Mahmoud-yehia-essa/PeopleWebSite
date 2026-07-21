<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Group;

class ChatApiController extends Controller
{
    /**
     * 3.5 جلب قائمة المحادثات والمجموعات المتزامنة مع Firestore
     */
    public function listChats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_ids'  => 'nullable|array',  // مصفوفة معرفات المستخدمين المطلوب بياناتهم
            'group_ids' => 'nullable|array', // مصفوفة معرفات المجموعات المطلوب بياناتها
            'search'    => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $formattedData = [];

        // 1. معالجة وجلب بيانات المستخدمين الفرديين (Chats)
        if ($request->has('user_ids') && is_array($request->user_ids)) {
            $usersQuery = User::whereIn('id', $request->user_ids)->where('is_active', 1);
            
            if ($request->has('search')) {
                $usersQuery->where(function($q) use ($request) {
                    $q->where('first_name', 'LIKE', '%' . $request->search . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $request->search . '%');
                });
            }

            $users = $usersQuery->get();

            foreach ($users as $user) {
                $formattedData[] = [
                    'id'              => (string)$user->id,
                    'name'            => $user->first_name . ' ' . $user->last_name,
                    'profile_picture' => $user->profile_picture,
                    'unread_count'    => 0, // تدار القيمة الحية ديناميكياً عبر واجهة الموبايل
                    'type'            => 'user'
                ];
            }
        }

        // 2. معالجة وجلب بيانات المجموعات (Group Chats)
        if ($request->has('group_ids') && is_array($request->group_ids)) {
            $groupsQuery = Group::whereIn('id', $request->group_ids);

            if ($request->has('search')) {
                $groupsQuery->where('name', 'LIKE', '%' . $request->search . '%');
            }

            $groups = $groupsQuery->get();

            foreach ($groups as $group) {
                $formattedData[] = [
                    'id'              => 'group_' . $group->id, // البادئة المتوقعة لتفريق الغرف بالـ Flutter
                    'name'            => $group->name,
                    'profile_picture' => $group->image,
                    'unread_count'    => 0, // تدار عبر كود الموبايل و الكاش المحلي
                    'type'            => 'group'
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $formattedData
        ]);
    }
}