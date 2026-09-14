{{--
    Expects $stores (Collection<Store>, with category loaded), $reorderUrl,
    $emptyLabel, $suggestScope. Shared by the Featured/Popular/Pending tabs on
    the classification screen — only the data source, reorder endpoint, and
    suggest scope differ.
--}}
<div class="mb-3">
    <input type="text" data-row-filter placeholder="Search this list..." autocomplete="off"
           data-autosuggest-endpoint="{{ route('admin.stores.suggest', ['scope' => $suggestScope]) }}"
           class="block w-full max-w-xs rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:w-64">
</div>
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" data-reorder-loading-target>
    <table class="w-full text-left text-sm">
        <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
            <tr>
                <th class="w-8 px-2 py-3"></th>
                <th class="px-4 py-3">Store Name</th>
                <th class="px-4 py-3">Category</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Added On</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody data-sortable data-sortable-url="{{ $reorderUrl }}" class="divide-y divide-gray-100">
            @forelse ($stores as $store)
                <tr data-sort-id="{{ $store->id }}" class="cursor-move hover:bg-gray-50">
                    <td class="px-2 py-3 text-center text-gray-300" aria-hidden="true">⠿</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            @if ($store->logo_path)
                                <img src="{{ Storage::url($store->logo_path) }}" alt="" width="28" height="28" class="h-7 w-7 shrink-0 rounded object-contain">
                            @else
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-gray-100 text-xs font-semibold text-gray-400">{{ mb_substr($store->name, 0, 1) }}</span>
                            @endif
                            <span class="font-medium text-gray-900">{{ $store->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if ($store->category)
                            <span class="rounded-full bg-sky-50 px-2 py-1 text-xs font-medium text-sky-700">{{ $store->category->name }}</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($store->is_active)
                            <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Active</span>
                        @else
                            <span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700">Pending</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $store->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.stores.edit', $store) }}" class="font-medium text-emerald-600 hover:text-emerald-700" data-no-ajax>Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">{{ $emptyLabel }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
