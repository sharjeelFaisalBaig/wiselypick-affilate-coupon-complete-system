@extends('public.layouts.app')

@push('schema')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Store',
        'name' => $store->name,
        'url' => url()->current(),
        'aggregateRating' => $store->star_rating > 0 ? [
            '@type' => 'AggregateRating',
            'ratingValue' => (string) $store->star_rating,
            'bestRating' => '5',
        ] : null,
    ], JSON_UNESCAPED_SLASHES) !!}
    </script>
@endpush

@section('content')
    <div class="relative overflow-hidden bg-gradient-to-r from-[var(--color-dark-surface)] to-[var(--color-dark-surface-deep)]">
        <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-emerald-500/10 blur-3xl"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-10 text-center sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-white sm:text-3xl">{{ $h1 }}</h1>
            @if ($generalSettings->store_page_disclaimer)
                <p class="mx-auto mt-2 max-w-2xl text-center text-sm text-gray-300">
                    {{ $generalSettings->store_page_disclaimer }}
                </p>
            @endif
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div data-ajax-filter data-base-url="{{ $store->urlFor($region) }}" class="mt-6">
            @php
                $currentOfferFilter = request('filter', '');
                $offerTabBase = 'rounded-full px-4 py-1.5 text-sm font-medium transition-colors';
                $offerTabActive = $offerTabBase.' bg-emerald-500 text-white shadow-sm';
                $offerTabInactive = $offerTabBase.' bg-white text-gray-600 border border-gray-300 hover:bg-gray-50';
            @endphp
            <form data-ajax-filter-form class="flex flex-col items-center gap-4">
                <div class="flex w-full max-w-xl gap-2">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search codes..."
                           class="w-full rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @include('partials.ajax-search-button')
                </div>

                <input type="hidden" name="filter" value="{{ $currentOfferFilter }}">
                <div class="flex flex-wrap justify-center gap-2">
                    <button type="button" data-instant-filter-tab data-instant-filter-target="filter" data-value=""
                            class="{{ $currentOfferFilter === '' ? $offerTabActive : $offerTabInactive }}">All</button>
                    <button type="button" data-instant-filter-tab data-instant-filter-target="filter" data-value="coupon"
                            class="{{ $currentOfferFilter === 'coupon' ? $offerTabActive : $offerTabInactive }}">Codes ({{ $couponCount }})</button>
                    <button type="button" data-instant-filter-tab data-instant-filter-target="filter" data-value="deal"
                            class="{{ $currentOfferFilter === 'deal' ? $offerTabActive : $offerTabInactive }}">Deals ({{ $dealCount }})</button>
                </div>
            </form>

            {{-- Main paginated offer grid — keeps our own page-number pagination, not the reference site's "load more" button --}}
            <div data-ajax-filter-results class="mt-6">
                @include('public.partials.store-offers-results')
            </div>
        </div>

        {{-- Store info card: left = logo/title/link/rating/reviews/about, right = auto-calculated savings stats --}}
        <div data-reveal class="mt-12 grid grid-cols-1 gap-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:grid-cols-2">
            <div>
                <div class="flex flex-col items-center gap-3 sm:flex-row sm:items-start">
                    <span class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-100 bg-white shadow-sm ring-1 ring-gray-100">
                        @if ($store->logo_path)
                            <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}" width="120" height="60" class="h-full w-full object-contain p-2">
                        @else
                            @include('public.partials.placeholder-image', ['class' => 'h-full w-full', 'iconClass' => 'h-6 w-6'])
                        @endif
                    </span>
                    <div class="text-center sm:text-left">
                        <p class="text-lg font-bold text-gray-900">{{ $store->name }}</p>
                        <div class="mt-1 flex items-center justify-center gap-1 text-amber-400 sm:justify-start">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="{{ $i <= round($store->star_rating) ? 'currentColor' : '#e5e7eb' }}" class="h-4 w-4"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.285-3.958z"/></svg>
                            @endfor
                            <span class="ml-1 text-sm text-gray-500">{{ number_format($store->star_rating, 1) }} / 5 ({{ number_format($store->reviews_count) }} reviews)</span>
                        </div>
                    </div>
                </div>
                <h2 class="mt-4 text-lg font-bold text-gray-900">About {{ $store->name }}</h2>
                @if ($store->about)
                    <div class="prose prose-emerald mt-2 max-w-none text-sm">{!! $store->about !!}</div>
                @else
                    <p class="mt-2 text-sm text-gray-400">No description available yet.</p>
                @endif
            </div>
            <div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 p-4">
                        <p class="text-xs font-medium text-emerald-700">Verified Discount Codes</p>
                        <p class="mt-1 text-2xl font-bold text-emerald-900">{{ $savingsStats['verified_codes'] }}</p>
                    </div>
                    <div class="rounded-xl bg-deal-50 p-4">
                        <p class="text-xs font-medium text-deal-700">Total Coupons</p>
                        <p class="mt-1 text-2xl font-bold text-deal-900">{{ $savingsStats['total_coupons'] }}</p>
                    </div>
                    <div class="col-span-2 rounded-xl bg-gray-50 p-4">
                        <p class="text-xs font-medium text-gray-500">Last Coupon Added</p>
                        <p class="mt-1 font-bold text-gray-900">{{ $savingsStats['last_coupon_added'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
