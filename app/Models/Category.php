<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'type',
        'name',
        'slug',
        'icon',
        'icon_path',
        'description',
        'meta_title',
        'meta_description',
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

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function breadcrumbLabel(): string
    {
        return $this->name;
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function scopeStoreType($query)
    {
        return $query->where('type', 'store');
    }
}
