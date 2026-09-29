@extends('admin.layouts.app')

@section('title', 'Regions')

@push('head')
    @vite(['resources/js/region-copy.js'])
@endpush

@section('content')
    @if (session('copy_result'))
        @php($result = session('copy_result'))
        <div class="mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-sm font-semibold text-gray-900">
                Copy result: {{ $result['source_name'] }} &rarr; {{ $result['new_name'] }}
                <span class="font-normal text-gray-400">(disabled until published — review and publish it from the list below)</span>
            </p>
            @if (empty($result['modules']))
                <p class="mt-2 text-sm text-gray-500">No modules were selected — only a bare region shell (default menus/pages, no content) was created.</p>
            @else
                <ul class="mt-2 space-y-1 text-sm">
                    @foreach ($result['modules'] as $module => $outcome)
                        <li class="flex items-center gap-2">
                            @if ($outcome['status'] === 'success')
                                <span class="text-emerald-600">&#10003;</span>
                                <span class="text-gray-700">{{ \App\Support\RegionCopier::MODULES[$module] ?? $module }} copied successfully.</span>
                            @else
                                <span class="text-red-600">&#10007;</span>
                                <span class="text-gray-700">{{ \App\Support\RegionCopier::MODULES[$module] ?? $module }} failed: {{ $outcome['message'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

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
                            <button type="button" data-copy-region-open data-copy-url="{{ route('admin.regions.copy', $region) }}" data-region-name="{{ $region->name }}"
                                    class="ml-3 font-medium text-emerald-600 hover:text-emerald-700">Copy</button>
                            <a href="{{ route('admin.regions.edit', $region) }}" class="ml-3 font-medium text-emerald-600 hover:text-emerald-700">Edit</a>
                            @unless ($region->is_default)
                                <a href="{{ route('admin.regions.confirm-delete', $region) }}" class="ml-3 font-medium text-red-600 hover:text-red-700">Delete</a>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No regions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div data-copy-region-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 p-4">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
            <h2 class="text-lg font-semibold text-gray-900">Copy <span data-copy-region-source></span></h2>
            <p class="mt-1 text-sm text-gray-500">Creates a brand-new region (disabled until you publish it) and copies whichever of the modules below you select — each runs and can fail independently of the others.</p>

            <form data-copy-region-form method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" name="code" required minlength="2" maxlength="4" pattern="[A-Za-z]+" placeholder="e.g. fr"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">2-4 letters, must be unique across all regions.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" required maxlength="255" placeholder="e.g. France"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <p class="mt-1 text-xs text-gray-400">Must also be unique across all regions.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">What to copy</label>
                    <div class="space-y-2 rounded-md border border-gray-200 p-3">
                        <label class="flex items-start gap-2">
                            <input type="checkbox" name="modules[]" value="blogs" checked class="mt-0.5 rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                            <span>
                                <span class="block text-sm font-medium text-gray-800">Blogs</span>
                                <span class="block text-xs text-gray-400">Blog categories and all other dependent taxonomies are always copied along with the blogs.</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="checkbox" name="modules[]" value="stores" checked class="mt-0.5 rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                            <span>
                                <span class="block text-sm font-medium text-gray-800">Stores &amp; Promotions</span>
                                <span class="block text-xs text-gray-400">Store/promotion categories and all other dependent taxonomies (store suffix, promo features, etc.) are always copied along with them.</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="checkbox" name="modules[]" value="config" checked class="mt-0.5 rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                            <span>
                                <span class="block text-sm font-medium text-gray-800">All Configurations</span>
                                <span class="block text-xs text-gray-400">URL schemes, menus, general settings, logos, page contents, page SEO settings, scripts, and all other configuration items.</span>
                            </span>
                        </label>
                    </div>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit" data-copy-region-submit class="flex-1 rounded-md bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-600">
                        Copy Region
                    </button>
                    <button type="button" data-copy-region-cancel class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
                <p data-copy-region-progress class="hidden text-xs text-gray-400">Copying region — this can take a little while for a large region, please don't close this tab...</p>
            </form>
        </div>
    </div>
@endsection
