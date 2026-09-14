<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'offer_type',
        'code',
        'title',
        'is_active',
        'is_featured',
        'featured_order',
        'start_date',
        'expiry_date',
        'sort_order',
        'clicks',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'start_date' => 'date',
            'expiry_date' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Max 2 enforced at data-entry time (admin form), not by a DB
     * constraint — see BadgeController/OfferController validation.
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'offer_badge');
    }

    public function isCoupon(): bool
    {
        return $this->offer_type === 'coupon';
    }

    /**
     * Where a click on this offer's CTA lands — always the store's own
     * affiliate URL now; promotions no longer have their own destination.
     */
    public function redirectUrl(): string
    {
        return $this->store->affiliate_url;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    /**
     * Record a CTA click: bumps the usage counter and stamps last_used_at,
     * so both are current the instant a shopper reveals/uses this offer.
     */
    public function recordClick(): void
    {
        $this->increment('clicks');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * "{N} uses · Last used {X ago}" — shown on every offer card everywhere.
     * The "Last used" clause is dropped once it's more than a day stale, so
     * cards don't advertise obviously-cold codes as recently used.
     */
    public function usageLabel(): string
    {
        $label = number_format($this->clicks).' '.($this->clicks === 1 ? 'use' : 'uses');

        if ($this->last_used_at && $this->last_used_at->isAfter(now()->subDay())) {
            $label .= ' · Last used '.$this->last_used_at->diffForHumans();
        }

        return $label;
    }

    /**
     * Whether this offer carries the "Verified" badge — replaces the old
     * is_verified boolean; checked by name since badges are now a plain
     * admin-managed list rather than fixed flags.
     */
    public function isVerified(): bool
    {
        return $this->relationLoaded('badges')
            ? $this->badges->contains('name', 'Verified')
            : $this->badges()->where('name', 'Verified')->exists();
    }
}
