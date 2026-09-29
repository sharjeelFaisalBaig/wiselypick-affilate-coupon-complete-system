@extends('admin.layouts.app')

@section('title', 'Store Slug Suffixes')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Selectable in the Store form's Slug Suffix dropdown. A suffix assigned to one or more stores can't be deleted.</p>
        <a href="{{ route('admin.store-slug-suffixes.create') }}" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
            + Add Slug Suffix
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Suffix</th>
                    <th class="px-4 py-3">Assigned Stores</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($storeSlugSuffixes as $storeSlugSuffix)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $storeSlugSuffix->value }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $storeSlugSuffix->stores_count }}</td>
                        <td class="px-4 py-3">
                            @if ($storeSlugSuffix->is_active)
                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Active</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.store-slug-suffixes.edit', $storeSlugSuffix) }}" class="font-medium text-emerald-600 hover:text-emerald-700">Edit</a>
                            <form action="{{ route('admin.store-slug-suffixes.destroy', $storeSlugSuffix) }}" method="POST" class="ml-3 inline"
                                  onsubmit="return confirm('Delete this slug suffix?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No slug suffixes yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
