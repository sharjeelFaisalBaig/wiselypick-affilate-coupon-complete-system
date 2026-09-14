@extends('admin.layouts.app')

@section('title', 'Pages')

@section('content')
    <p class="mb-4 text-sm text-gray-500">
        The 7 fixed frontend pages. Pages can't be added or removed — each row deep-links to where its content and SEO are actually managed.
    </p>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Page</th>
                    <th class="px-4 py-3">Path</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Manage</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($rows as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $row['name'] }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $row['path'] }}</td>
                        <td class="px-4 py-3">
                            @if ($row['is_active'])
                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Published</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($row['content_edit_route'])
                                <a href="{{ $row['content_edit_route'] }}" class="font-medium text-emerald-600 hover:text-emerald-700">{{ $row['content_edit_label'] ?? 'Content' }}</a>
                            @endif
                            @if (!empty($row['seo_edit_route']))
                                <a href="{{ $row['seo_edit_route'] }}" class="ml-3 font-medium text-emerald-600 hover:text-emerald-700">{{ $row['seo_edit_label'] ?? 'SEO' }}</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
