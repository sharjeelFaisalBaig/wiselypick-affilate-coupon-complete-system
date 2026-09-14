{{--
    Expects: $type, $title, $max, $resultsUrl, $showCategory, $showStore,
    $showBadge, $showSearch. Uses the page-scoped $storeCategoryOptions,
    $storeOptions, $badgeOptions already loaded for the form.
--}}
<div data-picker-modal="{{ $type }}" data-results-url="{{ $resultsUrl }}" data-max="{{ $max }}"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 p-4">
    <div class="flex h-[85vh] w-full max-w-5xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
            <button type="button" data-picker-close class="text-gray-400 hover:text-gray-600" aria-label="Close">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex min-h-0 flex-1">
            <div class="flex min-h-0 flex-1 flex-col border-r border-gray-200">
                <div data-picker-filters class="flex flex-wrap gap-2 border-b border-gray-200 bg-gray-50 px-6 py-3">
                    @if ($showCategory)
                        <div class="w-full sm:w-56">
                            <select data-filter="filter_category_id" data-select2-enable data-placeholder="All Categories" class="w-full rounded-md border-gray-300 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">All Categories</option>
                                @foreach ($storeCategoryOptions as $category)
                                    <option value="{{ $category->id }}">{{ $category->breadcrumbLabel() }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if ($showStore)
                        <div class="w-full sm:w-56">
                            <select data-filter="filter_store_id" data-select2-enable data-placeholder="All Stores" class="w-full rounded-md border-gray-300 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">All Stores</option>
                                @foreach ($storeOptions as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if ($showBadge)
                        <div class="w-full sm:w-56">
                            <select data-filter="filter_badge_id" data-select2-enable data-placeholder="All Badges" class="w-full rounded-md border-gray-300 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">All Badges</option>
                                @foreach ($badgeOptions as $badge)
                                    <option value="{{ $badge->id }}">{{ $badge->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if ($showSearch)
                        <input type="text" data-filter="filter_search" placeholder="Search..."
                               class="min-w-[10rem] flex-1 rounded-md border-gray-300 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @endif
                </div>

                <div data-picker-results class="flex-1 divide-y divide-gray-100 overflow-y-auto"></div>
            </div>

            <aside class="flex w-72 shrink-0 flex-col bg-gray-50">
                <div class="border-b border-gray-200 px-4 py-3">
                    <p class="text-sm font-semibold text-gray-900">
                        Selected (<span data-picker-count>0</span>/{{ $max }})
                    </p>
                </div>
                <ul data-picker-staged class="flex-1 space-y-2 overflow-y-auto p-4"></ul>
                <div class="flex gap-2 border-t border-gray-200 p-4">
                    <button type="button" data-picker-save
                            class="flex-1 rounded-md bg-emerald-500 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-600">
                        Save
                    </button>
                    <button type="button" data-picker-cancel
                            class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </aside>
        </div>
    </div>
</div>
