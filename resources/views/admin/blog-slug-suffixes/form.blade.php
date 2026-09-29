@extends('admin.layouts.app')

@section('title', $blogSlugSuffix->exists ? 'Edit Slug Suffix' : 'Add Slug Suffix')

@section('content')
    <div class="max-w-xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $blogSlugSuffix->exists ? route('admin.blog-slug-suffixes.update', $blogSlugSuffix) : route('admin.blog-slug-suffixes.store') }}"
              class="space-y-5">
            @csrf
            @if ($blogSlugSuffix->exists) @method('PUT') @endif

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Suffix</label>
                <input type="text" name="value" value="{{ old('value', $blogSlugSuffix->value) }}" required placeholder="e.g. guide" maxlength="255"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <p class="mt-1 text-xs text-gray-400">The path segment after a post's slug, e.g. "guide" for /articles/{slug}/guide. Lowercase letters, numbers, hyphens and slashes only.</p>
                @error('value') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $blogSlugSuffix->id ? $blogSlugSuffix->is_active : true))
                       class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                <span class="text-sm text-gray-700">Active</span>
            </label>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    {{ $blogSlugSuffix->exists ? 'Save Changes' : 'Create Slug Suffix' }}
                </button>
                <a href="{{ route('admin.blog-slug-suffixes.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:-translate-y-0.5 hover:border-gray-400 hover:bg-gray-50 hover:shadow-sm active:translate-y-0">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
