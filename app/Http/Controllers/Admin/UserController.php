<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Users are global (not region-scoped) — any admin account can already
 * switch between regions freely via the session-based region selector, so
 * there's no region ownership to guard here, unlike every other admin
 * resource in this app.
 */
class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, requirePassword: true);
        $data['password'] = bcrypt($data['password']);
        $data['is_active'] = true;

        User::create($data);

        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, requirePassword: false, user: $user);

        if (filled($data['password'] ?? null)) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 403, 'You cannot delete your own account.');

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'User deleted.');
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 403, 'You cannot suspend your own account.');

        $user->update(['is_active' => false]);

        return redirect()->route('admin.users.index')->with('status', 'User suspended.');
    }

    public function reactivate(User $user): RedirectResponse
    {
        $user->update(['is_active' => true]);

        return redirect()->route('admin.users.index')->with('status', 'User re-enabled.');
    }

    private function validated(Request $request, bool $requirePassword, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => [$requirePassword ? 'required' : 'nullable', Password::min(8)],
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        return $data;
    }
}
