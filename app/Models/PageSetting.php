<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSetting extends Model
{
    protected $fillable = [
        'region_id',
        'page_key',
        'slug',
        'is_active',
        'heading',
        'subheading',
        'hero_search_placeholder',
        'hero_search_button_text',
        'hero_badge_text',
        'meta_title',
        'meta_description',
        'og_title',
        'og_description',
        'og_image',
        'robots_index',
        'robots_follow',
        'schema_script',
        'head_start_script',
        'head_end_script',
        'body_start_script',
        'body_end_script',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public static function forPage(int $regionId, string $pageKey): ?self
    {
        return static::where('region_id', $regionId)->where('page_key', $pageKey)->first();
    }

    public const DEFAULT_SLUGS = ['home' => '', 'stores' => 'stores', 'coupons' => 'coupons', 'blogs' => 'blogs'];

    /**
     * The live "/{region}/{slug}" URL for one of the 4 fixed pages, driven
     * by this row's own (admin-renameable) slug rather than a hardcoded
     * route path — falls back to the original default segment for a region
     * that has no row yet (e.g. one seeded before this column existed).
     */
    public static function urlFor(Region $region, string $pageKey): string
    {
        $slug = static::forPage($region->id, $pageKey)?->slug ?? self::DEFAULT_SLUGS[$pageKey] ?? $pageKey;

        return $region->publicUrl($slug);
    }

    /**
     * Resolves an incoming URL segment (already stripped of the {region}
     * prefix) back to one of the 4 fixed page keys for this region — used
     * by the catch-all page router. Null means no fixed page owns this
     * segment (a 404, since it also isn't store/category/blog/p/etc., all
     * of which are matched by their own distinct route prefixes first).
     */
    public static function resolveSlug(Region $region, string $slug): ?string
    {
        $settings = static::where('region_id', $region->id)->get()->keyBy('page_key');

        foreach (self::DEFAULT_SLUGS as $pageKey => $default) {
            $actual = $settings->get($pageKey)?->slug ?? $default;
            if ($actual === $slug) {
                return $pageKey;
            }
        }

        return null;
    }

    /**
     * Seeds real, editable copy (matching each page's hardcoded fallback
     * text) so the admin form shows actual content instead of blank
     * fields — the fallback strings in each controller remain as a
     * safety net for regions/pages created before this seed ran.
     */
    public static function seedDefaultsFor(Region $region): void
    {
        $defaults = [
            'home' => [
                'heading' => $region->name.' Coupons, Promo Codes & Deals',
                'subheading' => 'Save today with verified coupon codes, promo codes and deals for top stores in '.$region->name.'.',
                'hero_search_placeholder' => 'Search for a store or brand...',
                'hero_search_button_text' => 'Search',
                'hero_badge_text' => 'Verified daily by our editors',
                'meta_title' => $region->name.' Coupons, Promo Codes & Deals',
                'meta_description' => 'Save today with verified coupon codes, promo codes and deals for top stores in '.$region->name.'.',
                'og_title' => $region->name.' Coupons, Promo Codes & Deals',
                'og_description' => 'Save today with verified coupon codes, promo codes and deals for top stores in '.$region->name.'.',
            ],
            'coupons' => [
                'heading' => "Today's Top Promo Codes & Coupons",
                'subheading' => 'Browse all active promo codes and coupons for stores in '.$region->name.', verified regularly.',
                'meta_title' => "Today's Top Promo Codes & Coupons — ".$region->name,
                'meta_description' => 'Browse all active promo codes and coupons for stores in '.$region->name.', verified regularly.',
                'og_title' => "Today's Top Promo Codes & Coupons — ".$region->name,
                'og_description' => 'Browse all active promo codes and coupons for stores in '.$region->name.', verified regularly.',
            ],
            'stores' => [
                'heading' => 'Find Coupons by Store',
                'subheading' => 'Browse all stores with active coupon codes and deals in '.$region->name.'.',
                'meta_title' => 'Find Coupons by Store — '.$region->name,
                'meta_description' => 'Browse all stores with active coupon codes and deals in '.$region->name.'.',
                'og_title' => 'Find Coupons by Store — '.$region->name,
                'og_description' => 'Browse all stores with active coupon codes and deals in '.$region->name.'.',
            ],
            'blogs' => [
                'heading' => $region->name.' Blog',
                'subheading' => 'Shopping tips, savings guides and the latest deals news for '.$region->name.'.',
                'meta_title' => $region->name.' Blog — Shopping Tips, Deals & Savings Guides',
                'meta_description' => 'Shopping tips, savings guides and the latest deals news for '.$region->name.'.',
                'og_title' => $region->name.' Blog — Shopping Tips, Deals & Savings Guides',
                'og_description' => 'Shopping tips, savings guides and the latest deals news for '.$region->name.'.',
            ],
        ];

        foreach ($defaults as $pageKey => $data) {
            self::firstOrCreate(
                ['region_id' => $region->id, 'page_key' => $pageKey],
                $data + ['is_active' => true, 'slug' => self::DEFAULT_SLUGS[$pageKey]]
            );
        }
    }
}
