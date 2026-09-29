<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaticPage extends Model
{
    use HasFactory;

    public const DEFAULT_ROUTE_PREFIX = '';

    /**
     * Placeholder content seeded for every fresh region (SRS §1: "default
     * page shells with placeholder content"). `page_type` is load-bearing —
     * StaticPageController::show() and Admin\PagesOverviewController key off
     * it (not the freely-editable `slug`) to find "the" contact/terms/
     * privacy page and to render the contact form, so an admin can rename
     * any of these pages' URLs freely without breaking either.
     */
    public const SEED_DEFAULTS = [
        [
            'slug' => 'contact',
            'page_type' => 'contact',
            'title' => 'Contact Us',
            'content' => '<p>Have a question, found a bug, or want to partner with us? Fill out the form below and our team will get back to you.</p>',
        ],
        [
            'slug' => 'terms-of-use',
            'page_type' => 'terms',
            'title' => 'Terms of Use',
            'content' => '<p>These Terms of Use govern your use of our coupon and deals platform. By using this site you agree to these terms.</p>',
        ],
        [
            'slug' => 'privacy-policy',
            'page_type' => 'privacy',
            'title' => 'Privacy Policy',
            'content' => '<p>This Privacy Policy describes how we collect, use, and handle your information when you use our site.</p>',
        ],
    ];

    protected $fillable = [
        'region_id',
        'slug',
        'page_slug_prefix_id',
        'page_slug_suffix_id',
        // Deliberately NOT 'page_type' — that flag is only ever set by
        // seedDefaultsFor() below, never from the admin form, so a page
        // can't accidentally become (or stop being) "the" contact page via
        // mass assignment.
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

    /**
     * The "{prefix}/{slug}[/{suffix}]" path segment after "/{region}/" — see
     * Store::path()/Blog::path() for the identical rationale (per-entity
     * override of the "(none)" default, picked from the admin-managed
     * PageSlugPrefix/PageSlugSuffix taxonomies rather than a fixed "/p/"
     * route literal).
     */
    public function path(): string
    {
        $prefix = trim($this->pageSlugPrefix?->value ?: self::DEFAULT_ROUTE_PREFIX, '/');
        $path = $prefix !== '' ? "{$prefix}/{$this->slug}" : $this->slug;

        if ($this->pageSlugSuffix?->value) {
            $path .= '/'.trim($this->pageSlugSuffix->value, '/');
        }

        return $path;
    }

    public function urlFor(Region $region): string
    {
        return $region->publicUrl($this->path());
    }

    /** See Store::resolveByPath()/Blog::resolveByPath() for the identical strategy. */
    public static function resolveByPath(Region $region, string $path): ?self
    {
        $path = trim($path, '/');
        if ($path === '') {
            return null;
        }
        $segments = explode('/', $path);

        $page = self::where('region_id', $region->id)->where('slug', end($segments))->where('is_active', true)->first();
        if ($page && $page->path() === $path) {
            return $page;
        }

        if (count($segments) >= 2) {
            $page = self::where('region_id', $region->id)->where('slug', $segments[count($segments) - 2])->where('is_active', true)->first();
            if ($page && $page->path() === $path) {
                return $page;
            }
        }

        return null;
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function pageSlugPrefix(): BelongsTo
    {
        return $this->belongsTo(PageSlugPrefix::class);
    }

    public function pageSlugSuffix(): BelongsTo
    {
        return $this->belongsTo(PageSlugSuffix::class);
    }

    public static function seedDefaultsFor(Region $region): void
    {
        foreach (self::SEED_DEFAULTS as $page) {
            $pageType = $page['page_type'];
            $attributes = collect($page)->except('page_type')->all();

            $model = self::firstOrCreate(
                ['region_id' => $region->id, 'slug' => $attributes['slug']],
                $attributes + [
                    'meta_title' => $page['title'],
                    'meta_description' => $page['title'].' — '.$region->name,
                    'og_title' => $page['title'],
                    'og_description' => $page['title'].' — '.$region->name,
                    'robots_index' => true,
                    'robots_follow' => true,
                    'is_active' => true,
                ]
            );

            if (! $model->page_type) {
                $model->forceFill(['page_type' => $pageType])->save();
            }
        }
    }
}
