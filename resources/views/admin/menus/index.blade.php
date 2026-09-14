@extends('admin.layouts.app')

@section('title', 'Menus')

@section('content')
    <p class="mb-6 text-sm text-gray-500">
        Menu structure is fixed (1 Header + 3 Footer menus) — you can add, remove, and reorder items within each, but not the menus themselves. The blog section runs its own independent set below, entirely separate from the rest of the site's.
    </p>

    @foreach (\App\Models\Menu::SCOPES as $scope => $scopeLabel)
        <div class="{{ !$loop->first ? 'mt-8' : '' }}">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">{{ $scopeLabel }}</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($menusByScope->get($scope, collect()) as $menu)
                    <a href="{{ route('admin.menus.edit', $menu) }}"
                       class="card-lift rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-emerald-300">
                        <p class="font-semibold text-gray-900">{{ $menu->name }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ $menu->items_count }} {{ $menu->items_count === 1 ? 'item' : 'items' }}</p>
                        <span class="mt-3 inline-block text-sm font-medium text-emerald-600">Manage items &rarr;</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
@endsection
