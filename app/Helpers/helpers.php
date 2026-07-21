<?php

if (!function_exists('get_default_locale')) {
    /**
     * Get the default language code (cached).
     *
     * @return string
     */
    function get_default_locale()
    {
        $defaultLangData = cache()->rememberForever('default_language', function() {
            $lang = \App\Models\Language::where('is_default', true)->first() 
                ?? \App\Models\Language::where('is_active', true)->first()
                ?? \App\Models\Language::first();
            return $lang ? [
                'id' => $lang->id,
                'code' => $lang->code,
                'direction' => $lang->direction,
                'flag_path' => $lang->flag_path,
                'name' => $lang->name
            ] : null;
        });
        return $defaultLangData ? $defaultLangData['code'] : config('app.locale', 'ar');
    }
}

if (!function_exists('__t')) {
    /**
     * Translate key using translations from database (cached).
     *
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    function __t($key, $replace = [], $locale = null)
    {
        if (!$locale) {
            $locale = session()->get('locale') ?? get_default_locale();
        }

        $cacheKey = "translations_{$locale}";

        $translations = cache()->rememberForever($cacheKey, function () use ($locale) {
            $lang = \App\Models\Language::where('code', $locale)->first();
            if (!$lang) {
                $lang = \App\Models\Language::where('is_default', true)->first()
                    ?? \App\Models\Language::first();
            }

            if ($lang) {
                return \App\Models\Translation::where('language_id', $lang->id)
                    ->pluck('value', 'key')
                    ->toArray();
            }

            return [];
        });

        $value = isset($translations[$key]) ? $translations[$key] : $key;

        if (!empty($replace)) {
            foreach ($replace as $k => $v) {
                $value = str_replace(':' . $k, $v, $value);
            }
        }

        return $value;
    }
}

if (!function_exists('current_language')) {
    /**
     * Get the active language object (cached).
     *
     * @return \App\Models\Language|null
     */
    function current_language()
    {
        $locale = session()->get('locale') ?? get_default_locale();
        
        $langData = cache()->rememberForever("lang_details_{$locale}", function () use ($locale) {
            $lang = \App\Models\Language::where('code', $locale)->first()
                ?? \App\Models\Language::where('is_default', true)->first()
                ?? \App\Models\Language::first();
            return $lang ? [
                'id' => $lang->id,
                'code' => $lang->code,
                'direction' => $lang->direction,
                'flag_path' => $lang->flag_path,
                'name' => $lang->name
            ] : null;
        });

        return $langData ? (object) $langData : null;
    }
}
