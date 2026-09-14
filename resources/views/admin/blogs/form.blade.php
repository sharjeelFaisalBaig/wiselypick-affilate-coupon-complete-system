@extends('admin.layouts.app')

@section('title', $blog->exists ? 'Edit Blog Post' : 'Add Blog Post')

@push('head')
    @vite(['resources/js/blog-editor.js', 'resources/js/slug-preview.js', 'resources/js/image-dimension-check.js', 'resources/js/blog-form.js'])
@endpush

@section('content')
    <div class="max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $blog->exists ? route('admin.blogs.update', $blog) : route('admin.blogs.store') }}"
              enctype="multipart/form-data" class="space-y-5">
            @csrf
            @if ($blog->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" value="{{ old('title', $blog->title) }}" required data-slug-source
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Blog Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $blog->slug) }}" placeholder="auto-generated from title if left blank" data-slug-preview
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">URL slug, e.g. "my-first-post". Live-previewed from the title above while left blank.</p>
                    @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Blog Category</label>
                    <select name="blog_category_id" data-select2-enable data-placeholder="— None —" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">— None —</option>
                        @foreach ($blogCategories as $category)
                            <option value="{{ $category->id }}" @selected(old('blog_category_id', $blog->blog_category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Slug Prefix</label>
                    <input type="text" name="route_prefix" value="{{ old('route_prefix', $blog->route_prefix) }}" placeholder="{{ \App\Models\Blog::DEFAULT_ROUTE_PREFIX }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">The path segment before the slug — e.g. "articles". Leave blank for the default "{{ \App\Models\Blog::DEFAULT_ROUTE_PREFIX }}".</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Suffix</label>
                    <input type="text" name="route_suffix" value="{{ old('route_suffix', $blog->route_suffix) }}" placeholder="optional"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">Optional trailing path segment after the slug.</p>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Excerpt</label>
                <textarea name="excerpt" rows="2"
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('excerpt', $blog->excerpt) }}</textarea>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Content</label>
                <div data-quill-editor="content" style="min-height: 300px;" class="bg-white"></div>
                <textarea name="content" data-content-field="content" class="hidden">{{ old('content', $blog->content) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">H2/H3 headings are automatically turned into the article's table of contents on save.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Featured Image</label>
                @if ($blog->featured_image)
                    <img data-live-preview src="{{ Storage::url($blog->featured_image) }}" alt="" width="120" height="68" class="mb-2 h-[68px] w-[120px] rounded border border-gray-200 object-cover">
                @endif
                <input type="file" name="featured_image" accept="image/*" data-required-width="670" data-required-height="300"
                       class="block text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                <p class="mt-1 text-xs text-gray-400">* Required dimensions: exactly 670x300px.</p>
                <p data-dimension-check-result class="mt-1 text-xs"></p>
                @error('featured_image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Author Name</label>
                    <input type="text" name="author_name" value="{{ old('author_name', $blog->author_name) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Published At</label>
                    <input type="datetime-local" name="published_at"
                           value="{{ old('published_at', optional($blog->published_at)->format('Y-m-d\TH:i')) }}"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="auto_link_related_blogs" value="1" data-auto-link-toggle
                       @checked(old('auto_link_related_blogs', $blog->id ? $blog->auto_link_related_blogs : true))
                       class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                <span class="text-sm text-gray-700">Related Blogs Auto-linking</span>
            </label>
            <p class="-mt-3 text-xs text-gray-400">When on, the sidebar automatically shows other posts in the same category, newest-updated first. Turn off to hand-pick them below.</p>

            <div data-related-blogs-picker @if (old('auto_link_related_blogs', $blog->id ? $blog->auto_link_related_blogs : true)) class="hidden" @endif>
                <label class="mb-1 block text-sm font-medium text-gray-700">Related Blogs</label>
                <select name="related_blog_ids[]" multiple data-select2-enable data-placeholder="Select related blogs..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @foreach ($otherBlogs as $other)
                        <option value="{{ $other->id }}" @selected(in_array($other->id, old('related_blog_ids', $selectedRelatedBlogs)))>{{ $other->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="mb-2 flex items-center justify-between">
                    <label class="block text-sm font-medium text-gray-700">FAQs (for FAQ schema markup)</label>
                    <button type="button" data-faq-add class="text-sm font-medium text-emerald-600 hover:text-emerald-700">+ Add FAQ</button>
                </div>
                <div data-faq-list data-faq-next-index="{{ count($blog->faqs ?? []) }}" class="space-y-2">
                    @foreach (($blog->faqs ?? []) as $i => $faq)
                        <div class="flex items-start gap-2 rounded-md border border-gray-200 p-3">
                            <div class="flex-1 space-y-2">
                                <input type="text" name="faqs[{{ $i }}][question]" value="{{ $faq['question'] ?? '' }}" placeholder="Question"
                                       class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <textarea name="faqs[{{ $i }}][answer]" rows="2" placeholder="Answer"
                                          class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ $faq['answer'] ?? '' }}</textarea>
                            </div>
                            <button type="button" data-faq-remove class="text-sm font-medium text-red-600 hover:text-red-700">Remove</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap gap-6">
                @foreach ([
                    'is_published' => 'Published',
                    'auto_compress_images' => 'Auto-compress Images',
                    'convert_to_webp' => 'Convert to WebP',
                    'enable_amp' => 'Enable AMP',
                ] as $field => $label)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="{{ $field }}" value="1"
                               @checked(old($field, $blog->id ? $blog->$field : in_array($field, ['auto_compress_images', 'convert_to_webp'])))
                               class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <fieldset class="rounded-md border border-gray-200 p-4">
                <legend class="px-1 text-sm font-medium text-gray-700">SEO</legend>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $blog->meta_title) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">OG Title</label>
                        <input type="text" name="og_title" value="{{ old('og_title', $blog->og_title) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-gray-500">Meta Description</label>
                        <input type="text" name="meta_description" value="{{ old('meta_description', $blog->meta_description) }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">Schema Type</label>
                        <select name="schema_type" data-select2-enable class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="BlogPosting" @selected(old('schema_type', $blog->schema_type ?? 'BlogPosting') === 'BlogPosting')>BlogPosting</option>
                            <option value="Article" @selected(old('schema_type', $blog->schema_type) === 'Article')>Article</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-6">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="robots_index" value="1" @checked(old('robots_index', $blog->id ? $blog->robots_index : true))
                                   class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                            <span class="text-sm text-gray-700">Robots: Index</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="robots_follow" value="1" @checked(old('robots_follow', $blog->id ? $blog->robots_follow : true))
                                   class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                            <span class="text-sm text-gray-700">Robots: Follow</span>
                        </label>
                    </div>
                </div>
            </fieldset>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    {{ $blog->exists ? 'Save Changes' : 'Create Blog Post' }}
                </button>
                <a href="{{ route('admin.blogs.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:-translate-y-0.5 hover:border-gray-400 hover:bg-gray-50 hover:shadow-sm active:translate-y-0">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
