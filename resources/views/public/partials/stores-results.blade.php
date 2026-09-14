{{-- Results fragment for /stores Row 2 — also returned directly for AJAX filter requests. --}}
<div data-reveal-group class="grid grid-cols-1 gap-x-8 gap-y-2 sm:grid-cols-2">
    @forelse ($stores as $store)
        <a data-reveal href="{{ $store->urlFor($region) }}" class="group flex items-center gap-3 rounded-xl border border-transparent px-2 py-3 transition-all duration-300 hover:-translate-y-0.5 hover:border-gray-100 hover:bg-white hover:shadow-md">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-100 bg-white shadow-sm transition-transform duration-300 group-hover:scale-110">
                @if ($store->logo_path)
                    <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}" width="48" height="48" loading="lazy" class="h-full w-full object-contain p-1.5">
                @else
                    @include('public.partials.placeholder-image', ['class' => 'h-full w-full', 'iconClass' => 'h-4 w-4'])
                @endif
            </span>
            <div>
                <p class="font-medium text-gray-900 group-hover:text-emerald-700">{{ $store->name }} coupons</p>
                <p class="text-xs text-gray-400">
                    {{ $store->active_coupons_count }} coupon{{ $store->active_coupons_count === 1 ? '' : 's' }}
                    &middot; {{ $store->active_deals_count }} deal{{ $store->active_deals_count === 1 ? '' : 's' }}
                </p>
            </div>
        </a>
    @empty
        <p class="text-gray-400">No stores found.</p>
    @endforelse
</div>

<div class="mt-6">{{ $stores->links() }}</div>
