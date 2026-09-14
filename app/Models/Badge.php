<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    /**
     * Starting rows seeded per region — an open-ended, admin-editable
     * taxonomy (no cap on how many can exist), not protected defaults
     * (only protected from deletion while assigned to an active promotion).
     */
    public const SEED_NAMES = ['Verified', 'Exclusive', 'Top Deals'];

    protected $fillable = [
        'region_id',
        'name',
        'color',
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
        return $this->belongsToMany(Offer::class, 'offer_badge');
    }

    public static function seedDefaultsFor(Region $region): void
    {
        foreach (self::SEED_NAMES as $name) {
            self::firstOrCreate(['region_id' => $region->id, 'name' => $name]);
        }
    }

    /**
     * Purely presentational — the 3 seeded names get their historical pill
     * colors as a Tailwind class fallback for badges with no admin-picked
     * color; anything custom (including a custom color on a seeded name)
     * renders via inline style() instead, so classes() and style() are
     * mutually exclusive in the view (see offer-card* partials).
     */
    public function classes(): string
    {
        return match ($this->name) {
            'Top Deals' => 'bg-amber-50 text-amber-700',
            'Exclusive' => 'bg-purple-50 text-purple-700',
            'Verified' => 'bg-sky-50 text-sky-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    /**
     * Inline pill style built from the admin-picked hex color — a light
     * tint background + the full color for text/border, matching the same
     * visual weight as the classes() Tailwind pairs above.
     */
    public function style(): ?string
    {
        if (! $this->color) {
            return null;
        }

        return "background-color: {$this->color}1a; color: {$this->color}; border: 1px solid {$this->color}4d;";
    }
}
