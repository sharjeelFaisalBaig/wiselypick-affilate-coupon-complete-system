@extends('public.layouts.app')

@push('schema')
    <script type="application/ld+json">
    {!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => $blog->schema_type,
        'headline' => $blog->title,
        'datePublished' => optional($blog->published_at)->toIso8601String(),
        'author' => $blog->author_name ? ['@type' => 'Person', 'name' => $blog->author_name] : null,
        'mainEntityOfPage' => url()->current(),
    ]), JSON_UNESCAPED_SLASHES) !!}
    </script>
    @if (!empty($blog->faqs))
        <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($blog->faqs)->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
            ])->all(),
        ], JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif
@endpush

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <article class="grid grid-cols-1 gap-8 lg:grid-cols-[220px_1fr_260px]">
            <aside class="hidden lg:block">
                <div class="sticky top-8">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Share</p>
                    <div class="mt-2 flex flex-col gap-2 text-sm">
                        <a target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($blog->title) }}" class="text-gray-500 hover:text-emerald-600">Share on X</a>
                        <a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" class="text-gray-500 hover:text-emerald-600">Share on Facebook</a>
                        <a target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}" class="text-gray-500 hover:text-emerald-600">Share on LinkedIn</a>
                    </div>

                    @if (!empty($blog->toc))
                        <p class="mt-6 text-xs font-semibold uppercase tracking-wide text-gray-400">On this page</p>
                        <ul class="mt-2 space-y-2 text-sm">
                            @foreach ($blog->toc as $item)
                                <li class="{{ ($item['level'] ?? 'h2') === 'h3' ? 'pl-3' : '' }}"><a href="#{{ $item['anchor'] }}" class="text-gray-500 hover:text-emerald-600">{{ $item['text'] }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </aside>

            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">{{ $blog->blogCategory?->name }}</p>
                <h1 class="mt-1 text-3xl font-bold text-gray-900">{{ $blog->title }}</h1>
                <p class="mt-2 text-sm text-gray-400">
                    @if ($blog->author_name) By {{ $blog->author_name }} &middot; @endif
                    {{ optional($blog->published_at)->format('F j, Y') }} &middot; {{ $blog->reading_time_minutes }} min read
                </p>

                @if ($blog->featured_image)
                    <img src="{{ Storage::url($blog->featured_image) }}" alt="{{ $blog->title }}" width="800" height="450" class="mt-6 h-auto w-full rounded-xl object-cover">
                @else
                    <div class="mt-6">
                        @include('public.partials.placeholder-image', ['class' => 'h-[300px] w-full rounded-xl', 'iconClass' => 'h-14 w-14'])
                    </div>
                @endif

                <div class="prose prose-emerald mt-6 max-w-none">
                    {!! $blog->content !!}
                </div>

                @if (!empty($blog->faqs))
                    <div class="mt-10">
                        <h2 class="text-lg font-bold text-gray-900">Frequently Asked Questions</h2>
                        <div class="mt-4 space-y-3">
                            @foreach ($blog->faqs as $faq)
                                <details class="rounded-md border border-gray-200 p-3">
                                    <summary class="cursor-pointer text-sm font-medium text-gray-900">{{ $faq['question'] }}</summary>
                                    <p class="mt-2 text-sm text-gray-600">{{ $faq['answer'] }}</p>
                                </details>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <aside>
                @if ($relatedBlogs->isNotEmpty())
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Related Posts</p>
                    <div class="mt-3 space-y-3">
                        @foreach ($relatedBlogs as $related)
                            <a href="{{ $related->urlFor($region) }}" class="flex items-center gap-2 rounded-lg border border-gray-200 p-2 hover:-translate-y-0.5 hover:bg-gray-50 hover:shadow-sm">
                                @if ($related->featured_image)
                                    <img src="{{ Storage::url($related->featured_image) }}" alt="{{ $related->title }}" width="48" height="48" loading="lazy" class="h-12 w-12 shrink-0 rounded object-cover">
                                @else
                                    @include('public.partials.placeholder-image', ['class' => 'h-12 w-12 shrink-0 rounded'])
                                @endif
                                <div>
                                    <span class="line-clamp-2 text-sm text-gray-900">{{ $related->title }}</span>
                                    <span class="text-xs text-gray-400">{{ optional($related->published_at)->format('M j, Y') }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </aside>
        </article>
    </div>
@endsection
