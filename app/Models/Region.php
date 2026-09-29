<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'favicon_path',
        'flag_path',
        'head_start_script',
        'head_end_script',
        'body_start_script',
        'body_end_script',
        'canonical_base_url',
        'robots_extra_allow',
        'robots_extra_disallow',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Route-model binding resolves public {region} route segments by `code`
     * (e.g. "us"), restricted to active regions, instead of by primary key.
     * Admin routes (Route::resource('regions', ...) and friends) also bind
     * a parameter literally named {region} — those pass a numeric ID and
     * must resolve by primary key regardless of is_active, since disabling/
     * enabling a region is exactly what those routes are for.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (is_numeric($value)) {
            return $this->where('id', $value)->firstOrFail();
        }

        return $this->where('code', $value)->where('is_active', true)->firstOrFail();
    }

    /**
     * The "/{code}" URL prefix every public route for this region normally
     * sits under — empty for the one region allowed to have a blank `code`
     * (the default region, per item 18: leaving its slug blank makes the
     * bare site root itself serve that region, instead of redirecting to
     * "/{code}"). Every public URL builder (Store/Blog/PageSetting/MenuItem
     * urlFor(), the sitemap/redirect links, etc.) must go through this —
     * never concatenate "/{$region->code}" directly — so all of them stay
     * correct for a root-mounted default region automatically.
     */
    public function urlPrefix(): string
    {
        return $this->code ? '/'.$this->code : '';
    }

    /**
     * Builds an absolute public URL under this region's prefix (or the bare
     * site root, for a root-mounted default region — see urlPrefix()).
     * $path is relative, with or without a leading slash; empty/omitted
     * gives the region's homepage.
     */
    public function publicUrl(string $path = ''): string
    {
        $path = trim($path, '/');
        $prefix = $this->urlPrefix();

        if ($path === '') {
            return url($prefix === '' ? '/' : $prefix);
        }

        return url($prefix.'/'.$path);
    }

    /**
     * Every page's <link rel="canonical"> is built from this + the current
     * path, rather than a hand-typed per-page canonical_url field — admins
     * were never supposed to type arbitrary canonical URLs per store/blog.
     * Falls back to the currently-resolved host when left blank.
     */
    public function canonicalUrlFor(string $path): string
    {
        $base = rtrim($this->canonical_base_url ?: request()->getSchemeAndHttpHost(), '/');

        return $base.'/'.ltrim($path, '/');
    }

    /**
     * Admin-typed one-URL-per-line textarea, split into a clean array of
     * absolute URLs for SitemapController to render as robots.txt lines —
     * blank lines and stray whitespace dropped.
     */
    public function robotsExtraAllowLines(): array
    {
        return self::splitRobotsLines($this->robots_extra_allow);
    }

    public function robotsExtraDisallowLines(): array
    {
        return self::splitRobotsLines($this->robots_extra_disallow);
    }

    private static function splitRobotsLines(?string $value): array
    {
        if (! $value) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function blogCategories(): HasMany
    {
        return $this->hasMany(BlogCategory::class);
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    public function staticPages(): HasMany
    {
        return $this->hasMany(StaticPage::class);
    }

    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function affiliateNetworks(): HasMany
    {
        return $this->hasMany(AffiliateNetwork::class);
    }

    public function scriptInjections(): HasMany
    {
        return $this->hasMany(ScriptInjection::class);
    }

    public function homepageSections(): HasMany
    {
        return $this->hasMany(HomepageSection::class);
    }

    public function pageSettings(): HasMany
    {
        return $this->hasMany(PageSetting::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function generalSetting(): HasOne
    {
        return $this->hasOne(GeneralSetting::class);
    }
}
