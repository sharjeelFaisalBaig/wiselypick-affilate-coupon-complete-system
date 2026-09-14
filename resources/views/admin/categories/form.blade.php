@extends('admin.layouts.app')

@section('title', $category->exists ? 'Edit Category' : 'Add Category')

@push('head')
    @vite(['resources/js/image-dimension-check.js'])
@endpush

@section('content')
    <div class="max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
              enctype="multipart/form-data" class="space-y-5">
            @csrf
            @if ($category->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Category Name</label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" required maxlength="255"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">Optimal length: ~30 characters.</p>
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Category Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" placeholder="auto-generated from name if left blank"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">URI slug, e.g. "electronics".</p>
                    @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Category Image / Icon</label>
                @if ($category->icon_path)
                    <img data-live-preview src="{{ Storage::url($category->icon_path) }}" alt="" width="40" height="40" class="mb-2 h-10 w-10 rounded border border-gray-200 object-contain">
                @endif
                <input type="file" name="icon_image" accept="image/*"
                       class="block text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                <p class="mt-1 text-xs text-gray-400">* Optimal size: 64x64px.</p>
                @error('icon_image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                @if ($category->icon && ! $category->icon_path)
                    <p class="mt-1 text-xs text-gray-400">Currently using legacy icon value: "{{ $category->icon }}"</p>
                @endif
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="3" maxlength="500"
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description', $category->description) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Optimal length: ~150 characters.</p>
            </div>

            <fieldset class="rounded-md border border-gray-200 p-4">
                <legend class="px-1 text-sm font-medium text-gray-700">SEO</legend>
                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">Meta Description</label>
                        <input type="text" name="meta_description" value="{{ old('meta_description', $category->meta_description) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                </div>
            </fieldset>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))
                       class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                <span class="text-sm text-gray-700">Active (visible on storefront)</span>
            </label>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    {{ $category->exists ? 'Save Changes' : 'Create Category' }}
                </button>
                <a href="{{ route('admin.categories.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:-translate-y-0.5 hover:border-gray-400 hover:bg-gray-50 hover:shadow-sm active:translate-y-0">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
