<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScriptInjection extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'name',
        'placement',
        'script_content',
        'target_type',
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

    public function pageTargets(): HasMany
    {
        return $this->hasMany(ScriptInjectionPage::class);
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'script_injection_store');
    }

    /**
     * Scope to scripts that should render for the given page type
     * (and, when a store is provided, scripts targeting that store).
     */
    public function scopeForPage($query, string $pageType, ?int $storeId = null)
    {
        return $query->where('is_active', true)->where(function ($q) use ($pageType, $storeId) {
            $q->where('target_type', 'all_pages')
                ->orWhere(function ($q2) use ($pageType) {
                    $q2->where('target_type', 'specific_pages')
                        ->whereHas('pageTargets', fn ($t) => $t->where('page_type', $pageType));
                });

            if ($storeId) {
                $q->orWhere(function ($q3) use ($storeId) {
                    $q3->where('target_type', 'specific_stores')
                        ->whereHas('stores', fn ($s) => $s->where('stores.id', $storeId));
                });
            }
        });
    }
}
