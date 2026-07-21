<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        if ($request->has('ref')) {
            $code = $request->query('ref');
            $link = \App\Models\AffiliateLink::where('code', $code)->where('is_active', true)->first();
            if ($link) {
                session(['affiliate_ref' => $code]);
                
                // زيادة عداد النقرات مرة واحدة لكل جلسة
                $clickedKey = 'affiliate_clicked_' . $link->id;
                if (!session()->has($clickedKey)) {
                    $link->increment('clicks');
                    session([$clickedKey => true]);
                }
            }
        }
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'fname' => ['required', 'string', 'max:50'],
            'lname' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'first_name' => $request->fname,
            'last_name' => $request->lname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'password_hash' => md5($request->password),
            'status' => 1,
            'is_active' => 1,
        ]);

        // تسجيل تتبع الإحالة في حال وجود كود أفيليت بالجلسة
        if (session()->has('affiliate_ref')) {
            $code = session('affiliate_ref');
            $link = \App\Models\AffiliateLink::where('code', $code)->where('is_active', true)->first();
            if ($link) {
                $exists = \App\Models\AffiliateTracking::where('affiliate_link_id', $link->id)
                    ->where('registered_user_id', $user->id)
                    ->exists();
                if (!$exists) {
                    \App\Models\AffiliateTracking::create([
                        'affiliate_link_id' => $link->id,
                        'registered_user_id' => $user->id,
                        'ip_address' => $request->ip(),
                    ]);
                }
            }
            session()->forget('affiliate_ref');
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
