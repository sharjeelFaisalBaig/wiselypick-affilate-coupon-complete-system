<!DOCTYPE html>
<html lang="en" class="h-full bg-white">
<head>
    @include('public.partials.script-injections', ['placement' => 'head_start', 'pageType' => $pageType ?? 'home', 'storeId' => $storeId ?? null])
    {!! $region->head_start_script !!}
    {{-- The current page's OWN script fields (a store's, a static page's, or
         one of the 4 Fixed Pages' own) — every controller that has one
         passes it as $currentPageScripts specifically, never reused as
         $store/$page/etc: those get reassigned constantly by ordinary
         @foreach loops elsewhere in the SAME compiled template (e.g. home's
         "Trending Stores" section loops `as $store`), and since an
         @extends child view and its layout share one flat PHP variable
         scope, a leftover loop value under a generic name would otherwise
         leak in here and get picked up instead of the real page settings. --}}
    {!! ($currentPageScripts ?? null)?->head_start_script ?? '' !!}

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if ($region->favicon_path)
        <link rel="icon" href="{{ Storage::url($region->favicon_path) }}">
    @endif

    <title>{{ $seoTitle ?? ($region->name . ' Coupons, Promo Codes & Deals') }}</title>
    <meta name="description" content="{{ $seoDescription ?? 'Verified coupon codes, promo codes and deals for ' . $region->name . '.' }}">
    <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">
    <meta name="robots" content="{{ ($robotsIndex ?? true) ? 'index' : 'noindex' }},{{ ($robotsFollow ?? true) ? 'follow' : 'nofollow' }}">

    <meta property="og:title" content="{{ $ogTitle ?? ($seoTitle ?? $region->name . ' Coupons, Promo Codes & Deals') }}">
    <meta property="og:description" content="{{ $seoDescription ?? '' }}">
    @if (!empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    @stack('schema')
    {!! ($currentPageScripts ?? null)?->schema_script ?? '' !!}

    @fonts
    {{-- select2-init.js (~156KB, bundles jQuery) is intentionally NOT here —
         only /stores, /coupons, and /blogs (its mobile category filter)
         actually render a Select2 field on the public site; every other
         page (home, blog detail, store detail, static pages...) pushes
         nothing and never loads it. Those pages pull it in themselves via
         @push('head'). --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/ajax-filters.js', 'resources/js/autosuggest.js', 'resources/js/scroll-reveal.js'])
    {{-- Per-region brand theme colors (General Settings): re-tints Tailwind's
         built-in emerald/teal tokens and the app's own --color-deal tokens by
         overriding their CSS custom-property VALUES at runtime — the utility
         CLASS NAMES (bg-emerald-500, text-deal-700, ...) never change
         anywhere in the blade templates, only what they resolve to. This
         :root block must render after app.css so it wins the cascade. --}}
    @include('public.partials.theme-colors')
    @stack('head')

    @include('public.partials.script-injections', ['placement' => 'head_end', 'pageType' => $pageType ?? 'home', 'storeId' => $storeId ?? null])
    {!! $region->head_end_script !!}
    {!! ($currentPageScripts ?? null)?->head_end_script ?? '' !!}
</head>
<body class="flex min-h-full flex-col bg-white text-gray-900 antialiased">
    @include('public.partials.script-injections', ['placement' => 'body_start', 'pageType' => $pageType ?? 'home', 'storeId' => $storeId ?? null])
    {!! $region->body_start_script !!}
    {!! ($currentPageScripts ?? null)?->body_start_script ?? '' !!}

    @include('public.partials.header')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('public.partials.footer')

    @include('public.partials.script-injections', ['placement' => 'body_end', 'pageType' => $pageType ?? 'home', 'storeId' => $storeId ?? null])
    {!! $region->body_end_script !!}
    {!! ($currentPageScripts ?? null)?->body_end_script ?? '' !!}

    {{-- Rendered at true body level (outside <main> and any [data-reveal]
         ancestor) so each offer's reveal modal is never trapped inside a
         transformed containing block — see the docblock above. --}}
    @stack('modals')
</body>
</html>
