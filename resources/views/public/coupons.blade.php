@extends('public.layouts.app')

@push('head')
    @vite(['resources/js/select2-init.js'])
@endpush

@section('content')
    @include('public.partials.page-header', ['heading' => $heading, 'subheading' => $subheading])
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div data-ajax-filter data-base-url="{{ \App\Models\PageSetting::urlFor($region, 'coupons') }}" class="mt-6 grid grid-cols-1 gap-8 lg:grid-cols-[22%_1fr]">
            <aside>
                <form data-ajax-filter-form class="space-y-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-900">Search</p>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search promo codes..." autocomplete="off"
                               data-autosuggest-endpoint="{{ route('public.suggest.coupons', $region->code) }}"
                               class="mt-2 block w-full rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-900">Filter by Store Category</p>
                        <select name="store_category_id" data-select2-enable data-instant-filter data-placeholder="All Categories" class="mt-2 block w-full rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">All Categories</option>
                            @foreach ($storeCategories as $category)
                                <option value="{{ $category->id }}" @selected($selectedStoreCategoryId == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @include('partials.ajax-search-button', ['class' => 'flex w-full items-center justify-center gap-2 rounded-md bg-emerald-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-600 disabled:cursor-wait disabled:opacity-75'])

                    <a href="{{ \App\Models\PageSetting::urlFor($region, 'coupons') }}" data-no-ajax class="block text-center text-sm text-gray-500 hover:text-gray-700">Clear all</a>
                </form>
            </aside>

            <div data-ajax-filter-results>
                @include('public.partials.coupons-results')
            </div>
        </div>
    </div>
@endsection
