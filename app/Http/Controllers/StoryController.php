<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class StoryController extends Controller
{
    /**
     * عرض كل القصص
     */
    public function allStories()
    {
        $stories = Story::with(['user'])->withCount('views')->latest()->get();
        return view('admin.stories.all_stories', compact('stories'));
    }

    /**
     * شاشة إضافة قصة جديدة
     */
    public function addStory()
    {
        $users = User::where('is_active', 1)->orderBy('first_name', 'asc')->get();
        return view('admin.stories.add_story', compact('users'));
    }

    /**
     * حفظ قصة جديدة في قاعدة البيانات
     */
    public function storeStory(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'content' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'video' => 'nullable|mimes:mp4,mov,avi,wmv|max:20480',
            'expires_at' => 'nullable|date',
            'is_active' => 'required|in:0,1',
        ], [
            'user_id.required' => 'يرجى اختيار ناشر القصة.',
        ]);

        // التحقق من إدخال نص أو ميديا على الأقل
        if (empty($request->content) && !$request->hasFile('image') && !$request->hasFile('video')) {
            return redirect()->back()->withInput()->with([
                'message' => 'يجب إدخال نص للقصة أو رفع صورة أو فيديو على الأقل.',
                'alert-type' => 'error'
            ]);
        }

        try {
            $story = new Story();
            $story->user_id = $request->user_id;
            $story->content = $request->content;
            $story->view_count = 0;
            $story->is_active = $request->is_active;

            // تحديد تاريخ انتهاء الصلاحية
            if (!empty($request->expires_at)) {
                $story->expires_at = $request->expires_at;
            } else {
                $story->expires_at = now()->addDay(); // الافتراضي بعد 24 ساعة
            }

            // معالجة رفع الصورة
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = date('YmdHis') . '_st_img.' . $image->getClientOriginalExtension();
                $image->move(public_path('upload/stories'), $imageName);
                $story->image = $imageName;
            }

            // معالجة رفع الفيديو
            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $videoName = date('YmdHis') . '_st_vid.' . $video->getClientOriginalExtension();
                $video->move(public_path('upload/stories'), $videoName);
                $story->video = $videoName;
            }

            $story->save();

            $notification = [
                'message' => 'تم إضافة القصة بنجاح.',
                'alert-type' => 'success'
            ];

            return redirect()->route('all.stories')->with($notification);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with([
                'message' => 'حدث خطأ أثناء حفظ القصة: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * شاشة تعديل القصة
     */
    public function editStory($id)
    {
        $story = Story::findOrFail($id);
        $users = User::where('is_active', 1)->orderBy('first_name', 'asc')->get();
        return view('admin.stories.edit_story', compact('story', 'users'));
    }

    /**
     * تحديث بيانات قصة
     */
    public function updateStory(Request $request)
    {
        $id = $request->id;
        $story = Story::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'content' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'video' => 'nullable|mimes:mp4,mov,avi,wmv|max:20480',
            'expires_at' => 'nullable|date',
            'is_active' => 'required|in:0,1',
        ], [
            'user_id.required' => 'يرجى اختيار ناشر القصة.',
        ]);

        // التحقق من وجود ميديا أو نص
        if (empty($request->content) && !$request->hasFile('image') && !$request->hasFile('video') && empty($story->image) && empty($story->video)) {
            return redirect()->back()->withInput()->with([
                'message' => 'يجب إدخال نص للقصة أو رفع صورة أو فيديو على الأقل.',
                'alert-type' => 'error'
            ]);
        }

        try {
            $story->user_id = $request->user_id;
            $story->content = $request->content;
            $story->is_active = $request->is_active;

            if (!empty($request->expires_at)) {
                $story->expires_at = $request->expires_at;
            } else {
                $story->expires_at = now()->addDay();
            }

            // تحديث الصورة إن رفعت جديدة
            if ($request->hasFile('image')) {
                // حذف الصورة القديمة
                if ($story->image && !filter_var($story->image, FILTER_VALIDATE_URL)) {
                    $oldPath = public_path('upload/stories/' . $story->image);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }
                // رفع الصورة الجديدة
                $image = $request->file('image');
                $imageName = date('YmdHis') . '_st_img.' . $image->getClientOriginalExtension();
                $image->move(public_path('upload/stories'), $imageName);
                $story->image = $imageName;
                
                // إلغاء الفيديو القديم لضمان تماسك نوع القصة (ميديا واحدة)
                if ($story->video && !filter_var($story->video, FILTER_VALIDATE_URL)) {
                    $oldVidPath = public_path('upload/stories/' . $story->video);
                    if (File::exists($oldVidPath)) {
                        File::delete($oldVidPath);
                    }
                }
                $story->video = null;
            }

            // تحديث الفيديو إن رفع جديد
            if ($request->hasFile('video')) {
                // حذف الفيديو القديم
                if ($story->video && !filter_var($story->video, FILTER_VALIDATE_URL)) {
                    $oldPath = public_path('upload/stories/' . $story->video);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }
                // رفع الفيديو الجديد
                $video = $request->file('video');
                $videoName = date('YmdHis') . '_st_vid.' . $video->getClientOriginalExtension();
                $video->move(public_path('upload/stories'), $videoName);
                $story->video = $videoName;
                
                // إلغاء الصورة القديمة لضمان تماسك نوع القصة (ميديا واحدة)
                if ($story->image && !filter_var($story->image, FILTER_VALIDATE_URL)) {
                    $oldImgPath = public_path('upload/stories/' . $story->image);
                    if (File::exists($oldImgPath)) {
                        File::delete($oldImgPath);
                    }
                }
                $story->image = null;
            }

            $story->save();

            $notification = [
                'message' => 'تم تحديث القصة بنجاح.',
                'alert-type' => 'success'
            ];

            return redirect()->route('all.stories')->with($notification);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with([
                'message' => 'حدث خطأ أثناء تعديل البيانات: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * حذف قصة
     */
    public function deleteStory($id)
    {
        $story = Story::findOrFail($id);

        try {
            // حذف الميديا المرفوعة محلياً
            if ($story->image && !filter_var($story->image, FILTER_VALIDATE_URL)) {
                $oldImgPath = public_path('upload/stories/' . $story->image);
                if (File::exists($oldImgPath)) {
                    File::delete($oldImgPath);
                }
            }

            if ($story->video && !filter_var($story->video, FILTER_VALIDATE_URL)) {
                $oldVidPath = public_path('upload/stories/' . $story->video);
                if (File::exists($oldVidPath)) {
                    File::delete($oldVidPath);
                }
            }

            // حذف سجلات المشاهدات المرتبطة بالقصة
            DB::table('story_seen')->where('story_id', $story->id)->delete();

            // حذف القصة
            $story->delete();

            $notification = [
                'message' => 'تم حذف القصة بنجاح.',
                'alert-type' => 'success'
            ];
            return redirect()->back()->with($notification);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'message' => 'حدث خطأ أثناء حذف البيانات: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * تفعيل القصة
     */
    public function activeStory($id)
    {
        $story = Story::findOrFail($id);
        $story->is_active = 1;
        $story->save();

        $notification = [
            'message' => 'تم تفعيل القصة بنجاح.',
            'alert-type' => 'success'
        ];
        return redirect()->back()->with($notification);
    }

    /**
     * إيقاف تفعيل القصة
     */
    public function inactiveStory($id)
    {
        $story = Story::findOrFail($id);
        $story->is_active = 0;
        $story->save();

        $notification = [
            'message' => 'تم إيقاف تفعيل القصة بنجاح.',
            'alert-type' => 'success'
        ];
        return redirect()->back()->with($notification);
    }

    /**
     * جلب معلومات المستخدمين الذين شاهدوا القصة لعرضهم في المودال عبر AJAX
     */
    public function getStoryViewers($id)
    {
        $viewers = \App\Models\StorySeen::with(['user'])
            ->where('story_id', $id)
            ->latest('viewed_at')
            ->get();

        return response()->json([
            'success' => true,
            'viewers' => $viewers->map(function($view) {
                return [
                    'user_name' => $view->user ? trim($view->user->first_name . ' ' . $view->user->last_name) : 'مستخدم غير معروف',
                    'profile_picture' => ($view->user && $view->user->profile_picture && $view->user->profile_picture != 'non') 
                        ? (filter_var($view->user->profile_picture, FILTER_VALIDATE_URL) ? $view->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $view->user->profile_picture) 
                        : url('upload/no_image.jpg'),
                    'email' => $view->user ? $view->user->email : 'N/A',
                    'viewed_at' => $view->viewed_at ? $view->viewed_at->diffForHumans() : '',
                ];
            })
        ]);
     }

    /**
     * حفظ قصة جديدة من الواجهة الأمامية عبر AJAX
     */
    public function storeFrontendStory(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'video' => 'nullable|mimes:mp4,mov,avi,wmv|max:25600',
        ]);

        if (empty($request->content) && !$request->hasFile('image') && !$request->hasFile('video')) {
            return response()->json([
                'success' => false,
                'message' => 'يجب إدخال نص للقصة أو رفع صورة أو فيديو على الأقل.'
            ], 422);
        }

        try {
            $story = new Story();
            $story->user_id = auth()->id();
            $story->content = $request->content;
            $story->view_count = 0;
            $story->is_active = 1;
            $story->expires_at = now()->addDay(); // الصلاحية 24 ساعة

            // رفع الصورة
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = date('YmdHis') . '_st_img.' . $image->getClientOriginalExtension();
                $image->move(public_path('upload/stories'), $imageName);
                $story->image = $imageName;
            }

            // رفع الفيديو
            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $videoName = date('YmdHis') . '_st_vid.' . $video->getClientOriginalExtension();
                $video->move(public_path('upload/stories'), $videoName);
                $story->video = $videoName;
            }

            $story->save();

            $user = auth()->user();
            $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            $userAvatar = url('upload/no_image.jpg');
            if ($user && $user->profile_picture && $user->profile_picture !== 'non') {
                $userAvatar = filter_var($user->profile_picture, FILTER_VALIDATE_URL)
                    ? $user->profile_picture
                    : asset('new_wiselook/uploads/' . $user->profile_picture);
            }

            $mediaPath = '';
            $mediaType = 'text';
            if ($story->image) {
                $mediaPath = asset('upload/stories/' . $story->image);
                $mediaType = 'image';
            } elseif ($story->video) {
                $mediaPath = asset('upload/stories/' . $story->video);
                $mediaType = 'video';
            }

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة القصة بنجاح.',
                'story' => [
                    'id' => $story->id,
                    'content' => $story->content,
                    'media' => $mediaPath,
                    'type' => $mediaType,
                    'view_count' => 0,
                    'is_owner' => true,
                    'is_seen' => 1,
                    'created_at' => $story->created_at ? $story->created_at->diffForHumans() : 'الآن',
                    'user_name' => $userName,
                    'user_avatar' => $userAvatar,
                    'user_id' => $story->user_id
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ القصة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تسجيل مشاهدة القصة من الواجهة الأمامية عبر AJAX
     */
    public function markFrontendStorySeen(Request $request, $id)
    {
        $userId = auth()->id();
        $story = Story::findOrFail($id);

        // التحقق من وجود مشاهدة سابقة لمنع التكرار
        $seen = \App\Models\StorySeen::where('story_id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$seen) {
            \App\Models\StorySeen::create([
                'story_id' => $id,
                'user_id' => $userId,
                'viewed_at' => now(),
                'is_active' => 1
            ]);

            $story->increment('view_count');
        } else {
            $seen->update([
                'viewed_at' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل مشاهدة القصة بنجاح.',
            'view_count' => $story->view_count
        ]);
    }

    /**
     * حذف قصة من الواجهة الأمامية عبر AJAX
     */
    public function deleteFrontendStory($id)
    {
        $story = Story::findOrFail($id);
        
        // التحقق من الصلاحية (أن يكون هو صاحب القصة)
        if ($story->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذه القصة.'
            ], 403);
        }

        try {
            // حذف الميديا المرفوعة محلياً
            if ($story->image && !filter_var($story->image, FILTER_VALIDATE_URL)) {
                $oldImgPath = public_path('upload/stories/' . $story->image);
                if (File::exists($oldImgPath)) {
                    File::delete($oldImgPath);
                }
            }

            if ($story->video && !filter_var($story->video, FILTER_VALIDATE_URL)) {
                $oldVidPath = public_path('upload/stories/' . $story->video);
                if (File::exists($oldVidPath)) {
                    File::delete($oldVidPath);
                }
            }

            // حذف سجلات المشاهدات المرتبطة بالقصة
            DB::table('story_seen')->where('story_id', $story->id)->delete();

            // حذف القصة
            $story->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف القصة بنجاح.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف القصة: ' . $e->getMessage()
            ], 500);
        }
    }
}
