<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Friendship;
use App\Models\GroupSite;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * عرض شاشة اختيار الفترات الزمنية للتقارير
     */
    public function reportView()
    {
        return view('admin.report.report_view');
    }

    /**
     * تقرير اليوم المحدد
     */
    public function searchByDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ], [
            'date.required' => 'يرجى تحديد التاريخ أولاً.',
            'date.date' => 'صيغة التاريخ غير صحيحة.',
        ]);

        $startDate = Carbon::parse($request->date)->startOfDay();
        $endDate = Carbon::parse($request->date)->endOfDay();
        $periodTitle = "تقرير يوم: " . Carbon::parse($request->date)->format('Y-m-d');

        return $this->generateReportData($startDate, $endDate, $periodTitle);
    }

    /**
     * تقرير الشهر المحدد
     */
    public function searchByMonth(Request $request)
    {
        $request->validate([
            'month' => 'required|string|not_in:non',
            'year_name' => 'required|string|not_in:non',
        ], [
            'month.required' => 'يرجى تحديد الشهر.',
            'month.not_in' => 'يرجى تحديد الشهر.',
            'year_name.required' => 'يرجى تحديد السنة.',
            'year_name.not_in' => 'يرجى تحديد السنة.',
        ]);

        $months = [
            'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
            'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
            'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12
        ];

        $arabicMonths = [
            'January' => 'يناير', 'February' => 'فبراير', 'March' => 'مارس', 'April' => 'أبريل',
            'May' => 'مايو', 'June' => 'يونيو', 'July' => 'يوليو', 'August' => 'أغسطس',
            'September' => 'سبتمبر', 'October' => 'أكتوبر', 'November' => 'نوفمبر', 'December' => 'ديسمبر'
        ];

        $monthNum = $months[$request->month] ?? 1;
        $arabicMonth = $arabicMonths[$request->month] ?? $request->month;
        $year = $request->year_name;

        $startDate = Carbon::createFromDate($year, $monthNum, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $monthNum, 1)->endOfMonth();
        $periodTitle = "تقرير شهر: " . $arabicMonth . " - " . $year;

        return $this->generateReportData($startDate, $endDate, $periodTitle);
    }

    /**
     * تقرير السنة المحددة
     */
    public function searchByYear(Request $request)
    {
        $request->validate([
            'years' => 'required|string|not_in:non',
        ], [
            'years.required' => 'يرجى تحديد السنة.',
            'years.not_in' => 'يرجى تحديد السنة.',
        ]);

        $year = $request->years;
        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
        $periodTitle = "تقرير سنة: " . $year;

        return $this->generateReportData($startDate, $endDate, $periodTitle);
    }

    /**
     * تقرير فترة زمنية مخصصة (من - إلى)
     */
    public function searchByRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ], [
            'start_date.required' => 'يرجى تحديد تاريخ البداية.',
            'start_date.date' => 'صيغة تاريخ البداية غير صحيحة.',
            'end_date.required' => 'يرجى تحديد تاريخ النهاية.',
            'end_date.date' => 'صيغة تاريخ النهاية غير صحيحة.',
            'end_date.after_or_equal' => 'يجب أن يكون تاريخ النهاية مساوياً أو لاحقاً لتاريخ البداية.',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $periodTitle = "تقرير الفترة من: " . Carbon::parse($request->start_date)->format('Y-m-d') . " إلى: " . Carbon::parse($request->end_date)->format('Y-m-d');

        return $this->generateReportData($startDate, $endDate, $periodTitle);
    }

    /**
     * توليد واسترجاع إحصائيات التقرير وعرض النتائج
     */
    private function generateReportData($startDate, $endDate, $periodTitle)
    {
        // 1. عدد الأعضاء الجدد المسجلين
        $usersCount = User::whereBetween('created_at', [$startDate, $endDate])->count();

        // 2. عدد المواضيع (المنشورات) المنشأة
        $postsCount = Post::whereBetween('created_at', [$startDate, $endDate])->count();

        // 3. عدد طلبات الصداقة التي تمت (المقبولة)
        $friendshipsCount = Friendship::where('is_active', 1)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->count();

        // 4. عدد المجموعات الخاصة والعامة المنشأة
        $groupsCount = GroupSite::whereBetween('created_at', [$startDate, $endDate])->count();

        // 5. عدد القصص المرفوعة
        $storiesCount = Story::whereBetween('created_at', [$startDate, $endDate])->count();

        // 6. المستخدم الأكثر نشاطاً (أكثر من قام بنشر مواضيع في الفترة المحددة)
        $mostActiveUser = User::whereHas('posts', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->withCount(['posts' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->orderBy('posts_count', 'desc')
            ->first();

        // 7. المواضيع الخمسة الأكثر تعليقاً في تلك الفترة
        $topCommentedPosts = Post::join('comments', 'comments.post_id', '=', 'posts.id')
            ->whereBetween('comments.created_at', [$startDate, $endDate])
            ->whereNull('comments.deleted_at')
            ->select('posts.*', DB::raw('count(comments.id) as comments_count_period'))
            ->groupBy('posts.id')
            ->orderBy('comments_count_period', 'desc')
            ->limit(5)
            ->with('user')
            ->get();

        // 8. المواضيع الخمسة الأكثر تفاعلاً وإعجاباً في تلك الفترة
        $topReactedPosts = Post::join('reactions', 'reactions.content_id', '=', 'posts.id')
            ->where('reactions.content_type_id', 1) // 1 = post
            ->whereBetween('reactions.created_at', [$startDate, $endDate])
            ->whereNull('reactions.deleted_at')
            ->select('posts.*', DB::raw('count(reactions.id) as reactions_count_period'))
            ->groupBy('posts.id')
            ->orderBy('reactions_count_period', 'desc')
            ->limit(5)
            ->with('user')
            ->get();

        return view('admin.report.report_results', compact(
            'periodTitle',
            'startDate',
            'endDate',
            'usersCount',
            'postsCount',
            'friendshipsCount',
            'groupsCount',
            'storiesCount',
            'mostActiveUser',
            'topCommentedPosts',
            'topReactedPosts'
        ));
    }
}
