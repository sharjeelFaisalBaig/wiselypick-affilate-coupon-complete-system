@extends('admin.layouts.app')

@section('title', $menu->name . ' Menu')

@section('content')
    <div class="mb-4 flex items-center gap-3">
        <a href="{{ route('admin.menus.index') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">&larr; All Menus</a>
        <span class="rounded-full bg-sky-50 px-2 py-1 text-xs font-medium text-sky-700">{{ \App\Models\Menu::SCOPES[$menu->scope] ?? $menu->scope }}</span>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Link</th>
                        <th class="px-4 py-3">Target</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody data-sortable data-sortable-url="{{ route('admin.menus.items.reorder', $menu) }}" class="divide-y divide-gray-100">
                    @forelse ($items as $item)
                        <tr data-sort-id="{{ $item->id }}" class="cursor-move hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $item->title }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $item->url }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $item->target === 'new_tab' ? 'New Tab' : 'Same Tab' }}</td>
                            <td class="px-4 py-3 text-right">
                                <form action="{{ route('admin.menus.items.destroy', [$menu, $item]) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Remove this menu item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:text-red-700">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No items yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-3 text-sm font-semibold text-gray-900">Add Item</h2>
            <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Item Title</label>
                    <input type="text" name="title" required
                           class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Custom Link (URL)</label>
                    <input type="text" name="url" required placeholder="/exclusive or https://..."
                           class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Target</label>
                    <select name="target" data-select2-enable class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="same_tab">Same Tab</option>
                        <option value="new_tab">New Tab</option>
                    </select>
                </div>
                <button type="submit" class="w-full rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    Add Item
                </button>
            </form>
        </div>
    </div>
@endsection
