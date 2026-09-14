@extends('admin.layouts.app')

@section('title', 'Script Injections')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Raw HTML/JS snippets injected into the storefront head or before &lt;/body&gt;.</p>
        <a href="{{ route('admin.script-injections.create') }}" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
            + Add Script
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Placement</th>
                    <th class="px-4 py-3">Target</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($injections as $injection)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $injection->name }}</td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ match ($injection->placement) {
                                'head_start' => 'Start of <head>',
                                'head_end' => 'End of <head>',
                                'body_start' => 'Start of <body>',
                                'body_end' => 'End of <body>',
                            } }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            @if ($injection->target_type === 'all_pages') All Pages
                            @elseif ($injection->target_type === 'specific_pages') {{ $injection->pageTargets->count() }} page type(s)
                            @else {{ $injection->stores_count }} store(s) @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($injection->is_active)
                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Active</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.script-injections.edit', $injection) }}" class="font-medium text-emerald-600 hover:text-emerald-700">Edit</a>
                            <form action="{{ route('admin.script-injections.destroy', $injection) }}" method="POST" class="ml-3 inline"
                                  onsubmit="return confirm('Delete this script injection?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No script injections yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $injections->links() }}</div>
@endsection
