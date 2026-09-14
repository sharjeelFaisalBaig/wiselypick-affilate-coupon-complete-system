{{-- Results fragment for /coupons — also returned directly for AJAX filter requests. --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    @forelse ($offers as $offer)
        @include('public.partials.offer-card', ['offer' => $offer])
    @empty
        <p class="col-span-full text-gray-400">No active promo codes match your filters right now.</p>
    @endforelse
</div>

<div class="mt-6">{{ $offers->links() }}</div>
