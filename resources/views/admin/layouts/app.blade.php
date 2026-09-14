<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Coupons Platform Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/drag-sort.js', 'resources/js/offer-form.js', 'resources/js/script-injection-form.js', 'resources/js/faq-builder.js', 'resources/js/homepage-section-form.js', 'resources/js/homepage-section-picker.js', 'resources/js/ajax-filters.js', 'resources/js/autosuggest.js', 'resources/js/table-row-filter.js', 'resources/js/select2-init.js'])
    @stack('head')
</head>
<body data-admin-shell class="text-gray-900 antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 hidden w-64 shrink-0 flex-col bg-slate-900 lg:static lg:z-auto lg:flex">
            <div class="lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col">
                <div class="flex h-16 shrink-0 items-center gap-2 border-b border-slate-800 px-6">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-md shadow-emerald-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M13 2 3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                    </span>
                    <span class="font-display text-lg font-bold text-white">Coupons<span class="text-emerald-400">CMS</span></span>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm">
                @php
                    // Grouped so nothing manageable ends up reachable from two
                    // different places — the Pages group is the ONLY entry
                    // point for Homepage/Contact/Static Pages/Page Settings,
                    // each of which used to also sit here as its own top-level item.
                    // Any group with more than one item becomes a click-to-expand
                    // accordion (parent label + chevron); a single-item group
                    // renders as a plain flat link — nothing to reveal.
                    $navGroups = [
                        [
                            'label' => null,
                            'items' => [
                                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard'],
                            ],
                        ],
                        [
                            'label' => 'Categories',
                            'items' => [
                                ['label' => 'Add Category', 'route' => 'admin.categories.create', 'match' => 'admin.categories.create', 'can' => 'full-admin-access'],
                                ['label' => 'All Categories', 'route' => 'admin.categories.index', 'match' => ['admin.categories.index', 'admin.categories.edit'], 'can' => 'full-admin-access'],
                            ],
                        ],
                        [
                            'label' => 'Stores',
                            'items' => [
                                ['label' => 'Add Store', 'route' => 'admin.stores.create', 'match' => 'admin.stores.create', 'can' => 'manage-stores-coupons'],
                                ['label' => 'All Stores', 'route' => 'admin.stores.index', 'match' => ['admin.stores.index', 'admin.stores.edit'], 'can' => 'manage-stores-coupons'],
                                ['label' => 'Featured & Popular', 'route' => 'admin.stores.classification', 'match' => 'admin.stores.classification', 'can' => 'manage-stores-coupons'],
                            ],
                        ],
                        [
                            'label' => 'Coupons',
                            'items' => [
                                ['label' => 'Add Coupon', 'route' => 'admin.offers.create', 'match' => 'admin.offers.create', 'can' => 'manage-stores-coupons'],
                                ['label' => 'All Coupons', 'route' => 'admin.offers.index', 'match' => ['admin.offers.index', 'admin.offers.edit'], 'can' => 'manage-stores-coupons'],
                            ],
                        ],
                        [
                            'label' => 'Coupon Features',
                            'items' => [
                                ['label' => 'Add Coupon Feature', 'route' => 'admin.badges.create', 'match' => 'admin.badges.create', 'can' => 'full-admin-access'],
                                ['label' => 'All Coupon Features', 'route' => 'admin.badges.index', 'match' => ['admin.badges.index', 'admin.badges.edit'], 'can' => 'full-admin-access'],
                            ],
                        ],
                        [
                            'label' => 'Store Suffixes',
                            'items' => [
                                ['label' => 'Add Store Suffix', 'route' => 'admin.store-suffixes.create', 'match' => 'admin.store-suffixes.create', 'can' => 'full-admin-access'],
                                ['label' => 'All Store Suffixes', 'route' => 'admin.store-suffixes.index', 'match' => ['admin.store-suffixes.index', 'admin.store-suffixes.edit'], 'can' => 'full-admin-access'],
                            ],
                        ],
                        [
                            'label' => 'Pages',
                            'items' => [
                                // The ONLY entry point for page management — Homepage
                                // Sections, Page Settings, Contact Page agendas, and
                                // Static Pages are all reached from here, never as
                                // separate top-level items, so nothing is manageable
                                // from two different places in the sidebar.
                                ['label' => 'Pages', 'route' => 'admin.pages-overview.index', 'match' => ['admin.pages-overview.*', 'admin.homepage-sections.*', 'admin.page-settings.*', 'admin.contact-page.*', 'admin.static-pages.*'], 'can' => 'full-admin-access'],
                            ],
                        ],
                        [
                            'label' => 'Blogs',
                            'items' => [
                                ['label' => 'Blog Categories', 'route' => 'admin.blog-categories.index', 'match' => 'admin.blog-categories.*', 'can' => 'manage-blogs'],
                                ['label' => 'Add Blog', 'route' => 'admin.blogs.create', 'match' => 'admin.blogs.create', 'can' => 'manage-blogs'],
                                ['label' => 'All Blogs', 'route' => 'admin.blogs.index', 'match' => ['admin.blogs.index', 'admin.blogs.edit'], 'can' => 'manage-blogs'],
                            ],
                        ],
                        [
                            'label' => 'Regions',
                            'items' => [
                                ['label' => 'Add Region', 'route' => 'admin.regions.create', 'match' => 'admin.regions.create', 'can' => 'full-admin-access'],
                                ['label' => 'All Regions', 'route' => 'admin.regions.index', 'match' => ['admin.regions.index', 'admin.regions.edit'], 'can' => 'full-admin-access'],
                                ['label' => 'Menus', 'route' => 'admin.menus.index', 'match' => 'admin.menus.*', 'can' => 'full-admin-access'],
                                ['label' => 'General Settings', 'route' => 'admin.general-settings.edit', 'match' => 'admin.general-settings.*', 'can' => 'full-admin-access'],
                            ],
                        ],
                        [
                            'label' => 'System',
                            'items' => [
                                ['label' => 'Script Injections', 'route' => 'admin.script-injections.index', 'match' => 'admin.script-injections.*', 'can' => 'full-admin-access'],
                                ['label' => 'Affiliate Networks', 'route' => 'admin.affiliate-networks.index', 'match' => 'admin.affiliate-networks.*', 'can' => 'full-admin-access'],
                                ['label' => 'Contact Messages', 'route' => 'admin.contact-messages.index', 'match' => 'admin.contact-messages.*', 'can' => 'full-admin-access'],
                            ],
                        ],
                        [
                            'label' => 'Users',
                            'items' => [
                                ['label' => 'Add User', 'route' => 'admin.users.create', 'match' => 'admin.users.create', 'can' => 'manage-users'],
                                ['label' => 'All Users', 'route' => 'admin.users.index', 'match' => ['admin.users.index', 'admin.users.edit'], 'can' => 'manage-users'],
                            ],
                        ],
                        [
                            'label' => null,
                            'items' => [
                                ['label' => 'Admin Panel URL', 'route' => 'admin.admin-settings.edit', 'match' => 'admin.admin-settings.*', 'can' => 'manage-users'],
                            ],
                        ],
                    ];
                @endphp

                @foreach ($navGroups as $groupIndex => $group)
                    @php
                        // Route::has() alone only proves the route is registered —
                        // it says nothing about whether the current user is allowed
                        // to use it, so a 'can' key (checked via the same Gate the
                        // route itself is guarded by) is required to actually hide
                        // Superadmin-only items from a Manager.
                        $visibleItems = collect($group['items'])->filter(
                            fn ($item) => Route::has($item['route']) && (! isset($item['can']) || \Illuminate\Support\Facades\Gate::allows($item['can']))
                        );
                        $groupIsActive = $visibleItems->contains(fn ($item) => request()->routeIs(...(array) $item['match']));
                        $panelId = "nav-group-{$groupIndex}";
                    @endphp
                    @if ($visibleItems->isNotEmpty())
                        <div>
                            @if ($group['label'] && $visibleItems->count() > 1)
                                <button type="button" data-toggle="#{{ $panelId }}"
                                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide {{ $groupIsActive ? 'text-emerald-400' : 'text-slate-400 hover:text-slate-200' }}">
                                    {{ $group['label'] }}
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 shrink-0 transition-transform"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                                </button>
                                <div id="{{ $panelId }}" class="space-y-0.5 py-0.5 {{ $groupIsActive ? '' : 'hidden' }}">
                                    @foreach ($visibleItems as $item)
                                        <a href="{{ route($item['route']) }}"
                                           class="block rounded-lg py-2 pl-6 pr-3 font-medium {{ request()->routeIs(...(array) $item['match']) ? 'bg-emerald-500/15 text-emerald-400 shadow-sm ring-1 ring-inset ring-emerald-500/20' : 'text-slate-300 hover:translate-x-0.5 hover:bg-slate-800 hover:text-white' }}">
                                            {{ $item['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                @if ($group['label'])
                                    <p class="mb-1 px-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $group['label'] }}</p>
                                @endif
                                <div class="space-y-0.5">
                                    @foreach ($visibleItems as $item)
                                        <a href="{{ route($item['route']) }}"
                                           class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs(...(array) $item['match']) ? 'bg-emerald-500/15 text-emerald-400 shadow-sm ring-1 ring-inset ring-emerald-500/20' : 'text-slate-300 hover:translate-x-0.5 hover:bg-slate-800 hover:text-white' }}">
                                            {{ $item['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
                </nav>
            </div>
        </aside>

        <div class="flex min-h-screen min-w-0 flex-1 flex-col">
            <!-- Topbar -->
            <header class="sticky top-0 z-30 flex h-16 min-w-0 items-center justify-between gap-2 border-b border-gray-200 bg-white/95 px-3 shadow-sm backdrop-blur-sm sm:px-6">
                <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                    <button type="button" data-toggle="#admin-sidebar" aria-label="Open menu"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-gray-200 text-gray-600 lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        </svg>
                    </button>
                    <h1 class="truncate font-display text-base font-semibold sm:text-lg">@yield('title', 'Dashboard')</h1>
                </div>

                <div class="flex shrink-0 items-center gap-2 sm:gap-4">
                    @if(isset($allRegions) && isset($activeRegion))
                        <form method="POST" action="{{ route('admin.region.switch') }}" class="w-32 sm:w-56">
                            @csrf
                            <select name="region_id" data-select2-enable data-auto-submit
                                    class="w-full rounded-md border-gray-300 text-xs focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                                @foreach ($allRegions as $r)
                                    <option value="{{ $r->id }}" @selected($activeRegion->id === $r->id)
                                        @if ($r->flag_path) data-flag="{{ Storage::url($r->flag_path) }}" @endif>{{ strtoupper($r->code) }} — {{ $r->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif

                    <div class="relative shrink-0">
                        <button type="button" data-toggle="#user-menu" class="flex items-center gap-1 text-sm font-medium text-gray-700 sm:gap-2">
                            <span class="hidden sm:inline">{{ auth()->user()?->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 shrink-0"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                        </button>
                        <div id="user-menu" class="animate-fade-in hidden absolute right-0 z-50 mt-2 w-40 rounded-md border border-gray-200 bg-white py-1 shadow-lg">
                            <a href="{{ route('admin.profile.edit') }}" class="block px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50">My Profile</a>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50">Log out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="min-w-0 flex-1 p-4 sm:p-6">
                @if (session('status'))
                    <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
