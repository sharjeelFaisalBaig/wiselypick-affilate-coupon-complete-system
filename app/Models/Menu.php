<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    public const SLOT_HEADER = 'header';
    public const SLOT_FOOTER_ABOUT = 'footer_about_us';
    public const SLOT_FOOTER_CONNECT = 'footer_connect';
    public const SLOT_FOOTER_SHOP = 'footer_shop_coupons';

    public const SCOPE_GLOBAL = 'global';
    public const SCOPE_BLOG = 'blog';

    public const SCOPES = [
        self::SCOPE_GLOBAL => 'Global Site Menus',
        self::SCOPE_BLOG => 'Blog Page Menus',
    ];

    /**
     * Menu instances are fixed by the SRS — admins can add/remove items
     * within a menu, but never add or remove a menu itself. Each of these 4
     * exists TWICE per region — once per SCOPES entry — so the blog section
     * can run its own fully independent header/footer rather than sharing
     * the rest of the site's.
     */
    public const FIXED_SLOTS = [
        self::SLOT_HEADER => 'Header Menu',
        self::SLOT_FOOTER_ABOUT => 'About Us',
        self::SLOT_FOOTER_CONNECT => 'Connect',
        self::SLOT_FOOTER_SHOP => 'Shop Coupons',
    ];

    protected $fillable = [
        'region_id',
        'slot',
        'scope',
        'name',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    /**
     * Creates any of the 4 fixed menus that don't yet exist for this region
     * + scope combination (used both for fresh-region initialization and to
     * backfill regions that existed before the Menu Manager module, or this
     * scope, did).
     */
    public static function ensureFixedMenusExist(Region $region, string $scope = self::SCOPE_GLOBAL): void
    {
        foreach (self::FIXED_SLOTS as $slot => $name) {
            self::firstOrCreate(['region_id' => $region->id, 'slot' => $slot, 'scope' => $scope], ['name' => $name]);
        }
    }

    /**
     * All 4 of this region's menus for one scope, keyed by slot — the shape
     * ResolvePublicRegion needs to share header/footer menus that differ
     * between the blog section and the rest of the site.
     */
    public static function forRegionScope(Region $region, string $scope): Collection
    {
        return self::where('region_id', $region->id)->where('scope', $scope)->with('items')->get()->keyBy('slot');
    }

    /**
     * Seeds each fixed menu's starting links — guarded per-menu so it only
     * fills menus that are still empty, never overwriting an admin's own
     * edits. Without this, header.blade.php/footer.blade.php render with
     * zero links at all (no hardcoded fallback exists), so this must run
     * for every region a fresh install creates, not just ones made through
     * the admin UI. Seeds BOTH scopes — the blog scope gets its own
     * starting content (not a copy of the global one) since the whole point
     * is a section that reads as its own destination, not an appendage of
     * the deals site; admins are free to edit either from there.
     */
    public static function seedDefaultItemsFor(Region $region): void
    {
        self::ensureFixedMenusExist($region, self::SCOPE_GLOBAL);
        self::ensureFixedMenusExist($region, self::SCOPE_BLOG);

        self::seedItems($region, self::SCOPE_GLOBAL, self::SLOT_HEADER, [
            ['title' => 'Home', 'url' => '/exclusive'],
            ['title' => 'Promo Codes', 'url' => '/exclusive/coupons'],
            ['title' => 'Stores', 'url' => '/exclusive/stores'],
            ['title' => 'Blog', 'url' => '/'],
        ]);
        self::seedItems($region, self::SCOPE_GLOBAL, self::SLOT_FOOTER_ABOUT, [
            ['title' => 'Contact Us', 'url' => '/p/contact'],
            ['title' => 'Terms of Use', 'url' => '/p/terms-of-use'],
            ['title' => 'Privacy Policy', 'url' => '/p/privacy-policy'],
        ]);
        self::seedItems($region, self::SCOPE_GLOBAL, self::SLOT_FOOTER_CONNECT, [
            ['title' => 'Blog', 'url' => '/', 'target' => 'same_tab'],
            ['title' => 'Twitter', 'url' => '#', 'target' => 'new_tab'],
            ['title' => 'Facebook', 'url' => '#', 'target' => 'new_tab'],
            ['title' => 'Instagram', 'url' => '#', 'target' => 'new_tab'],
            ['title' => 'LinkedIn', 'url' => '#', 'target' => 'new_tab'],
        ]);
        self::seedItems($region, self::SCOPE_GLOBAL, self::SLOT_FOOTER_SHOP, [
            ['title' => 'Shop Deals', 'url' => '/exclusive/coupons'],
            ['title' => 'Stores by Category', 'url' => '/exclusive/stores'],
        ]);

        self::seedItems($region, self::SCOPE_BLOG, self::SLOT_HEADER, [
            ['title' => 'Blog Home', 'url' => '/'],
        ]);
        self::seedItems($region, self::SCOPE_BLOG, self::SLOT_FOOTER_ABOUT, [
            ['title' => 'Contact Us', 'url' => '/p/contact'],
            ['title' => 'Terms of Use', 'url' => '/p/terms-of-use'],
            ['title' => 'Privacy Policy', 'url' => '/p/privacy-policy'],
        ]);
        self::seedItems($region, self::SCOPE_BLOG, self::SLOT_FOOTER_CONNECT, [
            ['title' => 'Twitter', 'url' => '#', 'target' => 'new_tab'],
            ['title' => 'Facebook', 'url' => '#', 'target' => 'new_tab'],
            ['title' => 'Instagram', 'url' => '#', 'target' => 'new_tab'],
        ]);
        self::seedItems($region, self::SCOPE_BLOG, self::SLOT_FOOTER_SHOP, [
            ['title' => 'Latest Articles', 'url' => '/'],
        ]);
    }

    private static function seedItems(Region $region, string $scope, string $slot, array $items): void
    {
        $menu = self::where('region_id', $region->id)->where('slot', $slot)->where('scope', $scope)->first();
        if (! $menu || $menu->items()->exists()) {
            return;
        }

        foreach ($items as $i => $item) {
            $menu->items()->create($item + ['target' => 'same_tab', 'sort_order' => $i + 1]);
        }
    }
}
