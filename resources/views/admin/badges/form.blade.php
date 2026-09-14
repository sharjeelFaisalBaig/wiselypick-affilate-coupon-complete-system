@extends('admin.layouts.app')

@section('title', $badge->exists ? 'Edit Badge' : 'Add Badge')

@push('head')
    @vite(['resources/js/badge-form.js'])
@endpush

@section('content')
    <div class="max-w-xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $badge->exists ? route('admin.badges.update', $badge) : route('admin.badges.store') }}"
              class="space-y-5">
            @csrf
            @if ($badge->exists) @method('PUT') @endif

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Badge Name</label>
                <input type="text" name="name" value="{{ old('name', $badge->name) }}" required placeholder="e.g. Verified" maxlength="255"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <p class="mt-1 text-xs text-gray-400">Optimal length: ~15 characters so it fits on one line as a pill.</p>
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="use_custom_color" value="1" data-toggle-color
                           @checked(old('use_custom_color', (bool) $badge->color))
                           class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Use a custom color</span>
                </label>
                <div class="mt-2 flex items-center gap-2" @if (! old('use_custom_color', (bool) $badge->color)) style="display:none" @endif data-color-field>
                    <input type="color" name="color" value="{{ old('color', $badge->color ?: '#10b981') }}"
                           class="h-9 w-16 rounded-md border-gray-300 p-1 shadow-sm">
                    <span class="text-xs text-gray-400">Overrides the default pill color for this feature.</span>
                </div>
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $badge->id ? $badge->is_active : true))
                       class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                <span class="text-sm text-gray-700">Active</span>
            </label>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    {{ $badge->exists ? 'Save Changes' : 'Create Badge' }}
                </button>
                <a href="{{ route('admin.badges.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:-translate-y-0.5 hover:border-gray-400 hover:bg-gray-50 hover:shadow-sm active:translate-y-0">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
