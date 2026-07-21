<?php

namespace App\Http\Controllers;

use App\Models\Hashtag;
use App\Models\HashtagLink;
use App\Models\GroupSubject;
use App\Models\Post;
use Illuminate\Http\Request;

class HashtagController extends Controller
{
    /**
     * عرض المواضيع المرتبطة بهاشتاج معين
     */
    public function show($name)
    {
        // البحث عن الهاشتاج بالاسم
        $hashtag = Hashtag::where('name', $name)->first();

        $subjects = collect();
        $posts = collect();

        if ($hashtag) {
            // 1. جلب المواضيع النقاشية في المجموعات (content_type_id = 5)
            $subjectIds = HashtagLink::where('hashtag_id', $hashtag->id)
                ->where('content_type_id', 5)
                ->pluck('content_id');

            $subjects = GroupSubject::with(['user', 'comments.user', 'reactions'])
                ->whereIn('id', $subjectIds)
                ->latest()
                ->get();

            // 2. جلب المنشورات العامة (content_type_id = 1)
            $postIds = HashtagLink::where('hashtag_id', $hashtag->id)
                ->where('content_type_id', 1)
                ->pluck('content_id');

            $posts = Post::with(['user', 'poll.options', 'media'])
                ->whereIn('id', $postIds)
                ->latest()
                ->get();
        }

        return view('frontend.wiselook.pages.hashtag_subjects', compact('hashtag', 'name', 'subjects', 'posts'));
    }
}
