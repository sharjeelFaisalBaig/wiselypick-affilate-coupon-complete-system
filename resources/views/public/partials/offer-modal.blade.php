{{-- Expects $offer, $store, $redirectUrl — used for both coupons (shows the
     code + copy button) and deals (no code exists, shows "No Code Required"
     in the same slot instead). --}}
@php
    $isCoupon = $offer->isCoupon();
    $accent = $isCoupon ? 'from-emerald-500 to-teal-500' : 'from-deal-500 to-deal-700';
    $codeBoxClasses = $isCoupon
        ? 'border-emerald-300 bg-emerald-50 text-emerald-700'
        : 'border-deal-300 bg-deal-50 text-deal-700';
    $ctaClasses = $isCoupon
        ? 'bg-gradient-to-r from-emerald-500 to-teal-500 hover:shadow-emerald-500/30'
        : 'bg-gradient-to-r from-deal-500 to-deal-700 hover:shadow-deal-500/30';
@endphp
<div id="offer-modal-{{ $offer->id }}" data-modal class="hidden fixed inset-0 z-40 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/35 backdrop-blur-sm"></div>
    <div class="animate-pop relative w-full max-w-sm overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="h-1.5 bg-gradient-to-r {{ $accent }}"></div>
        <div class="p-6">
            <button type="button" data-modal-close aria-label="Close"
                    class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition-colors duration-200 hover:bg-gray-100 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>

            <div class="flex items-center gap-3">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-100 bg-white shadow-sm ring-1 ring-gray-100 sm:h-11 sm:w-11">
                    @if ($store->logo_path)
                        <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}" width="44" height="44" class="h-full w-full object-contain p-1">
                    @else
                        @include('public.partials.placeholder-image', ['class' => 'h-full w-full', 'iconClass' => 'h-4 w-4'])
                    @endif
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm text-gray-500">{{ $store->name }}</p>
                    <p class="truncate text-lg font-bold text-gray-900">{{ $offer->title }}</p>
                </div>
            </div>

            @if ($offer->code)
                <div class="ticket-notch mt-5 flex items-center rounded-xl border-2 border-dashed p-3 {{ $codeBoxClasses }}">
                    <span class="flex-1 truncate font-mono text-lg font-semibold">{{ $offer->code }}</span>
                    <button type="button" data-copy="{{ $offer->code }}" data-copy-label="Copy"
                            class="btn-shine ml-3 shrink-0 rounded-full px-4 py-1.5 text-sm font-semibold text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md {{ $ctaClasses }}">
                        Copy
                    </button>
                </div>
            @else
                <div class="ticket-notch mt-5 flex items-center justify-center rounded-xl border-2 border-dashed p-3 {{ $codeBoxClasses }}">
                    <span class="font-semibold">No Code Required</span>
                </div>
            @endif

            <p class="mt-3 text-xs text-gray-400">We've opened {{ $store->name }} in another tab — keep this tab open{{ $offer->code ? ' to copy your code' : '' }}.</p>

            <a href="{{ $redirectUrl }}" target="_blank" rel="noopener sponsored"
               class="btn-shine mt-4 block w-full rounded-full px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg {{ $ctaClasses }}">
                Continue to {{ $store->name }}
            </a>
        </div>
    </div>
</div>
