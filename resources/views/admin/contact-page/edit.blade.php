@extends('admin.layouts.app')

@section('title', 'Contact Page — Question Agendas')

@section('content')
    <p class="mb-4 text-sm text-gray-500">
        Page title, short description, and SEO for the Contact page are managed under
        <a href="{{ route('admin.static-pages.index') }}" class="text-emerald-600 hover:underline">Static Pages → Contact</a>.
        This screen manages the "I have a question about" radio options shown on that form — drag to reorder.
    </p>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Label</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody data-sortable data-sortable-url="{{ route('admin.contact-page.agendas.reorder') }}" class="divide-y divide-gray-100">
                    @forelse ($agendas as $agenda)
                        <tr data-sort-id="{{ $agenda->id }}" class="cursor-move hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $agenda->label }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $agenda->description }}</td>
                            <td class="px-4 py-3">
                                <form action="{{ route('admin.contact-page.agendas.update', $agenda) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="label" value="{{ $agenda->label }}">
                                    <input type="hidden" name="description" value="{{ $agenda->description }}">
                                    <button type="submit" name="is_active" value="{{ $agenda->is_active ? '0' : '1' }}"
                                            class="rounded-full px-2 py-1 text-xs font-medium {{ $agenda->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $agenda->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form action="{{ route('admin.contact-page.agendas.destroy', $agenda) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Remove this agenda option?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 hover:text-red-700">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No agenda options yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h2 class="mb-3 text-sm font-semibold text-gray-900">Add Agenda Option</h2>
            <form method="POST" action="{{ route('admin.contact-page.agendas.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Value (internal key, letters/dashes/underscores)</label>
                    <input type="text" name="value" required
                           class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Label</label>
                    <input type="text" name="label" required
                           class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500">Description</label>
                    <input type="text" name="description"
                           class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                </div>
                <button type="submit" class="w-full rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-md active:translate-y-0">
                    Add Option
                </button>
            </form>
        </div>
    </div>
@endsection
