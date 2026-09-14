{{-- Search button for an [data-ajax-filter-form] — shows a spinner and
     disables itself while the request is in flight (see ajax-filters.js).
     Optional $class overrides the button's own classes if a caller needs a
     different size/placement than the default. --}}
@php $btnClass = $class ?? 'w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-md bg-emerald-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-600 disabled:cursor-wait disabled:opacity-75'; @endphp
<button type="submit" data-ajax-filter-submit class="{{ $btnClass }}">
    <svg data-ajax-filter-spinner class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span>Search</span>
</button>
