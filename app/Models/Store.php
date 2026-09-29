<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    public const DEFAULT_ROUTE_PREFIX = 'exclusive/stores';

    protected $fillable = [
        'region_id',
        'category_id',
        'store_suffix_id',
        'store_slug_prefix_id',
        'store_slug_suffix_id',
        'starts_from_root',
        'name',
        'slug',
        'logo_path',
        'about',
        'affiliate_url',
        'start_date',
        'expiry_date',
        'star_rating',
        'reviews_count',
        'is_featured',
        'featured_order',
        'is_popular',
        'popular_order',
        'is_pending',
        'pending_order',
        'is_active',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'og_title',
        'og_description',
        'head_start_script',
        'head_end_script',
        'body_start_script',
        'body_end_script',
        'robots_index',
        'robots_follow',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expiry_date' => 'date',
            'star_rating' => 'decimal:1',
            'is_featured' => 'boolean',
            'is_popular' => 'boolean',
            'is_pending' => 'boolean',
            'is_active' => 'boolean',
            'starts_from_root' => 'boolean',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
        ];
    }

    /**
     * Row 6's auto-calculated statistics box — Verified Discount Codes,
     * Total Coupons, Last Coupon Added — derived from this store's own
     * active offers rather than stored, so they never go stale. Used to
     * also include Best Discount Today / Average Shopper Savings, but
     * those were derived from offers.discount_value, which no longer
     * exists now that promotions are free-text only.
     */
    public function savingsStats(): array
    {
        $activeOffers = $this->offers()->where('is_active', true)->get();

        return [
            'verified_codes' => $activeOffers->filter(fn ($o) => $o->isVerified())->count(),
            'total_coupons' => $activeOffers->where('offer_type', 'coupon')->count(),
            'last_coupon_added' => optional($this->offers()->latest('created_at')->first())->created_at?->diffForHumans() ?? '—',
        ];
    }

    /**
     * Published (Store State = Active) AND not past its own expiry date (if
     * one is set) — once a store expires, neither it nor its offers should
     * appear anywhere on the frontend, mirroring how Offer::isExpired()
     * already gates offers. Store State (is_active) is the single source of
     * truth for pending/active, both here and in
     * Admin\StoreController::classification()'s Pending tab — the legacy
     * `is_pending` column has no form field anywhere and no longer affects
     * anything.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->startOfDay()));
    }

    /**
     * The "{prefix}/{slug}[/{suffix}]" path segment after "/{region}/" — both
     * prefix and suffix are per-store overrides of the "store"/(none)
     * defaults (picked from the admin-managed StoreSlugPrefix/StoreSlugSuffix
     * taxonomies rather than free text), so this is never a fixed literal
     * like the old static `store/{storeSlug}` route was.
     */
    public function path(): string
    {
        if ($this->starts_from_root) {
            $path = $this->slug;
        } else {
            $prefix = trim($this->storeSlugPrefix?->value ?: self::DEFAULT_ROUTE_PREFIX, '/');
            $path = "{$prefix}/{$this->slug}";
        }

        if ($this->storeSlugSuffix?->value) {
            $path .= '/'.trim($this->storeSlugSuffix->value, '/');
        }

        return $path;
    }

    public function urlFor(Region $region): string
    {
        return $region->publicUrl($this->path());
    }

    /**
     * Resolves an incoming "{prefix...}/{slug}[/{suffix}]" path (already
     * stripped of the {region} prefix) back to the one visible store whose
     * own path() matches it exactly — used by the catch-all page router
     * now that a store's prefix/suffix are admin-editable per store rather
     * than the fixed "store/" literal the route used to match on. A
     * root-mounted store (starts_from_root) can be a single segment — see
     * Blog::resolveByPath() for the identical no-floor strategy.
     */
    public static function resolveByPath(Region $region, string $path): ?self
    {
        $path = trim($path, '/');
        if ($path === '') {
            return null;
        }
        $segments = explode('/', $path);

        // No suffix (including a root-mounted store, where this is the
        // ONLY segment): slug is the last segment.
        $store = self::where('region_id', $region->id)->where('slug', end($segments))->visible()->first();
        if ($store && $store->path() === $path) {
            return $store;
        }

        // With a suffix: slug is second-to-last.
        if (count($segments) >= 2) {
            $store = self::where('region_id', $region->id)->where('slug', $segments[count($segments) - 2])->visible()->first();
            if ($store && $store->path() === $path) {
                return $store;
            }
        }

        return null;
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function storeSuffix(): BelongsTo
    {
        return $this->belongsTo(StoreSuffix::class);
    }

    public function storeSlugPrefix(): BelongsTo
    {
        return $this->belongsTo(StoreSlugPrefix::class);
    }

    public function storeSlugSuffix(): BelongsTo
    {
        return $this->belongsTo(StoreSlugSuffix::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function coupons(): HasMany
    {
        return $this->offers()->where('offer_type', 'coupon');
    }

    public function deals(): HasMany
    {
        return $this->offers()->where('offer_type', 'deal');
    }

    public function scriptInjections(): BelongsToMany
    {
        return $this->belongsToMany(ScriptInjection::class, 'script_injection_store');
    }
}
