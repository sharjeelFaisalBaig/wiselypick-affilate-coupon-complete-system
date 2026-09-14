{{--
    Expects $offer (App\Models\Offer, possibly new) and $badges (Collection<Badge>) in scope.
    $stores/$selectedStore are only passed by the global create/edit forms.
--}}
@php
    // "Verified" starts pre-checked on the Add Coupon form only — the
    // edit form always reflects whatever is actually stored.
    $selectedBadgeIds = old('badge_ids', $offer->exists
        ? $offer->badges->pluck('id')->all()
        : $badges->where('name', 'Verified')->pluck('id')->all());
@endphp

@if (isset($stores))
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Store / Brand</label>
        <select name="store_id" required data-select2-enable class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">— Select a store —</option>
            @foreach ($stores as $storeOption)
                <option value="{{ $storeOption->id }}" @selected(old('store_id', $selectedStore?->id) == $storeOption->id)>{{ $storeOption->name }}</option>
            @endforeach
        </select>
    </div>
@endif

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Coupon Type</label>
        <select name="offer_type" data-offer-type required
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="coupon" @selected(old('offer_type', $offer->offer_type) === 'coupon')>Coupon Code</option>
            <option value="deal" @selected(old('offer_type', $offer->offer_type) === 'deal')>Deal (no code)</option>
        </select>
    </div>

    <div data-code-wrapper>
        <label class="mb-1 block text-sm font-medium text-gray-700">Coupon Code</label>
        <input type="text" name="code" value="{{ old('code', $offer->code) }}"
               class="block w-full rounded-md border-gray-300 uppercase shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
    </div>
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-gray-700">Offer Title</label>
    <input type="text" name="title" value="{{ old('title', $offer->title) }}" required maxlength="255"
           placeholder="e.g. 15% Off, $10 Off Storewide, Free Shipping"
           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
    <p class="mt-1 text-xs text-gray-400">The exact text shown on the card — type the discount, currency symbol, or offer text yourself. Keep to ~60 characters so it doesn't wrap awkwardly.</p>
    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Start Date</label>
        <input type="date" name="start_date" value="{{ old('start_date', optional($offer->start_date)->format('Y-m-d')) }}"
               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Expiry Date &amp; Time</label>
        <input type="datetime-local" name="expiry_date" value="{{ old('expiry_date', optional($offer->expiry_date)->format('Y-m-d\TH:i')) }}"
               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
    </div>
</div>

@if ($offer->exists)
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Usage Counter</label>
        <input type="number" min="0" name="clicks" value="{{ old('clicks', $offer->clicks) }}"
               class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
        <p class="mt-1 text-xs text-gray-400">Increments automatically on shopper clicks — editable here if you need to correct it.</p>
    </div>
@endif

<div>
    <label class="mb-1 block text-sm font-medium text-gray-700">Coupon Features (max 3)</label>
    <div data-max-badges="3" class="flex flex-wrap gap-4">
        @foreach ($badges as $badge)
            <label class="flex items-center gap-2">
                <input type="checkbox" name="badge_ids[]" value="{{ $badge->id }}" data-badge-checkbox
                       @checked(in_array($badge->id, $selectedBadgeIds))
                       class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                <span class="text-sm text-gray-700">{{ $badge->name }}</span>
            </label>
        @endforeach
    </div>
    <label class="mt-3 flex items-center gap-2">
        <input type="checkbox" name="is_featured" value="1"
               @checked(old('is_featured', $offer->id ? $offer->is_featured : false))
               class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
        <span class="text-sm text-gray-700">Featured Coupon</span>
    </label>
</div>

<label class="flex items-center gap-2">
    <input type="checkbox" name="is_active" value="1"
           @checked(old('is_active', $offer->id ? $offer->is_active : true))
           class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
    <span class="text-sm text-gray-700">Published</span>
</label>
