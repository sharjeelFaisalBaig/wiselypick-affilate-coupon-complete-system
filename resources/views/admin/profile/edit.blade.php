@extends('admin.layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="max-w-xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

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
                <label class="mb-1 block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" name="password" autocomplete="new-password" placeholder="Leave blank to keep current password"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <p class="mt-1 text-xs text-gray-400">Minimum 8 characters.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Role</label>
                <p class="text-sm text-gray-500">{{ ucfirst($user->role) }} <span class="text-xs text-gray-400">(only a Superadmin can change this)</span></p>
            </div>

            <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                Save Changes
            </button>
        </form>
    </div>
@endsection
