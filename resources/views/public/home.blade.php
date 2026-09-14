@extends('public.layouts.app')

@section('content')
    {{-- Hero: gradient band + search, matching the modern coupon-site pattern
         (a plain white page with no lede is what item 26's feedback flagged). --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-[var(--color-dark-surface)] via-[var(--color-dark-surface)] to-[var(--color-dark-surface-deep)]">
        <div class="blob animate-float pointer-events-none absolute -left-24 -top-24 h-72 w-72 bg-emerald-500/10"></div>
        <div class="blob animate-float-delayed pointer-events-none absolute -bottom-24 -right-24 h-72 w-72 bg-teal-300/10"></div>
        <div class="blob animate-float pointer-events-none absolute right-1/4 top-0 h-40 w-40 bg-deal-300/10"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8">
            @if ($heroBadgeText)
                <span class="animate-fade-up inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white ring-1 ring-inset ring-white/20 backdrop-blur-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 text-emerald-400"><path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.454 1.405 1.02L10 15.591l4.069 2.485c.713.434 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" /></svg>
                    {{ $heroBadgeText }}
                </span>
            @endif
            <h1 class="animate-fade-up mt-4 font-display text-3xl font-extrabold tracking-tight text-white sm:text-5xl">{{ $heading }}</h1>
            <p class="animate-fade-up mx-auto mt-3 max-w-2xl text-sm text-gray-300 sm:text-base">{{ $subheading }}</p>

            <form action="{{ \App\Models\PageSetting::urlFor($region, 'coupons') }}" method="GET" class="animate-fade-up mx-auto mt-6 flex max-w-xl gap-2">
                <input type="search" name="q" placeholder="{{ $heroSearchPlaceholder }}" autocomplete="off"
                       data-autosuggest-endpoint="{{ route('public.suggest.coupons', $region->code) }}"
                       class="w-full rounded-full border-0 bg-white px-5 py-3 text-sm text-gray-900 shadow-lg transition-shadow duration-200 focus:outline-none focus:ring-4 focus:ring-white/50">
                <button type="submit" class="btn-shine shrink-0 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl active:translate-y-0">
                    {{ $heroSearchButtonText }}
                </button>
            </form>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @forelse ($sections as $section)
            {{-- p-6 applies to every section regardless of tint, so the
                 content grid's available width stays identical whether or
                 not a section happens to have the alternating background —
                 previously only tinted sections got padding, narrowing
                 their grids relative to their neighbors. --}}
            <div data-reveal class="rounded-2xl p-6 {{ !$loop->first ? 'mt-10' : '' }} {{ $loop->even ? 'bg-gradient-to-br from-gray-50 to-emerald-50/50' : '' }}">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="relative pl-3.5 text-xl font-bold text-gray-900 before:absolute before:left-0 before:top-1 before:h-5 before:w-1.5 before:rounded-full before:bg-gradient-to-b before:from-emerald-500 before:to-teal-500 {{ $loop->first ? 'text-2xl' : '' }}">{{ $section->title }}</h2>
                    @if ($section->cta_label && $section->display_cta_url)
                        <a href="{{ $section->display_cta_url }}" @if ($section->cta_target === 'new_tab') target="_blank" rel="noopener" @endif
                           class="group inline-flex items-center gap-1 rounded-full border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 hover:shadow-sm active:translate-y-0">
                            {{ $section->cta_label }}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-0.5"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L11.29 6.15a.75.75 0 111.02-1.1l4.5 4.25a.75.75 0 010 1.1l-4.5 4.25a.75.75 0 11-1.02-1.1l3.098-3.1H3.75A.75.75 0 013 10z" clip-rule="evenodd" /></svg>
                        </a>
                    @endif
                </div>

                @if ($section->content_type === 'store')
                    {{-- Trending Stores: 6 stores, 3 per row, subtitle reflects actual inventory. --}}
                    <div data-reveal-group class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-3">
                        @foreach ($section->stores as $store)
                            @php
                                $hasCoupons = $store->active_coupons_count > 0;
                                $hasDeals = $store->active_deals_count > 0;
                                $subtitle = match (true) {
                                    $hasCoupons && $hasDeals => 'Coupons, Promo-Codes & Deals',
                                    $hasDeals => 'Deals',
                                    $hasCoupons => 'Coupons & Promo-Codes',
                                    default => 'Coupons, Promo-Codes & Deals',
                                };
                            @endphp
                            <a data-reveal href="{{ $store->urlFor($region) }}" class="group flex items-center gap-3 rounded-xl border border-transparent p-2 transition-all duration-300 hover:-translate-y-0.5 hover:border-gray-100 hover:bg-white hover:shadow-md">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-100 bg-white shadow-sm transition-transform duration-300 group-hover:scale-110">
                                    @if ($store->logo_path)
                                        <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}" width="48" height="48" loading="lazy" class="h-full w-full object-contain p-1.5">
                                    @else
                                        @include('public.partials.placeholder-image', ['class' => 'h-full w-full', 'iconClass' => 'h-4 w-4'])
                                    @endif
                                </span>
                                <div>
                                    <p class="font-medium text-gray-900 group-hover:text-emerald-700">{{ $store->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $subtitle }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @elseif ($section->content_type === 'deal')
                    {{-- Deals Section: 4 cards per row, hero-style, click opens affiliate link. --}}
                    <div data-reveal-group class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($section->offers as $offer)
                            @include('public.partials.offer-card-hero', ['offer' => $offer])
                        @endforeach
                    </div>
                @elseif ($section->content_type === 'ranked')
                    {{-- Top Performing Coupons: numbered leaderboard rows, stacked. --}}
                    <div data-reveal-group class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                        @foreach ($section->offers as $offer)
                            @include('public.partials.ranked-offer-row', ['offer' => $offer, 'rank' => $loop->iteration])
                        @endforeach
                    </div>
                @elseif ($section->content_type === 'categories')
                    @include('public.partials.category-icon-grid', ['categories' => $section->categories])
                @else
                    {{-- Coupons Per Category: 5-column single row. --}}
                    <div data-reveal-group class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($section->offers as $offer)
                            @include('public.partials.offer-card-compact', ['offer' => $offer])
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-gray-300 p-10 text-center text-gray-400">
                No homepage sections have been configured yet for {{ $region->name }}. Add some under Admin → Homepage Sections.
            </div>
        @endforelse
    </div>
@endsection
