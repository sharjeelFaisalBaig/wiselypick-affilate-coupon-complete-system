{{--
    "Deals Section" card per the SRS: thumbnail, used count, up to 3 badges,
    expiry date, deal title. The whole card — including the thumbnail — is
    one uniform click target: same-tab redirect + a new tab showing the
    offer modal (which renders "No Code Required" here since deals never
    have a code) — via data-deal-cta / initOfferCta() in app.js. The
    thumbnail deliberately does NOT link to the store page (it used to,
    which made clicking it behave differently from the rest of the card).
    Expects $offer (with store loaded) and $region.
--}}
@php
    $store = $offer->store;
    $thumbnailPath = $store->logo_path;
    $redirectUrl = route('public.offer.redirect', [$region->code, $offer]);
@endphp
<div data-reveal class="card-lift group relative flex h-full cursor-pointer flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm hover:border-transparent"
     data-deal-cta data-offer-id="{{ $offer->id }}" data-redirect-url="{{ $redirectUrl }}">
    <div class="h-1.5 shrink-0 bg-gradient-to-r from-deal-500 to-deal-700"></div>
    <div class="flex h-44 items-center justify-center overflow-hidden border-b border-gray-100 bg-gradient-to-br from-gray-50 to-white p-6">
        @if ($thumbnailPath)
            <img src="{{ Storage::url($thumbnailPath) }}" alt="{{ $store->name }}" width="160" height="80" loading="lazy" class="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-110">
        @else
            @include('public.partials.placeholder-image', ['class' => 'h-20 w-20 rounded', 'iconClass' => 'h-8 w-8'])
        @endif
    </div>
    {{--
        flex-1 + the title's mt-auto keeps the title pinned to the same
        baseline across every card in a row, regardless of whether the
        optional badges/expiry line above it are present — without this,
        cards missing a badge had their title sit noticeably higher than
        neighboring cards that had one.
    --}}
    <div class="flex flex-1 flex-col p-4">
        <div>
            {{-- Features render in one wrapped row --}}
            @if ($offer->badges->isNotEmpty())
                <div class="flex flex-row flex-wrap items-center gap-1.5">
                    @foreach ($offer->badges->sortBy(fn ($b) => $b->name === 'Verified' ? 0 : 1) as $badge)
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $badge->style() ? '' : $badge->classes() }}" @if ($badge->style()) style="{{ $badge->style() }}" @endif>{{ $badge->name }}</span>
                    @endforeach
                </div>
            @endif
            @if ($offer->expiry_date)
                <p class="mt-1 flex items-center gap-1 text-xs text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .199.079.39.22.53l3.5 3.5a.75.75 0 101.06-1.06L10.75 9.69V5z" clip-rule="evenodd" /></svg>
                    Expires {{ $offer->expiry_date->format('M j, Y') }}
                </p>
            @endif
            <p class="mt-1 text-xs text-gray-400">
                {{ $store->name }} code &middot; {{ $offer->usageLabel() }}
            </p>
        </div>
        <p class="mt-auto pt-2 font-bold text-gray-900">{{ $offer->title }} at {{ $store->name }}</p>
    </div>
</div>

{{-- Pushed to the body-level stack (see layouts.app) — see offer-card.blade.php
     for why: a [data-reveal] ancestor's transform otherwise contains this
     fixed-position modal instead of the real viewport. --}}
@push('modals')
    @include('public.partials.offer-modal', ['offer' => $offer, 'store' => $store, 'redirectUrl' => $redirectUrl])
@endpush
