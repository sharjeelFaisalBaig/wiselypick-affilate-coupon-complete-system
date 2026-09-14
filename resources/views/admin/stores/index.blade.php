@extends('admin.layouts.app')

@section('title', 'Stores')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex gap-2">
            <a href="{{ route('admin.stores.classification') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:-translate-y-0.5 hover:border-gray-400 hover:bg-gray-50 hover:shadow-sm active:translate-y-0">
                Featured & Popular
            </a>
            <a href="{{ route('admin.stores.create') }}" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                + Add Store
            </a>
        </div>
    </div>

    <div data-ajax-filter data-base-url="{{ route('admin.stores.index') }}">
        <form data-ajax-filter-form class="mb-4 flex flex-col gap-2 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:flex-row sm:flex-wrap sm:items-center">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search store name..." autocomplete="off"
                   data-autosuggest-endpoint="{{ route('admin.stores.suggest') }}"
                   class="w-full min-w-0 rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:w-auto">
            <div class="w-full sm:w-56">
                <select name="category_id" data-select2-enable data-placeholder="All Categories" class="w-full rounded-md border-gray-300 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-48">
                <select name="status" data-select2-enable data-placeholder="All Statuses" class="w-full rounded-md border-gray-300 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Pending</option>
                </select>
            </div>
            @include('partials.ajax-search-button')
            @if (request()->hasAny(['q', 'category_id', 'status']))
                <a href="{{ route('admin.stores.index') }}" data-no-ajax class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>

        <div data-ajax-filter-results>
            @include('admin.stores._results')
        </div>
    </div>
@endsection
