{{--
    Expects: $type (coupon|deal|store), $inputName (offer_ids|store_ids),
    $label, $max, $selected (Collection<{id,label}>).

    A readonly Select2-style tag field: shows the current selection as
    chips and hands off all actual picking to the modal opened by "Edit".
--}}
<div data-picker-summary data-type="{{ $type }}" data-max="{{ $max }}" data-input-name="{{ $inputName }}">
    <label class="mb-1 block text-sm font-medium text-gray-700">{{ $label }} (max {{ $max }})</label>

    <div class="flex items-start gap-3">
        <div data-picker-chips
             class="flex min-h-[3rem] flex-1 flex-wrap items-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-3 py-2">
            @forelse ($selected as $item)
                <span data-chip data-id="{{ $item['id'] }}"
                      class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-800">
                    {{ $item['label'] }}
                </span>
            @empty
                <span data-picker-empty-hint class="text-sm italic text-gray-400">Nothing selected yet</span>
            @endforelse
        </div>
        <button type="button" data-picker-open
                class="shrink-0 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
            Edit
        </button>
    </div>

    <div data-picker-inputs>
        @foreach ($selected as $item)
            <input type="hidden" name="{{ $inputName }}[]" value="{{ $item['id'] }}">
        @endforeach
    </div>
</div>
