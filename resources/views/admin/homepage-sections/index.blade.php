@extends('admin.layouts.app')

@section('title', 'Homepage Sections')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Drag rows to reorder how sections appear on the homepage.</p>
        <a href="{{ route('admin.homepage-sections.create') }}"
           class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
            + Add Section
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Items</th>
                    <th class="px-4 py-3">CTA</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody data-sortable data-sortable-url="{{ route('admin.homepage-sections.reorder') }}" class="divide-y divide-gray-100">
                @forelse ($sections as $section)
                    <tr data-sort-id="{{ $section->id }}" class="cursor-move hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $section->title }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ ['coupon' => 'Coupon', 'deal' => 'Deal', 'mixed' => 'Mixed Deals/Coupons', 'store' => 'Trending Stores', 'ranked' => 'Top Coupons (Ranked)', 'categories' => 'Category Icon Grid'][$section->content_type] ?? ucfirst($section->content_type) }}</td>
                        <td class="px-4 py-3 text-gray-500">
                            @if ($section->content_type === 'store')
                                {{ $section->stores_count }} / {{ \App\Models\HomepageSection::MAX_STORES }} stores
                            @elseif ($section->content_type === 'categories')
                                {{ $section->categories_count }} / {{ \App\Models\HomepageSection::MAX_CATEGORIES }} categories
                            @else
                                {{ $section->offers_count }} / {{ \App\Models\HomepageSection::MAX_OFFERS }} offers
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $section->cta_label ?: '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($section->is_active)
                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Active</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.homepage-sections.edit', $section) }}" class="font-medium text-emerald-600 hover:text-emerald-700">Edit</a>
                            <form action="{{ route('admin.homepage-sections.destroy', $section) }}" method="POST" class="ml-3 inline"
                                  onsubmit="return confirm('Delete this homepage section?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No homepage sections yet — the homepage will show nothing until you add some.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
