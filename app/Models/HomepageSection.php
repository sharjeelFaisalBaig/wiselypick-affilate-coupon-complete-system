<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HomepageSection extends Model
{
    use HasFactory;

    public const MAX_OFFERS = 5;

    public const MAX_STORES = 6;

    public const MAX_CATEGORIES = 6;

    protected $fillable = [
        'region_id',
        'title',
        'content_type',
        'cta_label',
        'cta_url',
        'cta_target',
        'sort_order',
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

    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class, 'homepage_section_offer')
            ->withPivot('sort_order')
            ->orderBy('homepage_section_offer.sort_order');
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'homepage_section_store')
            ->withPivot('sort_order')
            ->orderBy('homepage_section_store.sort_order');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'homepage_section_category')
            ->withPivot('sort_order')
            ->orderBy('homepage_section_category.sort_order');
    }

    public function isStoreShowcase(): bool
    {
        return $this->content_type === 'store';
    }

    public function isCategoryShowcase(): bool
    {
        return $this->content_type === 'categories';
    }
}
