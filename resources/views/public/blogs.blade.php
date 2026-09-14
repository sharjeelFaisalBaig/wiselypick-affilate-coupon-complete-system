@extends('public.layouts.app')

@section('content')
    {{-- Big image-overlay featured-post hero, matching the blog inspiration's
         treatment, instead of the generic dark-surface page-header band used
         by the coupons-vertical listing pages. --}}
    @if ($featured)
        <a data-reveal href="{{ $featured->urlFor($region) }}" class="group relative block h-[420px] w-full overflow-hidden sm:h-[480px]">
            @if ($featured->featured_image)
                <img src="{{ Storage::url($featured->featured_image) }}" alt="{{ $featured->title }}" width="1440" height="480" loading="eager" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
            @else
                @include('public.partials.placeholder-image', ['class' => 'h-full w-full', 'iconClass' => 'h-12 w-12'])
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-[var(--color-dark-surface)] via-[var(--color-dark-surface)]/40 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 mx-auto max-w-7xl px-4 pb-8 sm:px-6 lg:px-8">
                <span class="inline-flex w-fit items-center rounded-full bg-emerald-500 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-white">{{ $featured->blogCategory?->name }}</span>
                <h1 class="mt-3 max-w-2xl font-display text-2xl font-extrabold text-white sm:text-4xl">{{ $featured->title }}</h1>
                <p class="mt-2 max-w-xl text-sm text-gray-200">{{ $featured->excerpt }}</p>
                <p class="mt-3 flex items-center gap-1 text-xs text-gray-300">
                    {{ optional($featured->published_at)->format('M j, Y') }} &middot; {{ $featured->reading_time_minutes }} min read
                </p>
            </div>
        </a>
    @else
        @include('public.partials.page-header', ['heading' => $heading, 'subheading' => $subheading])
    @endif

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-gray-900">{{ $heading }}</h2>
            <form method="GET" class="flex gap-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search articles..." autocomplete="off"
                       data-autosuggest-endpoint="{{ route('public.suggest.blogs', $region->code) }}"
                       class="rounded-full border border-gray-300 px-4 py-2 text-sm transition-shadow duration-200 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/15">
            </form>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ \App\Models\PageSetting::urlFor($region, 'blogs') }}" class="rounded-full border px-3 py-1.5 text-xs font-medium transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm {{ !request('category') ? 'border-transparent bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-sm' : 'border-gray-300 text-gray-600 hover:border-emerald-300' }}">All</a>
            @foreach ($categories as $category)
                <a href="{{ \App\Models\PageSetting::urlFor($region, 'blogs') }}?category={{ $category->slug }}"
                   class="rounded-full border px-3 py-1.5 text-xs font-medium transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm {{ request('category') === $category->slug ? 'border-transparent bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-sm' : 'border-gray-300 text-gray-600 hover:border-emerald-300' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <div data-reveal-group class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($blogs as $blog)
                <a data-reveal href="{{ $blog->urlFor($region) }}" class="card-lift group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm hover:border-transparent">
                    <div class="h-[180px] w-full overflow-hidden">
                        @if ($blog->featured_image)
                            <img src="{{ Storage::url($blog->featured_image) }}" alt="{{ $blog->title }}" width="400" height="220" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                        @else
                            @include('public.partials.placeholder-image', ['class' => 'h-full w-full', 'iconClass' => 'h-10 w-10'])
                        @endif
                    </div>
                    <div class="p-4">
                        <span class="inline-flex w-fit items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ $blog->blogCategory?->name }}</span>
                        <h2 class="mt-2 font-bold text-gray-900 group-hover:text-emerald-700">{{ $blog->title }}</h2>
                        <div class="mt-3 flex items-center justify-between">
                            <p class="text-xs text-gray-400">{{ optional($blog->published_at)->format('M j, Y') }} &middot; {{ $blog->reading_time_minutes }} min read</p>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 text-emerald-500 transition-transform duration-300 group-hover:translate-x-1"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L11.29 6.15a.75.75 0 111.02-1.1l4.5 4.25a.75.75 0 010 1.1l-4.5 4.25a.75.75 0 11-1.02-1.1l3.098-3.1H3.75A.75.75 0 013 10z" clip-rule="evenodd" /></svg>
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-gray-400">No blog posts found.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $blogs->links() }}</div>
    </div>
@endsection
