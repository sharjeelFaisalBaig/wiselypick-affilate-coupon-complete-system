@extends('admin.layouts.app')

@section('title', 'Featured & Popular')

@section('content')
    <p class="mb-4 text-sm text-gray-500">
        Drag rows to reorder — each tab has its own independent order. To add or remove a store from a tab, edit the store and toggle "Featured" / "Popular" / "Pending Store". Featured Deals lists every featured promotion across every store, ordered separately from that promotion's own position on its store's detail page.
    </p>

    <div data-tabs>
        <div class="flex flex-wrap gap-1 border-b border-gray-200">
            <button type="button" data-tab-trigger="featured" class="px-4 py-2.5 text-sm font-medium">Featured Stores</button>
            <button type="button" data-tab-trigger="popular" class="px-4 py-2.5 text-sm font-medium">Popular Stores</button>
            <button type="button" data-tab-trigger="pending" class="px-4 py-2.5 text-sm font-medium">Pending Stores</button>
            <button type="button" data-tab-trigger="featured-deals" class="px-4 py-2.5 text-sm font-medium">Featured Deals</button>
        </div>

        <div data-tab-panel="featured" class="pt-4">
            @include('admin.stores._classification-store-table', ['stores' => $featured, 'reorderUrl' => route('admin.stores.reorder-featured'), 'emptyLabel' => 'No featured stores yet.', 'suggestScope' => 'featured'])
        </div>

        <div data-tab-panel="popular" class="pt-4">
            @include('admin.stores._classification-store-table', ['stores' => $popular, 'reorderUrl' => route('admin.stores.reorder-popular'), 'emptyLabel' => 'No popular stores yet.', 'suggestScope' => 'popular'])
        </div>

        <div data-tab-panel="pending" class="pt-4">
            @include('admin.stores._classification-store-table', ['stores' => $pending, 'reorderUrl' => route('admin.stores.reorder-pending'), 'emptyLabel' => 'No pending stores yet.', 'suggestScope' => 'pending'])
        </div>

        <div data-tab-panel="featured-deals" class="pt-4">
            <div class="mb-3">
                <input type="text" data-row-filter placeholder="Search this list..." autocomplete="off"
                       data-autosuggest-endpoint="{{ route('admin.offers.suggest', ['scope' => 'featured']) }}"
                       class="block w-full max-w-xs rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:w-64">
            </div>
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" data-reorder-loading-target>
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="w-8 px-2 py-3"></th>
                            <th class="px-4 py-3">Coupon</th>
                            <th class="px-4 py-3">Store</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Expiry</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody data-sortable data-sortable-url="{{ route('admin.stores.reorder-featured-offers') }}" class="divide-y divide-gray-100">
                        @forelse ($featuredOffers as $offer)
                            <tr data-sort-id="{{ $offer->id }}" class="cursor-move hover:bg-gray-50">
                                <td class="px-2 py-3 text-center text-gray-300" aria-hidden="true">⠿</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $offer->title }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $offer->store->name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ ucfirst($offer->offer_type) }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ optional($offer->expiry_date)->format('M j, Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.offers.edit', $offer) }}" class="font-medium text-emerald-600 hover:text-emerald-700" data-no-ajax>Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No featured coupons yet — check "Featured Coupon" on a coupon or deal to add one here.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
