<?php

namespace App\Http\Middleware;

use App\Models\Blog;
use App\Models\GeneralSetting;
use App\Models\Menu;
use App\Models\PageSetting;
use App\Models\Region;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolvePublicRegion
{
    /**
     * By the time this middleware runs, Laravel's own SubstituteBindings
     * middleware has already resolved the {region} route segment into a
     * Region model via Region::resolveRouteBinding() (404s automatically
     * if the code doesn't exist or isn't active). This just shares it
     * with views and request attributes for convenience.
     *
     * The root-mounted route group (routes/web.php — the default region's
     * own bare-domain copy of these routes, used when its `code` is left
     * blank per item 18) has no {region} URI segment at all, so there's
     * nothing for SubstituteBindings to resolve there — fall back to
     * looking up that region directly and bind it onto the route the same
     * way, so every controller downstream (all of which type-hint
     * `Region $region` as a route-bound parameter) keeps working unchanged.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Region|null $region */
        $region = $request->route('region');

        if (! $region instanceof Region) {
            $region = Region::where('is_active', true)->where('is_default', true)->whereNull('code')->first();
            abort_if(! $region, 404);

            // Every controller reached through here type-hints `Region
            // $region` as the FIRST route-bound parameter (ahead of any
            // other URI segment/model, e.g. `show(Region $region, string
            // $pageSlug)`) — Laravel's resolveMethodDependencies() doesn't
            // match route parameters to method parameters by name, it
            // trusts $route->parameters()'s existing array ORDER once a
            // value of the right class is already present anywhere in it.
            // On the normal "/{region}/..." routes that order is already
            // right, because {region} is literally the first URI segment.
            // Here there's no {region} segment at all, so plain
            // setParameter() would instead APPEND 'region' after whatever
            // URI params the route already captured (e.g. 'pageSlug') —
            // wrong order, silently swapping arguments (a Region where a
            // string was expected and vice versa). Rebuilding the
            // parameter list with 'region' first fixes that for every
            // route this middleware runs on.
            $route = $request->route();
            $otherParams = $route->parameters();
            foreach (array_keys($otherParams) as $key) {
                $route->forgetParameter($key);
            }
            $route->setParameter('region', $region);
            foreach ($otherParams as $key => $value) {
                $route->setParameter($key, $value);
            }
        }

        $request->attributes->set('region', $region);

        View::share('region', $region);

        // Header/footer nav (Menu Manager) and footer copy/logo (General
        // Settings) are shared here once per request for every public page,
        // matching how $region/$allRegions are already shared. The blog
        // section (listing + detail) runs its own fully independent set of
        // the same 4 menus rather than the rest of the site's — resolved
        // from whatever's left of the URL after the region prefix (or, for
        // a root-mounted region, the whole path — there's no prefix segment
        // to skip).
        $remainingSegments = $region->code ? array_slice($request->segments(), 1) : $request->segments();
        $remainingPath = implode('/', $remainingSegments);
        $firstSegment = $remainingSegments[0] ?? '';
        // Blog posts now sit directly at their own slug — no "/blog/"
        // segment ahead of it to spot from the URL alone (see item 18's
        // URL-scheme rework) — so a detail page is only identifiable by
        // actually resolving it as a blog post, the same way
        // PageRouterController itself would. Checked last (it's the only
        // one of the three that costs a query) since the first two already
        // cover the far more common blog-listing/legacy-"/blog/" cases.
        $isBlogSection = $firstSegment === 'blog'
            || PageSetting::resolveSlug($region, $firstSegment) === 'blogs'
            || Blog::resolveByPath($region, $remainingPath) !== null;
        $scope = $isBlogSection ? Menu::SCOPE_BLOG : Menu::SCOPE_GLOBAL;

        $menusBySlot = Menu::forRegionScope($region, $scope);
        View::share('headerMenu', $menusBySlot->get(Menu::SLOT_HEADER));
        View::share('footerAboutMenu', $menusBySlot->get(Menu::SLOT_FOOTER_ABOUT));
        View::share('footerConnectMenu', $menusBySlot->get(Menu::SLOT_FOOTER_CONNECT));
        View::share('footerShopMenu', $menusBySlot->get(Menu::SLOT_FOOTER_SHOP));
        View::share('generalSettings', GeneralSetting::forRegion($region->id));

        return $next($request);
    }
}
