@extends('admin.layouts.app')

@section('title', $user->exists ? 'Edit User' : 'Add User')

@section('content')
    <div class="max-w-xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
              class="space-y-5">
            @csrf
            @if ($user->exists) @method('PUT') @endif

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="255"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">
                    {{ $user->exists ? 'Reset Password' : 'Password' }}
                </label>
                <input type="password" name="password" autocomplete="new-password"
                       placeholder="{{ $user->exists ? 'Leave blank to keep current password' : '' }}"
                       {{ $user->exists ? '' : 'required' }}
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <p class="mt-1 text-xs text-gray-400">Minimum 8 characters.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Role</label>
                <select name="role" required data-select2-enable class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="manager" @selected(old('role', $user->role ?? 'manager') === 'manager')>Manager — full access except Users</option>
                    <option value="superadmin" @selected(old('role', $user->role) === 'superadmin')>Superadmin — full access including Users</option>
                    <option value="store_coupon_manager" @selected(old('role', $user->role) === 'store_coupon_manager')>Store &amp; Coupon Manager — Stores and Coupons only</option>
                    <option value="blog_manager" @selected(old('role', $user->role) === 'blog_manager')>Blog Manager — Blogs only</option>
                </select>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    {{ $user->exists ? 'Save Changes' : 'Create User' }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:-translate-y-0.5 hover:border-gray-400 hover:bg-gray-50 hover:shadow-sm active:translate-y-0">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
