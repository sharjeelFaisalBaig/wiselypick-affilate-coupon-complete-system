<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\PageSetting;
use App\Models\Region;
use App\Models\StaticPage;
use App\Models\Store;
use Illuminate\Http\Request;

/**
 * Catch-all for every URL whose path isn't one of the other few fixed
 * literal prefixes (category/{slug}, suggest/*, contact, go/{offer}) —
 * registered last in the {region} route group (routes/web.php) so those
 * always match first. Resolves the incoming path against, in order:
 *   1. one of the 4 fixed pages (home/stores/coupons/blogs), whose own URL
 *      segment is admin-renameable per region via PageSetting.slug;
 *   2. a store, whose "{prefix}/{slug}[/{suffix}]" path is admin-renameable
 *      PER STORE by picking from the StoreSlugPrefix/StoreSlugSuffix
 *      taxonomies (Store::storeSlugPrefix()/storeSlugSuffix());
 *   3. a blog post, same idea via BlogSlugPrefix/BlogSlugSuffix;
 *   4. a static page (Contact Us, Terms, Privacy, any other admin-added
 *      page), same idea via PageSlugPrefix/PageSlugSuffix — there is no
 *      more fixed "/p/{slug}" route at all, admin-added pages resolve
 *      exactly like stores/blogs do.
 * None of these are fixed literal routes anymore — resolution has to
 * happen here instead of at the router level.
 */
class PageRouterController extends Controller
{
    private const CONTROLLERS = [
        'home' => HomeController::class,
        'stores' => StoreDirectoryController::class,
        'coupons' => PromoCodeController::class,
        'blogs' => BlogController::class,
    ];

    public function __invoke(Request $request, Region $region, string $slug = '')
    {
        $pageKey = PageSetting::resolveSlug($region, $slug);
        if ($pageKey !== null) {
            return app()->call([app(self::CONTROLLERS[$pageKey]), 'index'], ['region' => $region]);
        }

        if ($store = Store::resolveByPath($region, $slug)) {
            return app()->call([app(StoreController::class), 'show'], ['region' => $region, 'store' => $store]);
        }

        if ($blog = Blog::resolveByPath($region, $slug)) {
            return app()->call([app(BlogController::class), 'show'], ['region' => $region, 'blog' => $blog]);
        }

        if ($page = StaticPage::resolveByPath($region, $slug)) {
            return app()->call([app(StaticPageController::class), 'show'], ['region' => $region, 'staticPage' => $page]);
        }

        abort(404);
    }
}
