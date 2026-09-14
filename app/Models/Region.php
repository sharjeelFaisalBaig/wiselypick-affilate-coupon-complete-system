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
