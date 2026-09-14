@extends('admin.layouts.app')

@section('title', 'Regions')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Region is the root entity — every store, coupon, deal, blog, and page belongs to one.</p>
        <a href="{{ route('admin.regions.create') }}" class="rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
            + Add Region
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Prefix</th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Default</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($regions as $region)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-gray-700">/{{ $region->code }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $region->name }}</td>
                        <td class="px-4 py-3">
                            @if ($region->is_active)
                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">Enabled</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-500">Disabled</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($region->is_default)
                                <span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700">Default</span>
                            @else
                                <form action="{{ route('admin.regions.make-default', $region) }}" method="POST"
                                      onsubmit="return confirm('Make {{ $region->name }} the default region?');">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">Make Default</button>
                                </form>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form action="{{ route('admin.regions.toggle-active', $region) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="font-medium text-gray-600 hover:text-gray-900">
                                    {{ $region->is_active ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                            <a href="{{ route('admin.regions.edit', $region) }}" class="ml-3 font-medium text-emerald-600 hover:text-emerald-700">Edit</a>
                            <form action="{{ route('admin.regions.destroy', $region) }}" method="POST" class="ml-3 inline"
                                  onsubmit="return confirm('Delete this region? This is only possible if nothing is assigned to it.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No regions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
