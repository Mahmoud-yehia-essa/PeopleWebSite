<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class LanguageController extends Controller
{
    // ==========================================
    // إدارة اللغات (Languages CRUD)
    // ==========================================

    /**
     * عرض قائمة اللغات
     */
    public function allLanguages()
    {
        $languages = Language::latest()->get();
        return view('admin.languages.all_languages', compact('languages'));
    }

    /**
     * شاشة إضافة لغة جديدة
     */
    public function addLanguage()
    {
        return view('admin.languages.add_language');
    }

    /**
     * حفظ اللغة الجديدة في قاعدة البيانات
     */
    public function storeLanguage(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:languages,code',
            'direction' => 'required|in:rtl,ltr',
        ];

        if ($request->flag_type === 'image') {
            $rules['flag_path'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        } else {
            $rules['flag_emoji'] = 'required|string|max:10';
        }

        $request->validate($rules, [
            'name.required' => 'يرجى إدخال اسم اللغة.',
            'code.required' => 'يرجى إدخال كود اللغة.',
            'code.unique' => 'كود اللغة هذا مستخدم بالفعل.',
            'direction.required' => 'يرجى تحديد اتجاه النص.',
            'flag_emoji.required' => 'يرجى اختيار العلم من قائمة الرموز التعبيرية.',
            'flag_path.image' => 'يجب أن يكون الملف المرفوع صورة.',
        ]);

        DB::beginTransaction();
        try {
            $language = new Language();
            $language->name = $request->name;
            $language->code = strtolower($request->code);
            $language->direction = $request->direction;
            $language->is_default = $request->has('is_default') ? true : false;
            $language->is_active = $request->has('is_active') ? true : false;

            // إذا تم اختيارها كلغة افتراضية، يجب تفعيلها تلقائياً
            if ($language->is_default) {
                $language->is_active = true;
            }

            // معالجة العلم (رمز تعبيري أو رفع صورة)
            if ($request->flag_type === 'emoji') {
                $language->flag_path = $request->flag_emoji;
            } else {
                if ($request->hasFile('flag_path')) {
                    $flag = $request->file('flag_path');
                    $flagName = date('YmdHis') . '_flag.' . $flag->getClientOriginalExtension();
                    $flag->move(public_path('upload/flags'), $flagName);
                    $language->flag_path = $flagName;
                }
            }

            $language->save();

            // إذا تم تعيين اللغة كلغة افتراضية، نقوم بإلغاء الافتراضية عن اللغات الأخرى
            if ($language->is_default) {
                Language::where('id', '!=', $language->id)->update(['is_default' => false]);
            }

            $this->clearTranslationCache($language->id);
            DB::commit();

            $notification = [
                'message' => 'تم إضافة اللغة بنجاح.',
                'alert-type' => 'success'
            ];

            return redirect()->route('all.languages')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with([
                'message' => 'حدث خطأ أثناء حفظ اللغة: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * شاشة تعديل بيانات اللغة
     */
    public function editLanguage($id)
    {
        $language = Language::findOrFail($id);
        return view('admin.languages.edit_language', compact('language'));
    }

    /**
     * تحديث بيانات اللغة في قاعدة البيانات
     */
    public function updateLanguage(Request $request)
    {
        $id = $request->id;
        $language = Language::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:languages,code,' . $language->id,
            'direction' => 'required|in:rtl,ltr',
        ];

        if ($request->flag_type === 'image') {
            $rules['flag_path'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        } else {
            $rules['flag_emoji'] = 'required|string|max:10';
        }

        $request->validate($rules, [
            'name.required' => 'يرجى إدخال اسم اللغة.',
            'code.required' => 'يرجى إدخال كود اللغة.',
            'code.unique' => 'كود اللغة هذا مستخدم بالفعل.',
            'direction.required' => 'يرجى تحديد اتجاه النص.',
            'flag_emoji.required' => 'يرجى اختيار العلم من قائمة الرموز التعبيرية.',
        ]);

        DB::beginTransaction();
        try {
            // معالجة تغيير وتأكيد علم اللغة الافتراضية لتجنب عدم وجود أي لغة افتراضية
            $isDefault = $request->has('is_default');
            $isActive = $request->has('is_active');

            if ($language->is_default && !$isDefault) {
                // إذا كانت هذه اللغة هي الافتراضية وحاول إلغاء تحديدها، نتحقق من وجود لغة افتراضية أخرى
                $otherDefault = Language::where('id', '!=', $language->id)->where('is_default', true)->exists();
                if (!$otherDefault) {
                    // إجبارها على البقاء كافتراضية ونشطة
                    $isDefault = true;
                    $isActive = true;
                }
            }

            if ($isDefault) {
                $isActive = true;
            }

            $language->name = $request->name;
            $language->code = strtolower($request->code);
            $language->direction = $request->direction;
            $language->is_default = $isDefault;
            $language->is_active = $isActive;

            // تحديث العلم بناء على النوع المختار
            if ($request->flag_type === 'emoji') {
                // حذف صورة العلم القديمة من الخادم إذا كانت ملفاً
                if ($language->flag_path && str_contains($language->flag_path, '.')) {
                    $oldPath = public_path('upload/flags/' . $language->flag_path);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }
                $language->flag_path = $request->flag_emoji;
            } else {
                if ($request->hasFile('flag_path')) {
                    // حذف صورة العلم القديمة من الخادم إذا كانت ملفاً
                    if ($language->flag_path && str_contains($language->flag_path, '.')) {
                        $oldPath = public_path('upload/flags/' . $language->flag_path);
                        if (File::exists($oldPath)) {
                            File::delete($oldPath);
                        }
                    }

                    $flag = $request->file('flag_path');
                    $flagName = date('YmdHis') . '_flag.' . $flag->getClientOriginalExtension();
                    $flag->move(public_path('upload/flags'), $flagName);
                    $language->flag_path = $flagName;
                }
            }

            $language->save();

            // إذا تم تعيين اللغة كافتراضية، نلغي صفة الافتراضية عن اللغات الأخرى
            if ($language->is_default) {
                Language::where('id', '!=', $language->id)->update(['is_default' => false]);
            }

            $this->clearTranslationCache($language->id);
            DB::commit();

            $notification = [
                'message' => 'تم تحديث بيانات اللغة بنجاح.',
                'alert-type' => 'success'
            ];

            return redirect()->route('all.languages')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with([
                'message' => 'حدث خطأ أثناء تعديل بيانات اللغة: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * حذف اللغة وحذف ترجماتها وعلمها
     */
    public function deleteLanguage($id)
    {
        $language = Language::findOrFail($id);

        // منع حذف اللغة الافتراضية للنظام
        if ($language->is_default) {
            $notification = [
                'message' => 'لا يمكن حذف اللغة الافتراضية للنظام. يرجى تعيين لغة أخرى كافتراضية أولاً.',
                'alert-type' => 'error'
            ];
            return redirect()->back()->with($notification);
        }

        DB::beginTransaction();
        try {
            // حذف ملف العلم محلياً إن وجد
            if ($language->flag_path && !filter_var($language->flag_path, FILTER_VALIDATE_URL)) {
                $flagPath = public_path('upload/flags/' . $language->flag_path);
                if (File::exists($flagPath)) {
                    File::delete($flagPath);
                }
            }

            // حذف سجل اللغة (سيؤدي لحذف الترجمات تلقائياً بسبب Cascade)
            $language->delete();

            $this->clearTranslationCache($id);
            DB::commit();

            $notification = [
                'message' => 'تم حذف اللغة والترجمات التابعة لها بنجاح.',
                'alert-type' => 'success'
            ];

            return redirect()->back()->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'message' => 'حدث خطأ أثناء حذف اللغة: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    // ==========================================
    // إدارة الترجمات (Translations CRUD)
    // ==========================================

    /**
     * عرض ترجمات لغة معينة
     */
    public function allTranslations($language_id)
    {
        $language = Language::findOrFail($language_id);
        $translations = Translation::where('language_id', $language_id)->latest()->get();
        $languages = Language::all();

        return view('admin.translations.all_translations', compact('language', 'translations', 'languages'));
    }

    /**
     * شاشة إضافة ترجمة جديدة للغة
     */
    public function addTranslation($language_id)
    {
        $language = Language::findOrFail($language_id);
        return view('admin.translations.add_translation', compact('language'));
    }

    /**
     * حفظ الترجمة الجديدة ومنع تكرار المفتاح لنفس اللغة
     */
    public function storeTranslation(Request $request)
    {
        $request->validate([
            'language_id' => 'required|exists:languages,id',
            'key' => 'required|string|max:255',
            'value' => 'required|string',
        ], [
            'key.required' => 'يرجى إدخال مفتاح الترجمة (Key).',
            'value.required' => 'يرجى إدخال النص المترجم (Value).',
        ]);

        $exists = Translation::where('language_id', $request->language_id)
            ->where('key', $request->key)
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with([
                'message' => 'هذا المفتاح (Key) مضاف بالفعل لهذه اللغة.',
                'alert-type' => 'error'
            ]);
        }

        Translation::create([
            'language_id' => $request->language_id,
            'key' => trim($request->key),
            'value' => $request->value,
        ]);

        $this->clearTranslationCache($request->language_id);

        $notification = [
            'message' => 'تم إضافة مفتاح الترجمة بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.translations', $request->language_id)->with($notification);
    }

    /**
     * شاشة تعديل قيمة ترجمة مفتاح
     */
    public function editTranslation($id)
    {
        $translation = Translation::with('language')->findOrFail($id);
        return view('admin.translations.edit_translation', compact('translation'));
    }

    /**
     * تحديث قيمة الترجمة والمفتاح
     */
    public function updateTranslation(Request $request)
    {
        $id = $request->id;
        $translation = Translation::findOrFail($id);

        $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required|string',
        ], [
            'key.required' => 'يرجى إدخال المفتاح.',
            'value.required' => 'يرجى إدخال النص المترجم.',
        ]);

        // التحقق من عدم تكرار المفتاح لغير الترجمة الحالية
        $exists = Translation::where('language_id', $translation->language_id)
            ->where('key', $request->key)
            ->where('id', '!=', $translation->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with([
                'message' => 'هذا المفتاح (Key) مضاف بالفعل لهذه اللغة لترجمة أخرى.',
                'alert-type' => 'error'
            ]);
        }

        $translation->key = trim($request->key);
        $translation->value = $request->value;
        $translation->save();

        $this->clearTranslationCache($translation->language_id);

        $notification = [
            'message' => 'تم تحديث الترجمة بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.translations', $translation->language_id)->with($notification);
    }

    /**
     * حذف ترجمة معينة
     */
    public function deleteTranslation($id)
    {
        $translation = Translation::findOrFail($id);
        $language_id = $translation->language_id;
        
        $translation->delete();

        $this->clearTranslationCache($language_id);

        $notification = [
            'message' => 'تم حذف مفتاح الترجمة بنجاح.',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.translations', $language_id)->with($notification);
    }

    /**
     * تغيير لغة العرض وتخزينها في الجلسة
     */
    public function switchLanguage($code)
    {
        $language = Language::where('code', $code)->where('is_active', 1)->first();
        if ($language) {
            session()->put('locale', $code);
            app()->setLocale($code);
            // إبطال كاش تفاصيل اللغة
            cache()->forget("lang_details_{$code}");
        }
        return redirect()->back();
    }

    /**
     * مسح التخزين المؤقت للترجمات
     */
    private function clearTranslationCache($languageId)
    {
        $language = Language::find($languageId);
        if ($language) {
            cache()->forget("translations_{$language->code}");
            cache()->forget("lang_details_{$language->code}");
        }
        cache()->forget("default_language");
        cache()->forget("active_languages");
    }
}
