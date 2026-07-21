<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Group;
use App\Models\GroupSite;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * توليد تنويعات الكلمات العربية (ى/ي، أ/إ/آ/ا، ة/ه) لتحسين دقة البحث
     */
    private function getArabicVariations($query)
    {
        $variations = [$query];
        
        // استبدال الياء والألف المقصورة
        if (str_contains($query, 'ى') || str_contains($query, 'ي')) {
            $variations[] = str_replace('ى', 'ي', $query);
            $variations[] = str_replace('ي', 'ى', $query);
        }
        
        // استبدال الهمزات بالألف العادية والعكس
        if (str_contains($query, 'أ') || str_contains($query, 'إ') || str_contains($query, 'آ') || str_contains($query, 'ا')) {
            $normalized = str_replace(['أ', 'إ', 'آ'], 'ا', $query);
            $variations[] = $normalized;
            $variations[] = str_replace('ا', 'أ', $normalized);
            $variations[] = str_replace('ا', 'إ', $normalized);
        }
        
        // استبدال التاء المربوطة بالهاء والعكس
        if (str_contains($query, 'ة') || str_contains($query, 'ه')) {
            $variations[] = str_replace('ة', 'ه', $query);
            $variations[] = str_replace('ه', 'ة', $query);
        }

        return array_unique($variations);
    }

    /**
     * عرض صفحة البحث الرئيسية للوحة التحكم (Admin)
     */
    public function searchForm(Request $request)
    {
        $query = $request->get('query');
        $types = $request->get('types', ['users', 'posts', 'groups']);

        $users = collect();
        $posts = collect();
        $groups = collect();
        $groupSites = collect();

        if (!empty($query)) {
            $variations = $this->getArabicVariations($query);

            // 1. البحث في الأشخاص
            if (in_array('users', $types)) {
                $users = User::where(function ($q) use ($variations) {
                        foreach ($variations as $var) {
                            $searchVar = '%' . $var . '%';
                            $q->orWhere('first_name', 'like', $searchVar)
                              ->orWhere('last_name', 'like', $searchVar)
                              ->orWhere('email', 'like', $searchVar)
                              ->orWhere('phone_number', 'like', $searchVar)
                              ->orWhere(\Illuminate\Support\Facades\DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', $searchVar);
                        }
                    })
                    ->orderBy('first_name', 'asc')
                    ->limit(20)
                    ->get();
            }

            // 2. البحث في المواضيع
            if (in_array('posts', $types)) {
                $posts = Post::where(function ($q) use ($variations) {
                        foreach ($variations as $var) {
                            $searchVar = '%' . $var . '%';
                            $q->orWhere('content', 'like', $searchVar);
                        }
                    })
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();
            }

            // 3. البحث في المجموعات
            if (in_array('groups', $types)) {
                $groups = Group::where(function ($q) use ($variations) {
                        foreach ($variations as $var) {
                            $searchVar = '%' . $var . '%';
                            $q->orWhere('name', 'like', $searchVar)
                              ->orWhere('descriptions', 'like', $searchVar);
                        }
                    })
                    ->with('creator')
                    ->orderBy('date_created', 'desc')
                    ->limit(20)
                    ->get();

                $groupSites = GroupSite::where(function ($q) use ($variations) {
                        foreach ($variations as $var) {
                            $searchVar = '%' . $var . '%';
                            $q->orWhere('title', 'like', $searchVar)
                              ->orWhere('description', 'like', $searchVar);
                        }
                    })
                    ->with('admin')
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get();
            }
        }

        return view('admin.search.search_form', compact('query', 'types', 'users', 'posts', 'groups', 'groupSites'));
    }

    /**
     * عرض صفحة البحث الرئيسية للواجهة الأمامية
     */
    public function searchFrontend(Request $request)
    {
        $query = $request->get('query');
        $types = $request->get('types', ['users', 'posts', 'groups']);

        $users = collect();
        $posts = collect();
        $groups = collect();
        $groupSites = collect();

        $perPage = 10;
        $page = intval($request->get('page', 1));
        $offset = ($page - 1) * $perPage;
        
        $usersHasMore = false;
        $postsHasMore = false;
        $groupsHasMore = false;
        $groupSitesHasMore = false;

        if (!empty($query)) {
            $variations = $this->getArabicVariations($query);

            // 1. البحث في الأشخاص
            if (in_array('users', $types)) {
                $usersQuery = User::where(function ($q) use ($variations) {
                        foreach ($variations as $var) {
                            $searchVar = '%' . $var . '%';
                            $q->orWhere('first_name', 'like', $searchVar)
                              ->orWhere('last_name', 'like', $searchVar)
                              ->orWhere('email', 'like', $searchVar)
                              ->orWhere(\Illuminate\Support\Facades\DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', $searchVar);
                        }
                    })
                    ->orderBy('first_name', 'asc');

                $usersCount = $usersQuery->count();
                $users = $usersQuery->offset($offset)->limit($perPage)->get();
                $usersHasMore = $usersCount > ($offset + $perPage);
            }

            // 2. البحث في المواضيع
            if (in_array('posts', $types)) {
                $postsQuery = Post::where(function ($q) use ($variations) {
                        foreach ($variations as $var) {
                            $searchVar = '%' . $var . '%';
                            $q->orWhere('content', 'like', $searchVar);
                        }
                    })
                    ->with(['user'])
                    ->orderBy('created_at', 'desc');

                $postsCount = $postsQuery->count();
                $posts = $postsQuery->offset($offset)->limit($perPage)->get();
                $postsHasMore = $postsCount > ($offset + $perPage);
            }

            // 3. البحث في المجموعات
            if (in_array('groups', $types)) {
                $groupsQuery = Group::where(function ($q) use ($variations) {
                        foreach ($variations as $var) {
                            $searchVar = '%' . $var . '%';
                            $q->orWhere('name', 'like', $searchVar)
                              ->orWhere('descriptions', 'like', $searchVar);
                        }
                    })
                    ->with('creator')
                    ->orderBy('date_created', 'desc');

                $groupsCount = $groupsQuery->count();
                $groups = $groupsQuery->offset($offset)->limit($perPage)->get();
                $groupsHasMore = $groupsCount > ($offset + $perPage);

                $groupSitesQuery = GroupSite::where(function ($q) use ($variations) {
                        foreach ($variations as $var) {
                            $searchVar = '%' . $var . '%';
                            $q->orWhere('title', 'like', $searchVar)
                              ->orWhere('description', 'like', $searchVar);
                        }
                    })
                    ->with('admin')
                    ->orderBy('created_at', 'desc');

                $groupSitesCount = $groupSitesQuery->count();
                $groupSites = $groupSitesQuery->offset($offset)->limit($perPage)->get();
                $groupSitesHasMore = $groupSitesCount > ($offset + $perPage);
            }
        }

        $hasMore = $usersHasMore || $postsHasMore || $groupsHasMore || $groupSitesHasMore;

        return view('frontend.wiselook.pages.search', compact('query', 'types', 'users', 'posts', 'groups', 'groupSites', 'hasMore', 'page'));
    }
}
