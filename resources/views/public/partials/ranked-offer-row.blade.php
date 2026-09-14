{{--
    "Top Performing Coupons" ranked leaderboard row — homepage section
    content_type "ranked". Expects $offer (with store loaded), $region, $rank.
--}}
@php
    $store = $offer->store;
    $isCoupon = $offer->isCoupon();
    $redirectUrl = route('public.offer.redirect', [$region->code, $offer]);
    $badgeItems = $offer->badges->sortBy(fn ($b) => $b->name === 'Verified' ? 0 : 1)->values();
    $accent = $isCoupon
        ? 'bg-gradient-to-r from-emerald-500 to-teal-500 hover:shadow-emerald-500/30'
        : 'bg-gradient-to-r from-deal-500 to-deal-700 hover:shadow-deal-500/30';
@endphp
<div data-reveal class="card-lift group flex cursor-pointer items-center gap-4 rounded-xl border border-gray-200 bg-white p-3 shadow-sm hover:border-transparent sm:p-4"
     @if ($isCoupon) data-coupon-cta @else data-deal-cta @endif data-offer-id="{{ $offer->id }}" data-redirect-url="{{ $redirectUrl }}">
    <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-lg bg-[var(--color-dark-surface)] text-white">
        <span class="text-lg font-extrabold leading-none">#{{ $rank }}</span>
        <span class="mt-0.5 text-[0.6rem] font-semibold uppercase tracking-wide text-gray-300">Top</span>
    </div>

    <span class="hidden h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-100 bg-white shadow-sm sm:flex">
        @if ($store->logo_path)
            <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}" width="40" height="40" loading="lazy" class="h-full w-full object-contain p-1">
        @else
            @include('public.partials.placeholder-image', ['class' => 'h-full w-full', 'iconClass' => 'h-3.5 w-3.5'])
        @endif
    </span>

    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold text-gray-900 sm:text-base">{{ $offer->store->name }} — {{ $offer->title }}</p>
        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
            @foreach ($badgeItems->take(3) as $badge)
                <span class="rounded-full px-2 py-0.5 text-[0.65rem] font-semibold {{ $badge->style() ? '' : $badge->classes() }}" @if ($badge->style()) style="{{ $badge->style() }}" @endif>
                    @if ($badge->name === 'Verified') &check; @endif{{ $badge->name }}
                </span>
            @endforeach
            @if ($offer->expiry_date)
                <span>Expires {{ $offer->expiry_date->format('M j, Y') }}</span>
            @endif
        </div>
    </div>

    <button type="button" class="btn-shine shrink-0 rounded-full px-4 py-2 text-xs font-semibold text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg sm:text-sm {{ $accent }}">
        {{ $isCoupon ? 'Get Code' : 'Get Deal' }}
    </button>
</div>

@push('modals')
    @include('public.partials.offer-modal', ['offer' => $offer, 'store' => $store, 'redirectUrl' => $redirectUrl])
@endpush
