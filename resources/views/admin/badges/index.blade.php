@extends('admin.layouts.app')

@section('title', 'Badges')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">A promotion can carry at most 2 of these. Badges assigned to active promotions can't be deleted.</p>
        <a href="{{ route('admin.badges.create') }}" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
            + Add Badge
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Badge Name</th>
                    <th class="px-4 py-3">Preview</th>
                    <th class="px-4 py-3">Assigned Promos</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($badges as $badge)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $badge->name }}</td>
                        <td class="px-4 py-3"><span class="rounded px-1.5 py-0.5 text-xs font-medium {{ $badge->style() ? '' : $badge->classes() }}" @if ($badge->style()) style="{{ $badge->style() }}" @endif>{{ $badge->name }}</span></td>
                        <td class="px-4 py-3 text-gray-500">{{ $badge->offers_count }}</td>
                        <td class="px-4 py-3">
                            @if ($badge->is_active)
                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Active</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.badges.edit', $badge) }}" class="font-medium text-emerald-600 hover:text-emerald-700">Edit</a>
                            <form action="{{ route('admin.badges.destroy', $badge) }}" method="POST" class="ml-3 inline"
                                  onsubmit="return confirm('Delete this badge?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No badges yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
