<?php

namespace App\Support;

use App\Models\AffiliateNetwork;
use App\Models\Badge;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogSlugPrefix;
use App\Models\BlogSlugSuffix;
use App\Models\Category;
use App\Models\ContactPageAgenda;
use App\Models\GeneralSetting;
use App\Models\HomepageSection;
use App\Models\Menu;
use App\Models\Offer;
use App\Models\PageSetting;
use App\Models\PageSlugPrefix;
use App\Models\PageSlugSuffix;
use App\Models\Region;
use App\Models\ScriptInjection;
use App\Models\StaticPage;
use App\Models\Store;
use App\Models\StoreSlugPrefix;
use App\Models\StoreSlugSuffix;
use App\Models\StoreSuffix;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Deep-copies a Region, split into 3 independently selectable, independently
 * transacted modules (item 5 of the 2026-09-27 batch):
 *
 *  - MODULE_BLOGS: blogs + blog categories + their related-blogs pivot.
 *  - MODULE_STORES: store categories, store suffixes, badges ("promo
 *    features"), stores, offers, and the offer/badge pivot.
 *  - MODULE_CONFIG: everything else configuration-shaped — the region's own
 *    branding/scripts, general settings, menus, page settings, static
 *    pages, script injections, homepage sections, affiliate networks, and
 *    contact page agendas.
 *
 * Every copy first creates a plain "shell" region seeded with the exact
 * same fresh-region defaults RegionController::store() gives a brand new
 * region (so the result is always a working region even if no module is
 * selected, or a module fails) — then each SELECTED module replaces that
 * baseline with the source region's real data, in its own transaction. A
 * module's failure rolls back only that module's rows (via the thrown
 * exception unwinding its DB::transaction()) and deletes only the files
 * *that module* had already copied to disk before the exception — sibling
 * modules that already committed are untouched.
 *
 * MODULE_CONFIG's pivots that reference stores/offers/blogs (script
 * injection targeting, homepage section picks) are resolved by querying
 * the new region's CURRENT rows at copy time (matched by slug), rather
 * than an in-memory id map — so they still wire up correctly regardless of
 * which order the caller runs the modules in, and degrade gracefully to
 * "no picks" if MODULE_STORES/MODULE_BLOGS wasn't selected or didn't
 * commit.
 */
class RegionCopier
{
    public const MODULE_BLOGS = 'blogs';
    public const MODULE_STORES = 'stores';
    public const MODULE_CONFIG = 'config';

    public const MODULES = [
        self::MODULE_BLOGS => 'Blogs',
        self::MODULE_STORES => 'Stores & Promotions',
        self::MODULE_CONFIG => 'All Configurations',
    ];

    /** @var array<int,int> old id => new id, scoped to whichever module is currently running */
    private array $map = [];

    /** @var string[] disk paths copied so far by the currently-running module, for cleanup on its failure */
    private array $copiedFiles = [];

    /**
     * @param  string[]  $modules  subset of self::MODULES' keys
     * @return array{region: Region, results: array<string,array{status: string, message: ?string}>}
     */
    public function copy(Region $source, string $newCode, string $newName, array $modules): array
    {
        $region = $this->createShellRegion($source, $newCode, $newName);

        $results = [];

        // Fixed order regardless of the caller's array order — MODULE_CONFIG
        // runs last so its store/offer/blog pivots can see whatever
        // MODULE_STORES/MODULE_BLOGS already committed in this same request.
        foreach ([self::MODULE_BLOGS, self::MODULE_STORES, self::MODULE_CONFIG] as $module) {
            if (! in_array($module, $modules, true)) {
                continue;
            }

            $this->map = [];
            $this->copiedFiles = [];

            try {
                DB::transaction(function () use ($module, $source, $region) {
                    match ($module) {
                        self::MODULE_BLOGS => $this->copyBlogsModule($source, $region),
                        self::MODULE_STORES => $this->copyStoresModule($source, $region),
                        self::MODULE_CONFIG => $this->copyConfigModule($source, $region),
                    };
                });

                $results[$module] = ['status' => 'success', 'message' => null];
            } catch (\Throwable $e) {
                foreach ($this->copiedFiles as $path) {
                    Storage::disk('public')->delete($path);
                }

                report($e);
                $results[$module] = ['status' => 'failed', 'message' => $e->getMessage()];
            }
        }

        return ['region' => $region, 'results' => $results];
    }

    /**
     * Always runs, outside any module's transaction — a brand new region
     * row plus the exact same baseline every fresh "Add Region" gets
     * (Menu::seedDefaultItemsFor() and friends), so the copy is a working
     * region even before any module below touches it. Disabled and never
     * default, exactly like a freshly created region.
     */
    private function createShellRegion(Region $source, string $newCode, string $newName): Region
    {
        $region = Region::create([
            'code' => $newCode,
            'name' => $newName,
            'is_active' => false,
            'is_default' => false,
            'sort_order' => (Region::max('sort_order') ?? 0) + 1,
        ]);

        Menu::seedDefaultItemsFor($region);
        Badge::seedDefaultsFor($region);
        ContactPageAgenda::seedDefaultsFor($region);
        StaticPage::seedDefaultsFor($region);
        PageSetting::seedDefaultsFor($region);
        StoreSuffix::seedDefaultsFor($region);
        GeneralSetting::seedDefaultsFor($region);

        return $region;
    }

    // ============ MODULE_BLOGS ============

    private function copyBlogsModule(Region $source, Region $region): void
    {
        $blogCategoryMap = [];
        foreach (BlogCategory::where('region_id', $source->id)->get() as $blogCategory) {
            $new = $blogCategory->replicate();
            $new->region_id = $region->id;
            $new->save();

            $blogCategoryMap[$blogCategory->id] = $new->id;
        }

        $blogSlugPrefixMap = [];
        foreach (BlogSlugPrefix::where('region_id', $source->id)->get() as $prefix) {
            $new = $prefix->replicate();
            $new->region_id = $region->id;
            $new->save();

            $blogSlugPrefixMap[$prefix->id] = $new->id;
        }

        $blogSlugSuffixMap = [];
        foreach (BlogSlugSuffix::where('region_id', $source->id)->get() as $suffix) {
            $new = $suffix->replicate();
            $new->region_id = $region->id;
            $new->save();

            $blogSlugSuffixMap[$suffix->id] = $new->id;
        }

        foreach (Blog::where('region_id', $source->id)->get() as $blog) {
            $new = $blog->replicate();
            $new->region_id = $region->id;
            $new->blog_category_id = $blog->blog_category_id ? ($blogCategoryMap[$blog->blog_category_id] ?? null) : null;
            $new->blog_slug_prefix_id = $blog->blog_slug_prefix_id ? ($blogSlugPrefixMap[$blog->blog_slug_prefix_id] ?? null) : null;
            $new->blog_slug_suffix_id = $blog->blog_slug_suffix_id ? ($blogSlugSuffixMap[$blog->blog_slug_suffix_id] ?? null) : null;
            $new->featured_image = $this->copyFile($blog->featured_image);
            $new->save();

            $this->map[$blog->id] = $new->id;
        }

        $oldBlogIds = array_keys($this->map);
        if ($oldBlogIds) {
            $insert = [];
            foreach (DB::table('blog_related_blog')->whereIn('blog_id', $oldBlogIds)->get() as $row) {
                if (isset($this->map[$row->blog_id], $this->map[$row->related_blog_id])) {
                    $insert[] = [
                        'blog_id' => $this->map[$row->blog_id],
                        'related_blog_id' => $this->map[$row->related_blog_id],
                        'sort_order' => $row->sort_order,
                    ];
                }
            }
            if ($insert) {
                DB::table('blog_related_blog')->insert($insert);
            }
        }
    }

    // ============ MODULE_STORES ============

    private function copyStoresModule(Region $source, Region $region): void
    {
        // Replace the shell's generic baseline taxonomy with the source
        // region's actual one, rather than merging the two — nothing
        // references the baseline rows yet, so this is safe.
        Category::where('region_id', $region->id)->where('type', 'store')->delete();
        StoreSuffix::where('region_id', $region->id)->delete();
        Badge::where('region_id', $region->id)->delete();

        $categoryMap = [];
        foreach (Category::where('region_id', $source->id)->where('type', 'store')->get() as $category) {
            $new = $category->replicate();
            $new->region_id = $region->id;
            $new->icon_path = $this->copyFile($category->icon_path);
            $new->save();

            $categoryMap[$category->id] = $new->id;
        }

        $suffixMap = [];
        foreach (StoreSuffix::where('region_id', $source->id)->get() as $suffix) {
            $new = $suffix->replicate();
            $new->region_id = $region->id;
            $new->save();

            $suffixMap[$suffix->id] = $new->id;
        }

        $slugPrefixMap = [];
        foreach (StoreSlugPrefix::where('region_id', $source->id)->get() as $prefix) {
            $new = $prefix->replicate();
            $new->region_id = $region->id;
            $new->save();

            $slugPrefixMap[$prefix->id] = $new->id;
        }

        $slugSuffixMap = [];
        foreach (StoreSlugSuffix::where('region_id', $source->id)->get() as $suffix) {
            $new = $suffix->replicate();
            $new->region_id = $region->id;
            $new->save();

            $slugSuffixMap[$suffix->id] = $new->id;
        }

        $badgeMap = [];
        foreach (Badge::where('region_id', $source->id)->get() as $badge) {
            $new = $badge->replicate();
            $new->region_id = $region->id;
            $new->save();

            $badgeMap[$badge->id] = $new->id;
        }

        $storeMap = [];
        foreach (Store::where('region_id', $source->id)->get() as $store) {
            $new = $store->replicate();
            $new->region_id = $region->id;
            $new->category_id = $store->category_id ? ($categoryMap[$store->category_id] ?? null) : null;
            $new->store_suffix_id = $store->store_suffix_id ? ($suffixMap[$store->store_suffix_id] ?? null) : null;
            $new->store_slug_prefix_id = $store->store_slug_prefix_id ? ($slugPrefixMap[$store->store_slug_prefix_id] ?? null) : null;
            $new->store_slug_suffix_id = $store->store_slug_suffix_id ? ($slugSuffixMap[$store->store_slug_suffix_id] ?? null) : null;
            $new->logo_path = $this->copyFile($store->logo_path);
            $new->og_image = $this->copyFile($store->og_image);
            $new->save();

            $storeMap[$store->id] = $new->id;
        }

        $offerMap = [];
        foreach ($storeMap as $oldStoreId => $newStoreId) {
            foreach (Offer::where('store_id', $oldStoreId)->get() as $offer) {
                $new = $offer->replicate();
                $new->store_id = $newStoreId;
                $new->save();

                $offerMap[$offer->id] = $new->id;
            }
        }

        $oldOfferIds = array_keys($offerMap);
        if ($oldOfferIds) {
            $insert = [];
            $now = now();
            foreach (DB::table('offer_badge')->whereIn('offer_id', $oldOfferIds)->get() as $row) {
                if (isset($offerMap[$row->offer_id], $badgeMap[$row->badge_id])) {
                    $insert[] = [
                        'offer_id' => $offerMap[$row->offer_id],
                        'badge_id' => $badgeMap[$row->badge_id],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
            if ($insert) {
                DB::table('offer_badge')->insert($insert);
            }
        }
    }

    // ============ MODULE_CONFIG ============

    private function copyConfigModule(Region $source, Region $region): void
    {
        $region->update([
            'canonical_base_url' => $source->canonical_base_url,
            'robots_extra_allow' => $source->robots_extra_allow,
            'robots_extra_disallow' => $source->robots_extra_disallow,
            'head_start_script' => $source->head_start_script,
            'head_end_script' => $source->head_end_script,
            'body_start_script' => $source->body_start_script,
            'body_end_script' => $source->body_end_script,
            'favicon_path' => $this->copyFile($source->favicon_path),
            'flag_path' => $this->copyFile($source->flag_path),
        ]);

        $this->copyGeneralSetting($source, $region);
        $this->copyMenus($source, $region);
        $this->copyPageSettings($source, $region);
        $this->copyStaticPages($source, $region);
        $this->copyContactPageAgendas($source, $region);
        $this->copyAffiliateNetworks($source, $region);
        $this->copyScriptInjections($source, $region);
        $this->copyHomepageSections($source, $region);
    }

    private function copyGeneralSetting(Region $source, Region $region): void
    {
        $setting = GeneralSetting::where('region_id', $source->id)->first();
        if (! $setting) {
            return;
        }

        // The shell already seeded one (region_id is unique) — delete it
        // before inserting the source's copy rather than updating in place,
        // so this stays the same replicate()-based shape as every other
        // config table here.
        GeneralSetting::where('region_id', $region->id)->delete();

        $new = $setting->replicate();
        $new->region_id = $region->id;
        $new->logo_path = $this->copyFile($setting->logo_path);
        $new->save();
    }

    private function copyMenus(Region $source, Region $region): void
    {
        Menu::where('region_id', $region->id)->delete();

        foreach (Menu::where('region_id', $source->id)->with('items')->get() as $menu) {
            $new = $menu->replicate();
            $new->region_id = $region->id;
            $new->save();

            foreach ($menu->items as $item) {
                $newItem = $item->replicate();
                $newItem->menu_id = $new->id;
                $newItem->save();
            }
        }
    }

    private function copyPageSettings(Region $source, Region $region): void
    {
        PageSetting::where('region_id', $region->id)->delete();

        foreach (PageSetting::where('region_id', $source->id)->get() as $setting) {
            $new = $setting->replicate();
            $new->region_id = $region->id;
            $new->og_image = $this->copyFile($setting->og_image);
            $new->save();
        }
    }

    private function copyStaticPages(Region $source, Region $region): void
    {
        StaticPage::where('region_id', $region->id)->delete();

        $pageSlugPrefixMap = [];
        foreach (PageSlugPrefix::where('region_id', $source->id)->get() as $prefix) {
            $new = $prefix->replicate();
            $new->region_id = $region->id;
            $new->save();

            $pageSlugPrefixMap[$prefix->id] = $new->id;
        }

        $pageSlugSuffixMap = [];
        foreach (PageSlugSuffix::where('region_id', $source->id)->get() as $suffix) {
            $new = $suffix->replicate();
            $new->region_id = $region->id;
            $new->save();

            $pageSlugSuffixMap[$suffix->id] = $new->id;
        }

        foreach (StaticPage::where('region_id', $source->id)->get() as $page) {
            $new = $page->replicate();
            $new->region_id = $region->id;
            $new->page_slug_prefix_id = $page->page_slug_prefix_id ? ($pageSlugPrefixMap[$page->page_slug_prefix_id] ?? null) : null;
            $new->page_slug_suffix_id = $page->page_slug_suffix_id ? ($pageSlugSuffixMap[$page->page_slug_suffix_id] ?? null) : null;
            $new->og_image = $this->copyFile($page->og_image);
            $new->save();
        }
    }

    private function copyContactPageAgendas(Region $source, Region $region): void
    {
        ContactPageAgenda::where('region_id', $region->id)->delete();

        foreach (ContactPageAgenda::where('region_id', $source->id)->get() as $agenda) {
            $new = $agenda->replicate();
            $new->region_id = $region->id;
            $new->save();
        }
    }

    private function copyAffiliateNetworks(Region $source, Region $region): void
    {
        foreach (AffiliateNetwork::where('region_id', $source->id)->get() as $network) {
            $new = $network->replicate();
            $new->region_id = $region->id;
            $new->save();
        }
    }

    private function copyScriptInjections(Region $source, Region $region): void
    {
        // Keyed by the new region's CURRENT stores (matched by slug) rather
        // than an in-memory id map from MODULE_STORES — that module runs in
        // its own transaction and may not even be selected, so this is the
        // only way to reliably find "the store this pivot row means" here.
        $newStoreIdBySlug = Store::where('region_id', $region->id)->pluck('id', 'slug');
        $sourceStoreSlugById = Store::where('region_id', $source->id)->pluck('slug', 'id');

        foreach (ScriptInjection::where('region_id', $source->id)->with(['pageTargets', 'stores'])->get() as $injection) {
            $new = $injection->replicate();
            $new->region_id = $region->id;
            $new->save();

            foreach ($injection->pageTargets as $target) {
                $newTarget = $target->replicate();
                $newTarget->script_injection_id = $new->id;
                $newTarget->save();
            }

            foreach ($injection->stores as $store) {
                $slug = $sourceStoreSlugById[$store->id] ?? null;
                $newStoreId = $slug ? ($newStoreIdBySlug[$slug] ?? null) : null;
                if ($newStoreId) {
                    DB::table('script_injection_store')->insert([
                        'script_injection_id' => $new->id,
                        'store_id' => $newStoreId,
                    ]);
                }
            }
        }
    }

    private function copyHomepageSections(Region $source, Region $region): void
    {
        $newStoreIdBySlug = Store::where('region_id', $region->id)->pluck('id', 'slug');
        $sourceStoreSlugById = Store::where('region_id', $source->id)->pluck('slug', 'id');

        // Offers have no natural unique key of their own — correlate by
        // (store slug, sort_order), which OfferSeeder/the store copy both
        // already keep unique per store.
        $newOfferIdByStoreAndSort = DB::table('offers')
            ->join('stores', 'stores.id', '=', 'offers.store_id')
            ->where('stores.region_id', $region->id)
            ->get(['stores.slug as store_slug', 'offers.sort_order', 'offers.id'])
            ->mapWithKeys(fn ($row) => ["{$row->store_slug}:{$row->sort_order}" => $row->id]);

        $sourceOfferStoreSlugAndSortById = DB::table('offers')
            ->join('stores', 'stores.id', '=', 'offers.store_id')
            ->where('stores.region_id', $source->id)
            ->get(['offers.id', 'stores.slug as store_slug', 'offers.sort_order'])
            ->keyBy('id');

        foreach (HomepageSection::where('region_id', $source->id)->get() as $section) {
            $new = $section->replicate();
            $new->region_id = $region->id;
            $new->save();

            foreach ($section->stores()->get() as $store) {
                $slug = $sourceStoreSlugById[$store->id] ?? null;
                $newStoreId = $slug ? ($newStoreIdBySlug[$slug] ?? null) : null;
                if ($newStoreId) {
                    DB::table('homepage_section_store')->insert([
                        'homepage_section_id' => $new->id,
                        'store_id' => $newStoreId,
                        'sort_order' => $store->pivot->sort_order,
                    ]);
                }
            }

            foreach ($section->offers()->get() as $offer) {
                $sourceInfo = $sourceOfferStoreSlugAndSortById[$offer->id] ?? null;
                $newOfferId = $sourceInfo ? ($newOfferIdByStoreAndSort["{$sourceInfo->store_slug}:{$sourceInfo->sort_order}"] ?? null) : null;
                if ($newOfferId) {
                    DB::table('homepage_section_offer')->insert([
                        'homepage_section_id' => $new->id,
                        'offer_id' => $newOfferId,
                        'sort_order' => $offer->pivot->sort_order,
                    ]);
                }
            }
        }
    }

    /**
     * Copies a public-disk file to a new, uniquely-named path in the same
     * directory (so the new region never shares a stored file with the
     * source) and records it against the CURRENTLY RUNNING module, for
     * cleanup if that module's transaction fails. Null/missing source
     * paths pass through unchanged.
     */
    private function copyFile(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return $path;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        $newPath = ($directory !== '.' ? $directory.'/' : '').Str::uuid().($extension ? ".{$extension}" : '');

        Storage::disk('public')->copy($path, $newPath);
        $this->copiedFiles[] = $newPath;

        return $newPath;
    }
}
