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

    public const DEFAULT_ROUTE_PREFIX = 'exclusive/store';

    protected $fillable = [
        'region_id',
        'category_id',
        'store_suffix_id',
        'name',
        'slug',
        'route_prefix',
        'route_suffix',
        'logo_path',
        'about',
        'affiliate_url',
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
            'expiry_date' => 'date',
            'star_rating' => 'decimal:1',
            'is_featured' => 'boolean',
            'is_popular' => 'boolean',
            'is_pending' => 'boolean',
            'is_active' => 'boolean',
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
     * Published, not pending, AND not past its own expiry date (if one is
     * set) — once a store expires, neither it nor its offers should appear
     * anywhere on the frontend, mirroring how Offer::isExpired() already
     * gates offers. `is_pending` (the classification checkbox) used to be a
     * pure curation tag with no effect on visibility — a store could be
     * "Active" AND flagged Pending and still show publicly, which made
     * "Pending" meaningless as a state. Pending, in either form (the Store
     * State select OR this checkbox), now always means hidden from the
     * frontend — see Admin\StoreController::classification() for the other
     * half (both forms of pending also always land in the Pending tab).
     */
    public function scopeVisible($query)
    {
        return $query->where('is_active', true)
            ->where('is_pending', false)
            ->where(fn ($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->startOfDay()));
    }

    /**
     * The "{prefix}/{slug}[/{suffix}]" path segment after "/{region}/" — both
     * prefix and suffix are per-store overrides of the "store"/(none)
     * defaults, so this is never a fixed literal like the old static
     * `store/{storeSlug}` route was.
     */
    public function path(): string
    {
        $prefix = trim($this->route_prefix ?: self::DEFAULT_ROUTE_PREFIX, '/');
        $path = "{$prefix}/{$this->slug}";

        if ($this->route_suffix) {
            $path .= '/'.trim($this->route_suffix, '/');
        }

        return $path;
    }

    public function urlFor(Region $region): string
    {
        return url("/{$region->code}/{$this->path()}");
    }

    /**
     * Resolves an incoming "{prefix...}/{slug}[/{suffix}]" path (already
     * stripped of the {region} prefix) back to the one visible store whose
     * own path() matches it exactly — used by the catch-all page router
     * now that a store's prefix/suffix are admin-editable per store rather
     * than the fixed "store/" literal the route used to match on.
     */
    public static function resolveByPath(Region $region, string $path): ?self
    {
        $path = trim($path, '/');
        $segments = explode('/', $path);
        if (count($segments) < 2) {
            return null;
        }

        // No suffix: "{prefix...}/{slug}" — slug is the last segment.
        $store = self::where('region_id', $region->id)->where('slug', end($segments))->visible()->first();
        if ($store && $store->path() === $path) {
            return $store;
        }

        // With a suffix: "{prefix...}/{slug}/{suffix}" — slug is second-to-last.
        if (count($segments) >= 3) {
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
