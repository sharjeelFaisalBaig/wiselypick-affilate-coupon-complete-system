@extends('admin.layouts.app')

@section('title', $region->exists ? 'Edit Region' : 'Add Region')

@push('head')
    @vite(['resources/js/image-dimension-check.js'])
@endpush

@section('content')
    <div class="max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $region->exists ? route('admin.regions.update', $region) : route('admin.regions.store') }}"
              enctype="multipart/form-data" class="space-y-5">
            @csrf
            @if ($region->exists) @method('PUT') @endif

            @unless ($region->exists)
                <p class="rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-700">New regions are created Disabled — enable it from the list once it's ready to publish.</p>
            @endunless

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Prefix Slug</label>
                    <input type="text" name="code" value="{{ old('code', $region->code) }}" required maxlength="4" placeholder="e.g. us"
                           class="block w-full rounded-md border-gray-300 lowercase shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">URL prefix, e.g. /us, /au. 2-4 letters.</p>
                    @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Region Title</label>
                    <input type="text" name="name" value="{{ old('name', $region->name) }}" required placeholder="e.g. USA"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Favicon</label>
                @if ($region->favicon_path)
                    <img data-live-preview src="{{ Storage::url($region->favicon_path) }}" alt="" width="32" height="32" class="mb-2 h-8 w-8 rounded border border-gray-200 object-contain">
                @endif
                <input type="file" name="favicon" accept="image/*,.ico"
                       class="block text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                <p class="mt-1 text-xs text-gray-400">* Optimal size: 32x32px, .ico or .png.</p>
                @error('favicon') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Flag</label>
                @if ($region->flag_path)
                    <img data-live-preview src="{{ Storage::url($region->flag_path) }}" alt="" width="24" height="16" class="mb-2 h-4 w-6 rounded-sm border border-gray-200 object-cover">
                @endif
                <input type="file" name="flag" accept="image/*" data-preview-class="mb-2 hidden h-4 w-6 rounded-sm border border-gray-200 object-cover"
                       class="block text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                <p class="mt-1 text-xs text-gray-400">* Optimal size: 24x16px (3:2). Shown next to this region in the admin panel's region switcher.</p>
                @error('flag') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @if ($region->exists)
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Sort Order</label>
                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $region->sort_order) }}"
                           class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            @endif

            <fieldset class="rounded-md border border-gray-200 p-4 space-y-3">
                <legend class="px-1 text-sm font-medium text-gray-700">Global Region Scripts</legend>
                <p class="text-xs text-gray-400">Injected on every page in this region — tracking pixels, analytics, etc.</p>
                @foreach ([
                    'head_start_script' => 'Start of <head>',
                    'head_end_script' => 'End of <head>',
                    'body_start_script' => 'Start of <body>',
                    'body_end_script' => 'End of <body>',
                ] as $field => $label)
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">{{ $label }}</label>
                        <textarea name="{{ $field }}" rows="2"
                                  class="block w-full rounded-md border-gray-300 font-mono text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old($field, $region->$field) }}</textarea>
                    </div>
                @endforeach
            </fieldset>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Canonical Base URL</label>
                <input type="url" name="canonical_base_url" value="{{ old('canonical_base_url', $region->canonical_base_url) }}" placeholder="https://www.example.com"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <p class="mt-1 text-xs text-gray-400">Used to build every page's canonical URL (this + the page's path). Leave blank to use the resolved request host.</p>
                @error('canonical_base_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @if ($region->exists)
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $region->is_active)) @disabled($region->is_default)
                           class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Enabled</span>
                    @if ($region->is_default)
                        <span class="text-xs text-gray-400">(the default region can't be disabled)</span>
                    @endif
                </label>
            @endif

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    {{ $region->exists ? 'Save Changes' : 'Create Region' }}
                </button>
                <a href="{{ route('admin.regions.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:-translate-y-0.5 hover:border-gray-400 hover:bg-gray-50 hover:shadow-sm active:translate-y-0">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
