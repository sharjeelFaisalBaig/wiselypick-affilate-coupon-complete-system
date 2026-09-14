@extends('admin.layouts.app')

@section('title', $section->exists ? 'Edit Homepage Section' : 'Add Homepage Section')

@section('content')
    <div class="max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST"
              action="{{ $section->exists ? route('admin.homepage-sections.update', $section) : route('admin.homepage-sections.store') }}"
              data-homepage-section-form class="space-y-5">
            @csrf
            @if ($section->exists) @method('PUT') @endif

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Section Title</label>
                <input type="text" name="title" value="{{ old('title', $section->title) }}" required maxlength="255"
                       placeholder="e.g. Skincare, Trending Deals, Trending Stores"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <p class="mt-1 text-xs text-gray-400">Optimal length: ~30 characters.</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Content Type</label>
                <div class="flex gap-6">
                    @foreach (['coupon' => 'Coupon', 'deal' => 'Deal', 'mixed' => 'Mixed Deals/Coupons', 'store' => 'Trending Stores', 'ranked' => 'Top Coupons (Ranked List)', 'categories' => 'Category Icon Grid'] as $value => $label)
                        <label class="flex items-center gap-2">
                            <input type="radio" name="content_type" value="{{ $value }}" data-content-type
                                   @checked(old('content_type', $section->content_type ?: 'coupon') === $value) required
                                   class="border-gray-300 text-emerald-500 focus:ring-emerald-500">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            @php
                $pickerResultsUrl = route('admin.homepage-sections.picker-results');
                $selectedCoupons = $selectedOffers->where('offer_type', 'coupon')->values();
                $selectedDeals = $selectedOffers->where('offer_type', 'deal')->values();
            @endphp

            <div data-picker="coupon" class="hidden">
                @include('admin.homepage-sections._picker-field', [
                    'type' => 'coupon',
                    'inputName' => 'offer_ids',
                    'label' => 'Select Coupons',
                    'max' => \App\Models\HomepageSection::MAX_OFFERS,
                    'selected' => $selectedCoupons->map(fn ($o) => ['id' => $o->id, 'label' => $o->store->name.' — '.$o->title]),
                ])
            </div>

            <div data-picker="deal" class="hidden">
                @include('admin.homepage-sections._picker-field', [
                    'type' => 'deal',
                    'inputName' => 'offer_ids',
                    'label' => 'Select Deals',
                    'max' => \App\Models\HomepageSection::MAX_OFFERS,
                    'selected' => $selectedDeals->map(fn ($o) => ['id' => $o->id, 'label' => $o->store->name.' — '.$o->title]),
                ])
            </div>

            <div data-picker="mixed" class="hidden">
                @include('admin.homepage-sections._picker-field', [
                    'type' => 'mixed',
                    'inputName' => 'offer_ids',
                    'label' => 'Select Coupons & Deals',
                    'max' => \App\Models\HomepageSection::MAX_OFFERS,
                    'selected' => $selectedOffers->map(fn ($o) => ['id' => $o->id, 'label' => $o->store->name.' — '.$o->title]),
                ])
            </div>

            <div data-picker="store" class="hidden">
                @include('admin.homepage-sections._picker-field', [
                    'type' => 'store',
                    'inputName' => 'store_ids',
                    'label' => 'Select Stores',
                    'max' => \App\Models\HomepageSection::MAX_STORES,
                    'selected' => $selectedStores->map(fn ($s) => ['id' => $s->id, 'label' => $s->name]),
                ])
            </div>

            <div data-picker="ranked" class="hidden">
                @include('admin.homepage-sections._picker-field', [
                    'type' => 'ranked',
                    'inputName' => 'offer_ids',
                    'label' => 'Select Coupons/Deals to Rank',
                    'max' => \App\Models\HomepageSection::MAX_OFFERS,
                    'selected' => $selectedOffers->map(fn ($o) => ['id' => $o->id, 'label' => $o->store->name.' — '.$o->title]),
                ])
            </div>

            <div data-picker="categories" class="hidden">
                @include('admin.homepage-sections._picker-field', [
                    'type' => 'categories',
                    'inputName' => 'category_ids',
                    'label' => 'Select Categories',
                    'max' => \App\Models\HomepageSection::MAX_CATEGORIES,
                    'selected' => $selectedCategories->map(fn ($c) => ['id' => $c->id, 'label' => $c->name]),
                ])
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">CTA Label</label>
                    <input type="text" name="cta_label" value="{{ old('cta_label', $section->cta_label) }}"
                           placeholder="View All / Show All / Explore All"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">CTA Link (relative URL)</label>
                    <input type="text" name="cta_url" value="{{ old('cta_url', $section->cta_url) }}"
                           placeholder="/exclusive or /exclusive/stores"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">CTA Target</label>
                    <select name="cta_target" data-select2-enable class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="same_tab" @selected(old('cta_target', $section->cta_target ?? 'same_tab') === 'same_tab')>Same Tab</option>
                        <option value="new_tab" @selected(old('cta_target', $section->cta_target) === 'new_tab')>New Tab</option>
                    </select>
                </div>
            </div>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $section->id ? $section->is_active : true))
                       class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                <span class="text-sm text-gray-700">Active (visible on homepage)</span>
            </label>

            <div class="flex gap-3">
                <button type="submit" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    {{ $section->exists ? 'Save Changes' : 'Create Section' }}
                </button>
                <a href="{{ route('admin.homepage-sections.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:-translate-y-0.5 hover:border-gray-400 hover:bg-gray-50 hover:shadow-sm active:translate-y-0">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    {{-- Picker modals live outside the max-w-2xl form column so the overlay covers the full viewport. --}}
    @include('admin.homepage-sections._picker-modal', [
        'type' => 'coupon',
        'title' => 'Select Coupons',
        'max' => \App\Models\HomepageSection::MAX_OFFERS,
        'resultsUrl' => $pickerResultsUrl,
        'showCategory' => false, 'showStore' => true, 'showBadge' => false, 'showSearch' => true,
    ])
    @include('admin.homepage-sections._picker-modal', [
        'type' => 'deal',
        'title' => 'Select Deals',
        'max' => \App\Models\HomepageSection::MAX_OFFERS,
        'resultsUrl' => $pickerResultsUrl,
        'showCategory' => false, 'showStore' => true, 'showBadge' => false, 'showSearch' => true,
    ])
    @include('admin.homepage-sections._picker-modal', [
        'type' => 'mixed',
        'title' => 'Select Coupons & Deals',
        'max' => \App\Models\HomepageSection::MAX_OFFERS,
        'resultsUrl' => $pickerResultsUrl,
        'showCategory' => false, 'showStore' => true, 'showBadge' => false, 'showSearch' => true,
    ])
    @include('admin.homepage-sections._picker-modal', [
        'type' => 'store',
        'title' => 'Select Stores',
        'max' => \App\Models\HomepageSection::MAX_STORES,
        'resultsUrl' => $pickerResultsUrl,
        'showCategory' => true, 'showStore' => false, 'showBadge' => false, 'showSearch' => true,
    ])
    @include('admin.homepage-sections._picker-modal', [
        'type' => 'ranked',
        'title' => 'Select Coupons/Deals to Rank',
        'max' => \App\Models\HomepageSection::MAX_OFFERS,
        'resultsUrl' => $pickerResultsUrl,
        'showCategory' => false, 'showStore' => true, 'showBadge' => false, 'showSearch' => true,
    ])
    @include('admin.homepage-sections._picker-modal', [
        'type' => 'categories',
        'title' => 'Select Categories',
        'max' => \App\Models\HomepageSection::MAX_CATEGORIES,
        'resultsUrl' => $pickerResultsUrl,
        'showCategory' => false, 'showStore' => false, 'showBadge' => false, 'showSearch' => true,
    ])
@endsection
