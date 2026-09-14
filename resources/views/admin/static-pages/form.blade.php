@extends('admin.layouts.app')

@section('title', $page->exists ? 'Edit Page' : 'Add Page')

@section('content')
    <div class="max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $page->exists ? route('admin.static-pages.update', $page) : route('admin.static-pages.store') }}"
              class="space-y-5">
            @csrf
            @if ($page->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" value="{{ old('title', $page->title) }}" required
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" placeholder="auto-generated from title if left blank"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">URI path, e.g. "terms-of-use" for /terms-of-use.</p>
                    @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Content (HTML)</label>
                <textarea name="content" rows="12"
                          class="block w-full rounded-md border-gray-300 font-mono text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('content', $page->content) }}</textarea>
            </div>

            <fieldset class="rounded-md border border-gray-200 p-4">
                <legend class="px-1 text-sm font-medium text-gray-700">SEO</legend>
                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">Meta Description</label>
                        <input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">OG Title</label>
                        <input type="text" name="og_title" value="{{ old('og_title', $page->og_title) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">OG Description</label>
                        <input type="text" name="og_description" value="{{ old('og_description', $page->og_description) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <details>
                        <summary class="cursor-pointer text-xs font-medium text-gray-500">Schema &amp; Script Injection</summary>
                        <div class="mt-3 space-y-3">
                            <textarea name="schema_script" rows="2" placeholder="SEO Schema / custom tracking scripts"
                                      class="block w-full rounded-md border-gray-300 font-mono text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('schema_script', $page->schema_script) }}</textarea>
                            @foreach ([
                                'head_start_script' => 'Start of <head>',
                                'head_end_script' => 'End of <head>',
                                'body_start_script' => 'Start of <body>',
                                'body_end_script' => 'End of <body>',
                            ] as $field => $label)
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-500">{{ $label }}</label>
                                    <textarea name="{{ $field }}" rows="2"
                                              class="block w-full rounded-md border-gray-300 font-mono text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old($field, $page->$field) }}</textarea>
                                </div>
                            @endforeach
                        </div>
                    </details>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="robots_index" value="1" @checked(old('robots_index', $page->id ? $page->robots_index : true))
                                   class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                            <span class="text-sm text-gray-700">Robots: Index</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="robots_follow" value="1" @checked(old('robots_follow', $page->id ? $page->robots_follow : true))
                                   class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                            <span class="text-sm text-gray-700">Robots: Follow</span>
                        </label>
                    </div>
                </div>
            </fieldset>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $page->id ? $page->is_active : true))
                       class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                <span class="text-sm text-gray-700">Active</span>
            </label>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    {{ $page->exists ? 'Save Changes' : 'Create Page' }}
                </button>
                <a href="{{ route('admin.static-pages.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:-translate-y-0.5 hover:border-gray-400 hover:bg-gray-50 hover:shadow-sm active:translate-y-0">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
