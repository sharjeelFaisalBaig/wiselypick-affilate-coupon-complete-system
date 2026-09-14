<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactPageAgenda extends Model
{
    use HasFactory;

    public const SEED_DEFAULTS = [
        ['value' => 'bug', 'label' => 'Bug or problem.', 'description' => "I'm experiencing a problem with the website or my account."],
        ['value' => 'suggestion', 'label' => 'Suggestion or feedback.', 'description' => 'I have a suggestion for improving the site.'],
        ['value' => 'partnership', 'label' => 'Partnerships or advertising.', 'description' => "I'm interested in partnership or advertising opportunities."],
        ['value' => 'general', 'label' => 'General question.', 'description' => 'Other question or inquiry.'],
    ];

    protected $fillable = [
        'region_id',
        'value',
        'label',
        'description',
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

    public static function seedDefaultsFor(Region $region): void
    {
        foreach (self::SEED_DEFAULTS as $i => $agenda) {
            self::firstOrCreate(
                ['region_id' => $region->id, 'value' => $agenda['value']],
                $agenda + ['sort_order' => $i + 1]
            );
        }
    }
}
