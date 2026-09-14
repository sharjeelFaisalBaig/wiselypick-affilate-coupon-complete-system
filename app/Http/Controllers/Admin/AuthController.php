<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        if (! Auth::user()->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Your admin account has been deactivated.']);
        }

        $request->session()->regenerate();

        // No region picker on login anymore — land in whatever the default
        // region is (falling back to the first active/any region if one
        // hasn't been marked default), and let the topbar region-switcher
        // (already present on every admin page) change it from there.
        $region = Region::where('is_default', true)->first()
            ?? Region::where('is_active', true)->orderBy('sort_order')->first()
            ?? Region::orderBy('sort_order')->first();

        if ($region) {
            $request->session()->put('admin_active_region_id', $region->id);
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
