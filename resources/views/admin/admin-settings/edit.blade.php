@extends('admin.layouts.app')

@section('title', 'Admin Panel URL')

@section('content')
    <div class="max-w-xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="mb-4 text-sm text-gray-500">Superadmin only. Changes where this admin panel lives, e.g. changing this to "backoffice" moves the panel from /admin to /backoffice. Existing bookmarks and logged-in sessions pointing at the old URL will stop working — you'll be redirected to the new one immediately after saving.</p>

        <form method="POST" action="{{ route('admin.admin-settings.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Admin Panel Path</label>
                <div class="flex items-center gap-1">
                    <span class="text-sm text-gray-400">/</span>
                    <input type="text" name="admin_panel_path" value="{{ old('admin_panel_path', $settings->admin_panel_path ?: 'admin') }}" required
                           pattern="[a-z0-9-]+" maxlength="100"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <p class="mt-1 text-xs text-gray-400">Lowercase letters, numbers, and hyphens only.</p>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
@endsection
