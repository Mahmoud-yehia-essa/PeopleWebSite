<?php

namespace App\Http\Controllers;

use App\Models\SavedPost;
use App\Models\Post;
use Illuminate\Http\Request;

class SavedPostController extends Controller
{
    /**
     * عرض قائمة المواضيع المحفوظة من قبل المستخدمين (الإدارة)
     */
    public function allSavedPosts()
    {
        $savedPosts = SavedPost::with(['user', 'post.user'])->latest()->get();
        return view('admin.posts.all_saved_posts', compact('savedPosts'));
    }

    /**
     * إلغاء حفظ منشور لمستخدم محدد (حذف سجل الحفظ - الإدارة)
     */
    public function deleteSavedPost($id)
    {
        $savedPost = SavedPost::findOrFail($id);
        $savedPost->delete();

        $notification = [
            'message' => 'تم إلغاء حفظ المنشور للمستخدم بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * تفعيل أو إلغاء حفظ موضوع للمستخدم الحالي في الواجهة الأمامية عبر AJAX
     */
    public function toggleSaveFrontend($id)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => __t('must_login_to_save')
            ], 401);
        }

        $userId = auth()->id();
        $post = Post::findOrFail($id);

        $savedPost = SavedPost::where('user_id', $userId)->where('post_id', $id)->first();

        if ($savedPost) {
            $savedPost->delete();
            return response()->json([
                'success' => true,
                'action' => 'unsaved',
                'message' => __t('post_unsaved_success')
            ]);
        } else {
            SavedPost::create([
                'user_id' => $userId,
                'post_id' => $id
            ]);
            return response()->json([
                'success' => true,
                'action' => 'saved',
                'message' => __t('post_saved_success')
            ]);
        }
    }

    /**
     * عرض المواضيع المحفوظة للمستخدم الحالي في الواجهة الأمامية
     */
    public function indexFrontend(Request $request)
    {
        $userId = auth()->id();
        $savedPosts = SavedPost::where('user_id', $userId)
            ->with(['post.user', 'post.poll.options'])
            ->latest()
            ->get();

        $posts = $savedPosts->map(function ($sp) {
            return $sp->post;
        })->filter();

        return view('frontend.wiselook.pages.saved_posts', compact('posts'));
    }
}
