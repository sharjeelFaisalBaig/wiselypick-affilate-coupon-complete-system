<div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
    <table class="w-full text-left text-sm">
        <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3">Store Name</th>
                <th class="px-4 py-3">Category</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($stores as $store)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <a href="{{ route('admin.stores.edit', $store) }}" data-no-ajax class="hover:text-emerald-600 hover:underline">{{ $store->name }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $store->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ $store->urlFor($activeRegion) }}" target="_blank" rel="noopener" class="font-medium text-gray-600 hover:text-gray-800" data-no-ajax>View</a>
                        <a href="{{ route('admin.stores.edit', $store) }}" class="ml-3 font-medium text-emerald-600 hover:text-emerald-700" data-no-ajax>Edit</a>
                        <form action="{{ route('admin.stores.destroy', $store) }}" method="POST" class="ml-3 inline"
                              onsubmit="return confirm('Delete this store? This is only possible if it has no active coupons or deals.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-medium text-red-600 hover:text-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">No stores found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $stores->links() }}</div>
