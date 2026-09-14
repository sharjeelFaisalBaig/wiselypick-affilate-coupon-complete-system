@extends('admin.layouts.app')

@section('title', $pageLabel.' Settings')

@section('content')
    <p class="mb-4 text-sm text-gray-500">Heading, intro copy, and SEO fields for this page only.</p>

    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.page-settings.update', $pageKey) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">URL Slug</label>
                <div class="flex items-center gap-1">
                    <span class="text-sm text-gray-400">/{{ $activeRegion->code }}/</span>
                    <input type="text" name="slug" value="{{ old('slug', $settings->slug ?? \App\Models\PageSetting::DEFAULT_SLUGS[$pageKey]) }}"
                           placeholder="e.g. coupons/usa-promotions"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <p class="mt-1 text-xs text-gray-400">Lowercase letters, numbers, hyphens, and "/" for multi-segment paths (e.g. "promos/blogs") — leave blank to make this page the region's root ("/"). Only one page per region can be blank at a time.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Heading (H1)</label>
                <input type="text" name="heading" value="{{ old('heading', $settings->heading) }}"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Subheading / Intro Copy</label>
                <textarea name="subheading" rows="2"
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('subheading', $settings->subheading) }}</textarea>
            </div>

            @if ($pageKey === 'home')
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Hero Badge Text</label>
                    <input type="text" name="hero_badge_text" value="{{ old('hero_badge_text', $settings->hero_badge_text) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">Small pill shown above the hero heading, e.g. "Verified daily by our editors". Leave blank to hide it.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Hero Search Placeholder</label>
                        <input type="text" name="hero_search_placeholder" value="{{ old('hero_search_placeholder', $settings->hero_search_placeholder) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Hero Search Button Text</label>
                        <input type="text" name="hero_search_button_text" value="{{ old('hero_search_button_text', $settings->hero_search_button_text) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $settings->meta_title) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Meta Description</label>
                    <input type="text" name="meta_description" value="{{ old('meta_description', $settings->meta_description) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">OG Title</label>
                    <input type="text" name="og_title" value="{{ old('og_title', $settings->og_title) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">OG Description</label>
                    <input type="text" name="og_description" value="{{ old('og_description', $settings->og_description) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <div class="flex gap-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="robots_index" value="1" @checked(old('robots_index', $settings->exists ? $settings->robots_index : true))
                           class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Robots: Index</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="robots_follow" value="1" @checked(old('robots_follow', $settings->exists ? $settings->robots_follow : true))
                           class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Robots: Follow</span>
                </label>
            </div>

            <details class="rounded-md border border-gray-200 p-3">
                <summary class="cursor-pointer text-xs font-medium text-gray-500">Schema &amp; Script Injection</summary>
                <div class="mt-3 space-y-3">
                    <textarea name="schema_script" rows="2" placeholder="SEO Schema / custom tracking scripts"
                              class="block w-full rounded-md border-gray-300 font-mono text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('schema_script', $settings->schema_script) }}</textarea>
                    @foreach ([
                        'head_start_script' => 'Start of <head>',
                        'head_end_script' => 'End of <head>',
                        'body_start_script' => 'Start of <body>',
                        'body_end_script' => 'End of <body>',
                    ] as $field => $label)
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500">{{ $label }}</label>
                            <textarea name="{{ $field }}" rows="2"
                                      class="block w-full rounded-md border-gray-300 font-mono text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old($field, $settings->$field) }}</textarea>
                        </div>
                    @endforeach
                </div>
            </details>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $settings->exists ? $settings->is_active : true))
                       class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                <span class="text-sm text-gray-700">Published (draft renders a 404)</span>
            </label>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    Save
                </button>
                <a href="{{ route('admin.pages-overview.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">Back to Pages</a>
            </div>
        </form>
    </div>
@endsection
