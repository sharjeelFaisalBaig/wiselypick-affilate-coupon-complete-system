@extends('admin.layouts.app')

@section('title', 'Coupons')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500">Coupons are managed one store at a time. Drag rows to reorder how they appear on that store's page.</p>
        <a href="{{ route('admin.offers.create', ['store_id' => $selectedStore?->id]) }}" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
            + Add Coupon
        </a>
    </div>

    <div data-ajax-filter data-base-url="{{ route('admin.offers.index') }}">
        <form data-ajax-filter-form class="mb-4 flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:flex-wrap sm:items-end">
            <div class="w-full min-w-0 sm:w-auto">
                <label class="mb-1 block text-xs font-medium text-gray-500">Store</label>
                <select name="store_id" required data-select2-enable class="w-full min-w-0 rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:w-auto">
                    @foreach ($stores as $s)
                        <option value="{{ $s->id }}" @selected($selectedStore?->id === $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full min-w-0 sm:w-auto">
                <label class="mb-1 block text-xs font-medium text-gray-500">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Title, code, feature..." autocomplete="off"
                       data-autosuggest-endpoint="{{ route('admin.offers.suggest') }}"
                       class="w-full min-w-0 rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:w-auto">
            </div>
            <div class="flex flex-col justify-end">
                <span class="mb-1 hidden text-xs font-medium text-transparent select-none sm:block" aria-hidden="true">Search</span>
                @include('partials.ajax-search-button')
            </div>
        </form>

        <div data-ajax-filter-results>
            @include('admin.offers._results')
        </div>
    </div>
@endsection
