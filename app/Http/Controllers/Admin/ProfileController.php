<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Self-service only — deliberately never accepts a `role` field, even from
 * a Manager who tries to submit one, since role changes are Superadmin-only
 * (Admin\UserController) territory.
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Only a Superadmin may change their own email or password from
            // this screen — every other role can only get either changed by
            // a Superadmin via Admin\UserController's edit form. Both
            // validated (not just hidden in the form) so a non-Superadmin
            // can't just POST the fields directly.
            'email' => $user->isSuperadmin()
                ? ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)]
                : ['prohibited'],
            'password' => $user->isSuperadmin() ? ['nullable', Password::min(8)] : ['prohibited'],
        ]);

        if (! $user->isSuperadmin()) {
            unset($data['email']);
        }

        if ($user->isSuperadmin() && filled($data['password'] ?? null)) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.profile.edit')->with('status', 'Profile updated.');
    }
}
