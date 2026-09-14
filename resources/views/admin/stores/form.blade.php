@extends('admin.layouts.app')

@section('title', $store->exists ? 'Edit Store' : 'Add Store')

@push('head')
    @vite(['resources/js/blog-editor.js', 'resources/js/slug-preview.js', 'resources/js/image-dimension-check.js'])
@endpush

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $store->exists ? route('admin.stores.update', $store) : route('admin.stores.store') }}"
              enctype="multipart/form-data" class="space-y-5">
            @csrf
            @if ($store->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Store/Brand Name</label>
                    <input type="text" name="name" value="{{ old('name', $store->name) }}" required maxlength="255" data-slug-source
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">Optimal length: ~40 characters. Shown exactly as typed — no prefix/suffix added.</p>
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Store Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $store->slug) }}" placeholder="auto-generated from name if left blank" data-slug-preview
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">URI slug, e.g. "amazon". Live-previewed from the name above while left blank.</p>
                    @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Slug Prefix</label>
                    <input type="text" name="route_prefix" value="{{ old('route_prefix', $store->route_prefix) }}" placeholder="{{ \App\Models\Store::DEFAULT_ROUTE_PREFIX }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">The path segment before the slug — e.g. "promotions" for /promotions/{{ $store->slug ?: 'slug' }}. Leave blank for the default "{{ \App\Models\Store::DEFAULT_ROUTE_PREFIX }}".</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Suffix</label>
                    <input type="text" name="route_suffix" value="{{ old('route_suffix', $store->route_suffix) }}" placeholder="e.g. best-deals"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">Optional trailing path segment after the slug.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Category</label>
                    <select name="category_id" data-select2-enable data-placeholder="— None —" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">— None —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $store->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Heading Suffix</label>
                    <select name="store_suffix_id" data-select2-enable data-placeholder="— None —" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">— None —</option>
                        @foreach ($storeSuffixes as $storeSuffix)
                            <option value="{{ $storeSuffix->id }}" @selected(old('store_suffix_id', $store->store_suffix_id) == $storeSuffix->id)>{{ $storeSuffix->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Appended after the store name in the store page heading. Manage options under Taxonomies → Store Suffixes.</p>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">About</label>
                <div data-quill-editor="about" style="min-height: 180px;" class="bg-white"></div>
                <textarea name="about" data-content-field="about" class="hidden">{{ old('about', $store->about) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Longer, structured "About {{ $store->name ?: 'Store' }}" content improves SEO — use headings to break it up.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Store Logo</label>
                @if ($store->logo_path)
                    <img data-live-preview src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}" width="64" height="64" class="mb-2 h-16 w-16 rounded border border-gray-200 object-contain">
                @endif
                <input type="file" name="logo" accept="image/*" data-required-width="200" data-required-height="200"
                       class="block text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                <p class="mt-1 text-xs text-gray-400">* Required dimensions: exactly 200x200px. JPG, PNG or WEBP, up to 1MB.</p>
                <p data-dimension-check-result class="mt-1 text-xs"></p>
                @error('logo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Affiliate URL</label>
                    <input type="url" name="affiliate_url" value="{{ old('affiliate_url', $store->affiliate_url) }}" required
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">Used for this store's coupon/deal redirect links.</p>
                    @error('affiliate_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Expiry Date</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date', optional($store->expiry_date)->format('Y-m-d')) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">Optional. After this date, this store and its offers stop showing anywhere on the frontend.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Star Rating (0–5)</label>
                    <input type="number" step="0.1" min="0" max="5" name="star_rating" value="{{ old('star_rating', $store->star_rating ?? 4.5) }}" required
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Reviews Count</label>
                    <input type="number" min="0" name="reviews_count" value="{{ old('reviews_count', $store->reviews_count ?? 0) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Store State</label>
                    <select name="status" class="block rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @php $currentStatus = old('status', $store->id ? ($store->is_active ? 'active' : 'pending') : 'active'); @endphp
                        <option value="active" @selected($currentStatus === 'active')>Active</option>
                        <option value="pending" @selected($currentStatus === 'pending')>Pending</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-400">Only Active stores show on the frontend. A Pending store also lists on the Featured &amp; Popular screen's Pending Stores tab.</p>
                </div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $store->is_featured))
                           class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Featured Store</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_popular" value="1" @checked(old('is_popular', $store->is_popular))
                           class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Popular Store</span>
                </label>
            </div>
            <p class="-mt-3 text-xs text-gray-400">Featured/Popular are curation tags for the Featured &amp; Popular screen (a store can carry either or both) — they don't affect frontend visibility. Pending is driven solely by Store State above now, not a separate tag.</p>

            <fieldset class="rounded-md border border-gray-200 p-4">
                <legend class="px-1 text-sm font-medium text-gray-700">SEO</legend>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $store->meta_title) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">Meta Keywords</label>
                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $store->meta_keywords) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-500">Meta Description</label>
                        <input type="text" name="meta_description" value="{{ old('meta_description', $store->meta_description) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">OG Title</label>
                        <input type="text" name="og_title" value="{{ old('og_title', $store->og_title) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">OG Description</label>
                        <input type="text" name="og_description" value="{{ old('og_description', $store->og_description) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="robots_index" value="1" @checked(old('robots_index', $store->id ? $store->robots_index : true))
                               class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                        <span class="text-sm text-gray-700">Robots: Index</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="robots_follow" value="1" @checked(old('robots_follow', $store->id ? $store->robots_follow : true))
                               class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                        <span class="text-sm text-gray-700">Robots: Follow</span>
                    </label>
                </div>
            </fieldset>

            <fieldset class="rounded-md border border-gray-200 p-4 space-y-3">
                <legend class="px-1 text-sm font-medium text-gray-700">Store Script Injection</legend>
                @foreach ([
                    'head_start_script' => 'Start of <head>',
                    'head_end_script' => 'End of <head>',
                    'body_start_script' => 'Start of <body>',
                    'body_end_script' => 'End of <body>',
                ] as $field => $label)
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">{{ $label }}</label>
                        <textarea name="{{ $field }}" rows="2"
                                  class="block w-full rounded-md border-gray-300 font-mono text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old($field, $store->$field) }}</textarea>
                    </div>
                @endforeach
            </fieldset>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    {{ $store->exists ? 'Save Changes' : 'Create Store' }}
                </button>
                <a href="{{ route('admin.stores.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:-translate-y-0.5 hover:border-gray-400 hover:bg-gray-50 hover:shadow-sm active:translate-y-0">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
