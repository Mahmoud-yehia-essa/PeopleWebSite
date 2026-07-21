<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ranking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RankingController extends Controller
{
    /**
     * عرض كل الرتب المتاحة
     */
    public function allRankings()
    {
        $rankings = Ranking::orderBy('rank_order', 'asc')->get();
        return view('admin.ranking.all_rankings', compact('rankings'));
    }

    /**
     * شاشة إضافة رتبة جديدة
     */
    public function addRanking()
    {
        return view('admin.ranking.add_ranking');
    }

    /**
     * حفظ الرتبة الجديدة
     */
    public function storeRanking(Request $request)
    {
        $rules = [
            'rank_name' => 'required|string|max:255',
            'rank_description' => 'nullable|string',
            'rank_order' => 'required|integer|min:1',
            'rank_start_point' => 'required|integer|min:0',
            'level_reward_amount' => 'nullable|integer|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ];

        // إذا لم تكن الرتبة الأخيرة، فإن نقطة النهاية مطلوبة
        if (!$request->has('is_last')) {
            $rules['rank_end_point'] = 'required|integer|gte:rank_start_point';
        } else {
            $rules['rank_end_point'] = 'nullable|integer';
        }

        $request->validate($rules, [
            'rank_name.required' => 'يرجى إدخال اسم الرتبة.',
            'rank_order.required' => 'يرجى إدخال ترتيب الرتبة.',
            'rank_order.integer' => 'يجب أن يكون الترتيب رقماً صحيحاً.',
            'rank_start_point.required' => 'يرجى تحديد بداية نقاط الرتبة.',
            'rank_start_point.integer' => 'يجب أن تكون نقاط البداية رقماً صحيحاً.',
            'rank_end_point.required' => 'يرجى تحديد نهاية نقاط الرتبة.',
            'rank_end_point.integer' => 'يجب أن تكون نقاط النهاية رقماً صحيحاً.',
            'rank_end_point.gte' => 'يجب أن تكون نقاط النهاية أكبر من أو تساوي نقاط البداية.',
            'level_reward_amount.integer' => 'يجب أن تكون قيمة المكافأة رقماً صحيحاً.',
            'photo.image' => 'يجب أن يكون الملف المرفوع صورة.',
            'photo.mimes' => 'صيغ الصور المسموح بها هي: jpeg, png, jpg, gif, svg, webp.',
            'photo.max' => 'أقصى حجم مسموح به للصورة هو 2 ميجابايت.',
        ]);

        DB::beginTransaction();
        try {
            $isLast = $request->has('is_last') ? 1 : 0;

            // إذا تم تعيين هذه الرتبة كالرتبة الأخيرة، نقوم بإلغاء ذلك من الرتب الأخرى
            if ($isLast === 1) {
                Ranking::where('is_last', 1)->update(['is_last' => 0]);
            }

            $photo_name = null;
            if ($request->file('photo')) {
                $file = $request->file('photo');
                $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('upload/rankings'), $filename);
                $photo_name = $filename;
            }

            Ranking::create([
                'rank_name' => $request->rank_name,
                'rank_description' => $request->rank_description,
                'rank_order' => $request->rank_order,
                'rank_start_point' => $request->rank_start_point,
                'rank_end_point' => $isLast === 1 ? null : $request->rank_end_point,
                'level_reward_amount' => $request->level_reward_amount ?? 0,
                'is_last' => $isLast,
                'photo' => $photo_name,
            ]);

            DB::commit();

            $notification = [
                'message' => 'تم إضافة الرتبة بنجاح.',
                'alert-type' => 'success'
            ];

            return redirect()->route('all.rankings')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with([
                'message' => 'حدث خطأ أثناء حفظ الرتبة: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * شاشة تعديل رتبة حالية
     */
    public function editRanking($id)
    {
        $ranking = Ranking::findOrFail($id);
        return view('admin.ranking.edit_ranking', compact('ranking'));
    }

    /**
     * تحديث بيانات الرتبة
     */
    public function updateRanking(Request $request)
    {
        $id = $request->id;
        $ranking = Ranking::findOrFail($id);

        $rules = [
            'rank_name' => 'required|string|max:255',
            'rank_description' => 'nullable|string',
            'rank_order' => 'required|integer|min:1',
            'rank_start_point' => 'required|integer|min:0',
            'level_reward_amount' => 'nullable|integer|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ];

        if (!$request->has('is_last')) {
            $rules['rank_end_point'] = 'required|integer|gte:rank_start_point';
        } else {
            $rules['rank_end_point'] = 'nullable|integer';
        }

        $request->validate($rules, [
            'rank_name.required' => 'يرجى إدخال اسم الرتبة.',
            'rank_order.required' => 'يرجى إدخال ترتيب الرتبة.',
            'rank_order.integer' => 'يجب أن يكون الترتيب رقماً صحيحاً.',
            'rank_start_point.required' => 'يرجى تحديد بداية نقاط الرتبة.',
            'rank_start_point.integer' => 'يجب أن تكون نقاط البداية رقماً صحيحاً.',
            'rank_end_point.required' => 'يرجى تحديد نهاية نقاط الرتبة.',
            'rank_end_point.integer' => 'يجب أن تكون نقاط النهاية رقماً صحيحاً.',
            'rank_end_point.gte' => 'يجب أن تكون نقاط النهاية أكبر من أو تساوي نقاط البداية.',
            'level_reward_amount.integer' => 'يجب أن تكون قيمة المكافأة رقماً صحيحاً.',
            'photo.image' => 'يجب أن يكون الملف المرفوع صورة.',
            'photo.mimes' => 'صيغ الصور المسموح بها هي: jpeg, png, jpg, gif, svg, webp.',
            'photo.max' => 'أقصى حجم مسموح به للصورة هو 2 ميجابايت.',
        ]);

        DB::beginTransaction();
        try {
            $isLast = $request->has('is_last') ? 1 : 0;

            if ($isLast === 1) {
                // إلغاء الرتبة الأخيرة من الرتب الأخرى
                Ranking::where('id', '!=', $id)->where('is_last', 1)->update(['is_last' => 0]);
            }

            $photo_name = $ranking->photo;
            if ($request->file('photo')) {
                $file = $request->file('photo');
                $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('upload/rankings'), $filename);
                
                // حذف الصورة القديمة
                if ($ranking->photo && file_exists(public_path('upload/rankings/' . $ranking->photo))) {
                    @unlink(public_path('upload/rankings/' . $ranking->photo));
                }
                
                $photo_name = $filename;
            }

            $ranking->update([
                'rank_name' => $request->rank_name,
                'rank_description' => $request->rank_description,
                'rank_order' => $request->rank_order,
                'rank_start_point' => $request->rank_start_point,
                'rank_end_point' => $isLast === 1 ? null : $request->rank_end_point,
                'level_reward_amount' => $request->level_reward_amount ?? 0,
                'is_last' => $isLast,
                'photo' => $photo_name,
            ]);

            DB::commit();

            $notification = [
                'message' => 'تم تحديث الرتبة بنجاح.',
                'alert-type' => 'success'
            ];

            return redirect()->route('all.rankings')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with([
                'message' => 'حدث خطأ أثناء تحديث الرتبة: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * حذف رتبة
     */
    public function deleteRanking($id)
    {
        $ranking = Ranking::findOrFail($id);
        
        // حذف الصورة المرتبطة
        if ($ranking->photo && file_exists(public_path('upload/rankings/' . $ranking->photo))) {
            @unlink(public_path('upload/rankings/' . $ranking->photo));
        }

        $ranking->delete();

        $notification = [
            'message' => 'تم حذف الرتبة بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * عرض نقاط ورتب المستخدمين مع إمكانية التصفية
     */
    public function usersRankings(Request $request)
    {
        // جلب جميع الرتب للفلتر المنسدل
        $rankings = Ranking::orderBy('rank_order', 'asc')->get();

        $selectedRankId = $request->get('rank_id');
        $query = User::query();

        // التصفية بحسب الرتبة المحددة من جدول الرتب ونطاق النقاط
        if (!empty($selectedRankId)) {
            $rank = Ranking::findOrFail($selectedRankId);
            
            if ($rank->is_last) {
                // الرتبة الأخيرة تعني النقاط أكبر من أو تساوي البداية
                $query->where('points', '>=', $rank->rank_start_point);
            } else {
                // نطاق نقاط محدد
                $query->where('points', '>=', $rank->rank_start_point)
                      ->where('points', '<=', $rank->rank_end_point);
            }
        }

        $users = $query->orderBy('points', 'desc')->get();

        // إدراج اسم الرتبة المناسبة ديناميكياً لكل مستخدم
        foreach ($users as $user) {
            $userPoints = $user->points ?? 0;
            
            $user->current_rank = $rankings->first(function ($rank) use ($userPoints) {
                if ($rank->is_last) {
                    return $userPoints >= $rank->rank_start_point;
                }
                return $userPoints >= $rank->rank_start_point && $userPoints <= $rank->rank_end_point;
            });
        }

        return view('admin.ranking.users_rankings', compact('users', 'rankings', 'selectedRankId'));
    }
}
