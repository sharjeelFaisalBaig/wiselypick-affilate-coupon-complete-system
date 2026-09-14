<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreSuffix extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    /**
     * Creates this region's 3 starting suffix rows if they don't already
     * exist — called from both the create_store_suffixes_table migration
     * (for an existing installation gaining this taxonomy) and
     * RegionDefaultsSeeder (for a fresh install, where no region exists yet
     * at migration time — see Menu::seedDefaultItemsFor()'s docblock for
     * the same architectural reason this can't just live in the migration).
     */
    public static function seedDefaultsFor(Region $region): void
    {
        foreach (['Promo Codes, Coupons & Deals', 'Promo Codes', 'Trending Deals'] as $name) {
            self::firstOrCreate(['region_id' => $region->id, 'name' => $name]);
        }
    }
}
