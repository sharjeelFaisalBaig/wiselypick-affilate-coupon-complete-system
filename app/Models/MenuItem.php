<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'title',
        'url',
        'target',
        'sort_order',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function opensInNewTab(): bool
    {
        return $this->target === 'new_tab';
    }

    /**
     * Seeded/admin-entered urls are region-relative paths (e.g. "/coupons")
     * so admins don't have to think about the region prefix — external
     * links ("https://...") and placeholders ("#") pass through unchanged.
     */
    public function resolvedUrl(Region $region): string
    {
        if ($this->url === '#' || str_starts_with($this->url, 'http://') || str_starts_with($this->url, 'https://')) {
            return $this->url;
        }

        return $region->publicUrl($this->url);
    }
}
