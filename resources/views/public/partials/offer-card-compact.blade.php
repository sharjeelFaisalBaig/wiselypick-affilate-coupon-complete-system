{{--
    Homepage category-section card. Expects $offer (with store loaded) and $region.
--}}
@php
    $store = $offer->store;
    $thumbnailPath = $store->logo_path;
    $badgeItems = $offer->badges->sortBy(fn ($b) => $b->name === 'Verified' ? 0 : 1)->values();
    $isCoupon = $offer->isCoupon();
    $redirectUrl = route('public.offer.redirect', [$region->code, $offer]);
    $accent = $isCoupon ? 'from-emerald-500 to-teal-500' : 'from-deal-500 to-deal-700';
    $ctaClasses = $isCoupon
        ? 'bg-gradient-to-r from-emerald-500 to-teal-500 hover:shadow-emerald-500/30'
        : 'bg-gradient-to-r from-deal-500 to-deal-700 hover:shadow-deal-500/30';
@endphp
<div data-reveal class="card-lift group relative flex cursor-pointer flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm hover:border-transparent"
     @if ($isCoupon) data-coupon-cta @else data-deal-cta @endif data-offer-id="{{ $offer->id }}" data-redirect-url="{{ $redirectUrl }}">
    <div class="h-1.5 shrink-0 bg-gradient-to-r {{ $accent }}"></div>

    <div class="flex flex-1 flex-col p-4">
        <span class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-100 bg-white shadow-sm ring-1 ring-gray-100 transition-transform duration-300 group-hover:scale-110 sm:h-11 sm:w-11">
            @if ($thumbnailPath)
                <img src="{{ Storage::url($thumbnailPath) }}" alt="{{ $store->name }}" width="44" height="44" loading="lazy" class="h-full w-full object-contain p-1">
            @else
                @include('public.partials.placeholder-image', ['class' => 'h-full w-full', 'iconClass' => 'h-4 w-4'])
            @endif
        </span>

        <p class="mt-3 text-lg font-bold leading-snug text-gray-900">{{ $offer->title }}</p>

        {{-- Features render in one wrapped row --}}
        @if ($badgeItems->isNotEmpty())
            <div class="mt-2 flex flex-row flex-wrap items-center gap-1.5">
                @foreach ($badgeItems as $badge)
                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $badge->style() ? '' : $badge->classes() }}" @if ($badge->style()) style="{{ $badge->style() }}" @endif>
                        @if ($badge->name === 'Verified') &check; @endif{{ $badge->name }}
                    </span>
                @endforeach
            </div>
        @endif

        <p class="mt-2 flex-1 text-sm text-gray-500">At <span class="font-medium text-gray-700">{{ $store->name }}</span></p>

        @if ($offer->expiry_date)
            <p class="mt-1 flex items-center gap-1 text-xs text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .199.079.39.22.53l3.5 3.5a.75.75 0 101.06-1.06L10.75 9.69V5z" clip-rule="evenodd" /></svg>
                Expires {{ $offer->expiry_date->format('M j, Y') }}
            </p>
        @endif

        <a href="{{ $store->urlFor($region) }}" onclick="event.stopPropagation()" class="link-underline mt-2 text-xs text-gray-500 hover:text-emerald-600">
            More {{ $store->name }} {{ $isCoupon ? 'coupons' : 'deals' }}
        </a>

        <p class="mt-1 flex items-center gap-1 text-xs text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5"><path d="M10 2a1 1 0 011 1v1.05a7.001 7.001 0 015.95 5.95H18a1 1 0 110 2h-1.05A7.001 7.001 0 0111 16.95V18a1 1 0 11-2 0v-1.05A7.001 7.001 0 013.05 11H2a1 1 0 110-2h1.05A7.001 7.001 0 019 3.05V2a1 1 0 011-1zm0 4a5 5 0 100 10 5 5 0 000-10z" /></svg>
            {{ $offer->usageLabel() }}
        </p>

        <div class="ticket-notch mt-3 pt-3">
            @if ($isCoupon)
                <button type="button" class="btn-shine block w-full rounded-full px-4 py-2 text-center text-sm font-semibold text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg {{ $ctaClasses }}">
                    Shop with code
                </button>
            @else
                <span class="btn-shine block w-full rounded-full px-4 py-2 text-center text-sm font-semibold text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg {{ $ctaClasses }}">
                    View Deal
                </span>
            @endif
        </div>
    </div>
</div>

{{-- Pushed to the body-level stack (see layouts.app) — see offer-card.blade.php
     for why: a [data-reveal] ancestor's transform otherwise contains this
     fixed-position modal instead of the real viewport. --}}
@push('modals')
    @include('public.partials.offer-modal', ['offer' => $offer, 'store' => $store, 'redirectUrl' => $redirectUrl])
@endpush
