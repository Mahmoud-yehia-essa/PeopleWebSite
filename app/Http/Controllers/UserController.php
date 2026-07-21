<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * عرض كل المستخدمين
     */
    public function allUsers()
    {
        // عرض الأعضاء العاديين (دورهم user)
        $users = User::where('role', 'user')->latest()->get();
        return view('admin.users.all_users', compact('users'));
    }

    /**
     * عرض كل المديرين
     */
    public function allAdmin()
    {
        // عرض المدراء (دورهم admin)
        $users = User::where('role', 'admin')->latest()->get();
        return view('admin.users.all_admin', compact('users'));
    }

    /**
     * شاشة إضافة مستخدم جديد
     */
    public function addUser()
    {
        $countryList = [
            ['code' => 'KWT', 'dial' => '+965', 'name' => 'الكويت', 'flag' => '🇰🇼'],
            ['code' => 'SAU', 'dial' => '+966', 'name' => 'السعودية', 'flag' => '🇸🇦'],
            ['code' => 'UAE', 'dial' => '+971', 'name' => 'الإمارات', 'flag' => '🇦🇪'],
            ['code' => 'QAT', 'dial' => '+974', 'name' => 'قطر', 'flag' => '🇶🇦'],
            ['code' => 'EGY', 'dial' => '+20', 'name' => 'مصر', 'flag' => '🇪🇬']
        ];
        return view('admin.users.add_user', compact('countryList'));
    }

    /**
     * حفظ مستخدم جديد في قاعدة البيانات
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'role' => 'required|string|in:admin,user',
            'fname' => 'required|string|max:50',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'required|string|max:20',
            'country_data' => 'required|string',
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // فك تشفير بيانات الدولة المحددة
        $countryData = json_decode($request->country_data, true);
        $dial = $countryData['dial'] ?? '';
        $flag = $countryData['flag'] ?? '';

        // دمج رمز الدولة مع الهاتف
        $phoneNumber = $dial . $request->phone;

        // معالجة الصورة
        $photoName = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $photoName = date('YmdHis') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('new_wiselook/uploads'), $photoName);
        }

        // إنشاء المستخدم
        $user = new User();
        $user->first_name = $request->fname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->password_hash = md5($request->password);
        $user->phone_number = $phoneNumber;
        $user->country_flag = $flag;
        $user->address = $request->address;
        $user->profile_picture = $photoName;
        $user->status = 1; // نشط افتراضياً
        $user->is_active = 1;
        $user->role = $request->role; // تعيين الدور عبر الميوتر

        $user->save();

        $notification = [
            'message' => 'تم إضافة المستخدم بنجاح',
            'alert-type' => 'success'
        ];

        return redirect()->route($request->role === 'admin' ? 'all.admin' : 'all.users')->with($notification);
    }

    /**
     * شاشة تعديل مستخدم
     */
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        $countryList = [
            ['code' => 'KWT', 'dial' => '+965', 'name' => 'الكويت', 'flag' => '🇰🇼'],
            ['code' => 'SAU', 'dial' => '+966', 'name' => 'السعودية', 'flag' => '🇸🇦'],
            ['code' => 'UAE', 'dial' => '+971', 'name' => 'الإمارات', 'flag' => '🇦🇪'],
            ['code' => 'QAT', 'dial' => '+974', 'name' => 'قطر', 'flag' => '🇶🇦'],
            ['code' => 'EGY', 'dial' => '+20', 'name' => 'مصر', 'flag' => '🇪🇬']
        ];
        return view('admin.users.edit_user', compact('user', 'countryList'));
    }

    /**
     * تحديث بيانات مستخدم
     */
    public function updateUser(Request $request)
    {
        $id = $request->id;
        $user = User::findOrFail($id);

        $request->validate([
            'role' => 'required|string|in:admin,user',
            'fname' => 'required|string|max:50',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'phone' => 'required|string|max:20',
            'country_data' => 'required|string',
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // فك تشفير بيانات الدولة
        $countryData = json_decode($request->country_data, true);
        $dial = $countryData['dial'] ?? '';
        $flag = $countryData['flag'] ?? '';

        // دمج رقم الهاتف
        $phoneNumber = $dial . $request->phone;

        // معالجة الصورة
        $photoName = $user->profile_picture;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            // حذف الصورة القديمة
            if ($user->profile_picture && File::exists(public_path('new_wiselook/uploads/' . $user->profile_picture))) {
                File::delete(public_path('new_wiselook/uploads/' . $user->profile_picture));
            }
            $photoName = date('YmdHis') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('new_wiselook/uploads'), $photoName);
        }

        $user->first_name = $request->fname;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->password_hash = md5($request->password);
        }
        $user->phone_number = $phoneNumber;
        $user->country_flag = $flag;
        $user->address = $request->address;
        $user->profile_picture = $photoName;
        $user->role = $request->role;

        $user->save();

        $notification = [
            'message' => 'تم تحديث بيانات المستخدم بنجاح',
            'alert-type' => 'success'
        ];

        return redirect()->route($user->role === 'user' ? 'all.users' : 'all.admin')->with($notification);
    }

    /**
     * حذف مستخدم
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        // حذف الصورة الشخصية إذا وجدت
        if ($user->profile_picture && File::exists(public_path('new_wiselook/uploads/' . $user->profile_picture))) {
            File::delete(public_path('new_wiselook/uploads/' . $user->profile_picture));
        }

        $user->delete();

        $notification = [
            'message' => 'تم حذف المستخدم بنجاح',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * إيقاف تفعيل المستخدم
     */
    public function inactiveUser($id)
    {
        $user = User::findOrFail($id);
        $user->status = 0;
        $user->save();

        $notification = [
            'message' => 'تم إيقاف تفعيل المستخدم بنجاح',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * تفعيل المستخدم
     */
    public function activeUser($id)
    {
        $user = User::findOrFail($id);
        $user->status = 1;
        $user->save();

        $notification = [
            'message' => 'تم تفعيل المستخدم بنجاح',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }
}
