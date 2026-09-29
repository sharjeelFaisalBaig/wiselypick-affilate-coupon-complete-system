<?php

use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\OfferRedirectController;
use App\Http\Controllers\Public\PageRouterController;
use App\Http\Controllers\Public\PromoCodeController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\StoreDirectoryController;
use App\Http\Controllers\Public\SystemController;
use App\Models\Region;
use Illuminate\Support\Facades\Route;

// The default region's own `code` decides how "/" behaves (item 18):
// filled in, "/" redirects to "/{code}" (registered here, as before); left
// blank, that region is root-mounted instead — its entire route set (the
// $registerRegionRoutes group further down, including its own "/" via
// "{slug?}") lives directly at the bare site root, so THIS route must not
// be registered at all (it would permanently shadow that "/"). Queried once
// at route-registration time, same DB-backed-route-shape pattern as
// AdminSetting::panelPath() below — cleared via route:clear whenever the
// default region changes or its code is edited (see RegionController).
// Wrapped the same way as AdminSetting::panelPath() — on a brand-new install
// this file is still `require`'d (and this query still runs) during
// `artisan migrate` itself, before the `regions` table exists yet.
try {
    $defaultRegion = Region::where('is_active', true)->where('is_default', true)->first();
} catch (\Throwable) {
    $defaultRegion = null;
}

if (! $defaultRegion || $defaultRegion->code) {
    // Deliberately NOT `use ($defaultRegion)` — `route:cache` serializes
    // this closure (via laravel-serializable-closure) to a cached PHP file,
    // and a captured Eloquent model doesn't survive that round-trip cleanly
    // (breaks at unserialize() with an "incomplete object" error on the
    // very first cached request). Re-querying fresh here avoids capturing
    // any state at all, and is one indexed lookup either way.
    Route::get('/', function () {
        $default = Region::where('is_active', true)->where('is_default', true)->first()
            ?? Region::where('is_active', true)->orderBy('sort_order')->first();
        abort_if(! $default, 404);

        return redirect($default->publicUrl());
    })->name('public.root');
}

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('public.sitemap');
Route::get('robots.txt', [SitemapController::class, 'robots'])->name('public.robots');

// Render's free tier has no SSH/one-off job support, so this is the only
// way to run `optimize:clear` after a deploy — gated by DEPLOY_TOKEN, not
// truly open (see SystemController).
Route::get('system/optimize-clear', [SystemController::class, 'optimizeClear'])->name('system.optimize-clear');

// Admin routes are static prefixes ("admin/...") and must be registered
// before the dynamic {region} catch-all below — otherwise a request like
// GET /admin matches {region}="admin" first and never reaches the panel.
require __DIR__.'/admin.php';

// The full set of per-region public routes, shared verbatim by both the
// normal "/{region}/..." group and the root-mounted group right below it
// (item 18: the default region's own blank `code` makes IT live at the bare
// site root instead of under a prefix). Registered with no route names —
// nothing needs to reverse-route any of these: every outbound link is built
// via Region::publicUrl() (or a model's urlFor(), which itself calls
// publicUrl()), never route('public.category'|'public.suggest.*'|
// 'public.offer.redirect'|'public.page'|'public.contact.submit', ...).
$registerRegionRoutes = function () {
    Route::get('robots.txt', [SitemapController::class, 'regionRobots']);
    Route::get('sitemap.xml', [SitemapController::class, 'regionSitemap']);

    Route::get('category/{categorySlug}', [CategoryController::class, 'show']);

    Route::get('suggest/stores', [StoreDirectoryController::class, 'suggest']);
    Route::get('suggest/coupons', [PromoCodeController::class, 'suggest']);
    Route::get('suggest/blogs', [BlogController::class, 'suggest']);

    Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:contact-form');

    Route::get('go/{offer}', OfferRedirectController::class);

    // Catch-all for: the 4 fixed pages (home/stores/coupons/blogs, admin-
    // renameable per region via PageSetting.slug), every store detail page
    // (admin-renameable PER STORE via the StoreSlugPrefix/StoreSlugSuffix
    // taxonomies, default "store/{slug}"), every blog detail page (same idea
    // via BlogSlugPrefix/BlogSlugSuffix, default "blog/{slug}"), and every
    // static page (same idea via PageSlugPrefix/PageSlugSuffix, default no
    // prefix at all — there's no more fixed "/p/" route). None of these are
    // fixed literal routes anymore — see PageRouterController for the
    // resolution order — so this must stay LAST in the group, and the slug
    // constraint allows "/" for multi-segment prefixes/suffixes.
    Route::get('{slug?}', PageRouterController::class)->where('slug', '[a-z0-9\-\/]*');
};

// The {region} prefix normally accepts any 2-4 lowercase letters (matching
// an existing region or not — an unresolvable one is caught by the
// ModelNotFoundException handler below and redirected to the default
// region, a deliberate graceful-typo fallback). That has to narrow to just
// the OTHER regions' actual codes when the default region is root-mounted,
// though: a root-mounted route like "go/{offer}" sits at the bare site root
// with no prefix at all, and "go" itself is a perfectly valid 2-4-letter
// string — so the loose pattern would swallow
// e.g. "/go/332" as {region}="go" (an unresolvable code) before the
// root-mounted group below ever got a chance to match it as an offer
// redirect, breaking every one of those paths. Scoping the constraint to
// real codes only when it matters keeps the graceful-typo-redirect
// behavior fully intact for the (overwhelmingly common) non-root-mounted
// case, where no such collision can happen.
if ($defaultRegion && ! $defaultRegion->code) {
    $otherRegionCodes = Region::where('is_active', true)->whereNotNull('code')->pluck('code')->all();

    if ($otherRegionCodes) {
        Route::prefix('{region}')->middleware('public.region')
            ->where(['region' => '('.implode('|', array_map('preg_quote', $otherRegionCodes)).')'])
            ->group($registerRegionRoutes);
    }

    // Root-mounted copy of the exact same routes — see the "/" block above
    // (the two are always each other's mirror image: exactly one of them
    // exists for "/" at any given time). ResolvePublicRegion's fallback (no
    // {region} URI segment here to bind) resolves it straight to the
    // default region.
    Route::middleware('public.region')->group($registerRegionRoutes);
} else {
    Route::prefix('{region}')->middleware('public.region')->where(['region' => '[a-z]{2,4}'])->group($registerRegionRoutes);
}
