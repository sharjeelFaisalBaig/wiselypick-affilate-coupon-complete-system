<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaticPage extends Model
{
    use HasFactory;

    /**
     * Placeholder content seeded for every fresh region (SRS §1: "default
     * page shells with placeholder content"). 'contact' is load-bearing —
     * StaticPageController::show() special-cases this slug to render the
     * contact form, so a region without this row would 404 at /p/contact.
     */
    public const SEED_DEFAULTS = [
        [
            'slug' => 'contact',
            'title' => 'Contact Us',
            'content' => '<p>Have a question, found a bug, or want to partner with us? Fill out the form below and our team will get back to you.</p>',
        ],
        [
            'slug' => 'terms-of-use',
            'title' => 'Terms of Use',
            'content' => '<p>These Terms of Use govern your use of our coupon and deals platform. By using this site you agree to these terms.</p>',
        ],
        [
            'slug' => 'privacy-policy',
            'title' => 'Privacy Policy',
            'content' => '<p>This Privacy Policy describes how we collect, use, and handle your information when you use our site.</p>',
        ],
    ];

    protected $fillable = [
        'region_id',
        'slug',
        'title',
        'content',
        'meta_title',
        'meta_description',
        'og_title',
        'og_description',
        'og_image',
        'schema_script',
        'head_start_script',
        'head_end_script',
        'body_start_script',
        'body_end_script',
        'robots_index',
        'robots_follow',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public static function seedDefaultsFor(Region $region): void
    {
        foreach (self::SEED_DEFAULTS as $page) {
            self::firstOrCreate(
                ['region_id' => $region->id, 'slug' => $page['slug']],
                $page + [
                    'meta_title' => $page['title'],
                    'meta_description' => $page['title'].' — '.$region->name,
                    'og_title' => $page['title'],
                    'og_description' => $page['title'].' — '.$region->name,
                    'robots_index' => true,
                    'robots_follow' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
