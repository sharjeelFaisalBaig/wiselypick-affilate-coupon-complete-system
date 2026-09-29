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
     *
     * Deliberately NOT named `forPage` — Eloquent\Builder::paginate()
     * internally calls `$this->forPage($page, $perPage)` for its
     * LIMIT/OFFSET slicing, and since Eloquent\Builder itself has no real
     * `forPage()` method (only the underlying Query\Builder does),
     * `__call()` resolves it via `hasNamedScope()` BEFORE ever reaching the
     * query builder — a local scope of that exact name silently hijacks
     * every `->paginate()` call on this model, replacing the LIMIT/OFFSET
     * with this scope's own WHERE clause instead (confirmed the hard way:
     * admin's script-injections index was calling ->paginate(20), which
     * became ->forPage('1', 20)->get() under the hood, filtering the list
     * down to `is_active=true AND (target_type='all_pages' OR ...
     * page_type='1' ... OR ... stores.id=20)` — dropping every
     * specific_stores script whose store id isn't literally 20).
     */
    public function scopeMatchingPage($query, string $pageType, ?int $storeId = null)
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
