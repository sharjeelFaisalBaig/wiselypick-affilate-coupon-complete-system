@extends('public.layouts.app')

@section('content')
    @include('public.partials.page-header', ['heading' => "Coupons for {$category->name} Stores ".now()->format('Y')])
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mt-3 flex flex-wrap items-center gap-4">
            <div>
                <button type="button" data-toggle="#browse-categories" class="flex items-center gap-1 text-sm font-medium text-gray-700 hover:text-emerald-600">
                    {{ $category->name }} Stores
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                </button>
            </div>
        </div>

        <ul id="browse-categories" class="hidden mt-3 space-y-1 text-sm">
            @foreach ($allCategories as $otherCategory)
                <li><a href="{{ route('public.category', [$region->code, $otherCategory->slug]) }}" class="text-gray-600 hover:text-emerald-600 {{ $otherCategory->id === $category->id ? 'font-semibold text-emerald-600' : '' }}">{{ $otherCategory->name }}</a></li>
            @endforeach
        </ul>

        <div class="mt-6 grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
            @forelse ($stores as $store)
                <a href="{{ $store->urlFor($region) }}" class="flex items-center gap-3 rounded-lg border-b border-gray-100 px-2 py-3 hover:translate-x-1 hover:bg-gray-50">
                    @if ($store->logo_path)
                        <img src="{{ Storage::url($store->logo_path) }}" alt="{{ $store->name }}" width="48" height="48" loading="lazy" class="h-12 w-12 shrink-0 rounded object-contain">
                    @else
                        @include('public.partials.placeholder-image', ['class' => 'h-12 w-12 rounded'])
                    @endif
                    <div>
                        <p class="font-medium text-gray-900">{{ $store->name }} coupons</p>
                        <p class="text-xs text-gray-400">{{ $store->offers_count }} active code{{ $store->offers_count === 1 ? '' : 's' }}</p>
                    </div>
                </a>
            @empty
                <p class="text-gray-400">No stores in this category yet.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $stores->links() }}</div>

        @if ($allStoresGrouped->isNotEmpty())
            <h2 class="mt-12 text-lg font-bold text-gray-900">More {{ $category->name }} Stores, by Alphabet</h2>
            <div class="mt-4 grid grid-cols-2 gap-x-8 gap-y-6 sm:grid-cols-4">
                @foreach ($allStoresGrouped as $letter => $storesForLetter)
                    <div>
                        <p class="mb-2 font-bold text-gray-900">{{ $letter }}</p>
                        <ul class="space-y-1">
                            @foreach ($storesForLetter as $store)
                                <li><a href="{{ $store->urlFor($region) }}" class="text-sm text-gray-500 hover:text-emerald-600">{{ $store->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
