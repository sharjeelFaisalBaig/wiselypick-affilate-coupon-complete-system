{{-- Results fragment for the store detail page's Row 4 offer grid — also returned directly for AJAX filter requests. --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @forelse ($offers as $offer)
        @include('public.partials.offer-card', ['offer' => $offer, 'hideStoreLink' => true])
    @empty
        <p class="col-span-full text-center text-gray-400">No active offers match your filters right now.</p>
    @endforelse
</div>
<div class="mt-6">{{ $offers->links() }}</div>
