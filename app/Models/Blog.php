<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    public const DEFAULT_ROUTE_PREFIX = '';

    protected $fillable = [
        'region_id',
        'blog_category_id',
        'blog_slug_prefix_id',
        'blog_slug_suffix_id',
        'starts_from_root',
        'sort_order',
        'title',
        'slug',
        'excerpt',
        'content_sections',
        'featured_image',
        'author_name',
        'author_avatar',
        'published_at',
        'reading_time_minutes',
        'toc',
        'faqs',
        'is_published',
        'meta_title',
        'meta_description',
        'og_title',
        'og_image',
        'robots_index',
        'robots_follow',
        'schema_type',
        'auto_compress_images',
        'convert_to_webp',
        'enable_amp',
        'auto_link_related_blogs',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'toc' => 'array',
            'faqs' => 'array',
            'content_sections' => 'array',
            'is_published' => 'boolean',
            'starts_from_root' => 'boolean',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'auto_compress_images' => 'boolean',
            'convert_to_webp' => 'boolean',
            'enable_amp' => 'boolean',
            'auto_link_related_blogs' => 'boolean',
        ];
    }

    /**
     * The "{prefix}/{slug}[/{suffix}]" path segment after "/{region}/" — see
     * Store::path() for the identical rationale (per-entity override of the
     * "blog"/(none) defaults, picked from the admin-managed
     * BlogSlugPrefix/BlogSlugSuffix taxonomies rather than free text).
     */
    public function path(): string
    {
        if ($this->starts_from_root) {
            $path = $this->slug;
        } else {
            $prefix = trim($this->blogSlugPrefix?->value ?: self::DEFAULT_ROUTE_PREFIX, '/');
            // DEFAULT_ROUTE_PREFIX is blank (blog posts sit directly at their
            // own slug, no "/blog/" segment) — guard against
            // "{$prefix}/{$slug}" producing a stray leading slash
            // ("/my-post") when there's no prefix to put before it.
            $path = $prefix !== '' ? "{$prefix}/{$this->slug}" : $this->slug;
        }

        if ($this->blogSlugSuffix?->value) {
            $path .= '/'.trim($this->blogSlugSuffix->value, '/');
        }

        return $path;
    }

    public function urlFor(Region $region): string
    {
        return $region->publicUrl($this->path());
    }

    /**
     * See Store::resolveByPath() — identical strategy, scoped to published
     * blogs instead of Store::scopeVisible().
     */
    public static function resolveByPath(Region $region, string $path): ?self
    {
        $path = trim($path, '/');
        if ($path === '') {
            return null;
        }
        $segments = explode('/', $path);

        // DEFAULT_ROUTE_PREFIX is blank, so an unprefixed post's whole path
        // IS just its slug — a single segment. The old ">= 2 segments"
        // floor assumed every post had at least a prefix segment ahead of
        // its slug, which silently made every default-prefix post
        // unreachable once the default became blank; trying "last segment"
        // as the slug candidate first (below) already covers that case
        // correctly on its own.
        $blog = self::where('region_id', $region->id)->where('slug', end($segments))->where('is_published', true)->first();
        if ($blog && $blog->path() === $path) {
            return $blog;
        }

        // With a suffix (or an explicit non-blank custom prefix): slug is
        // the second-to-last segment.
        if (count($segments) >= 2) {
            $blog = self::where('region_id', $region->id)->where('slug', $segments[count($segments) - 2])->where('is_published', true)->first();
            if ($blog && $blog->path() === $path) {
                return $blog;
            }
        }

        return null;
    }

    /**
     * content_sections with each section's `anchor` (its sidebar Quick
     * Link's scroll target) filled in — slugged from the section's title,
     * deduped against sibling sections sharing a title. Computed here
     * (rather than stored) so it's always correct even for sections that
     * never passed through BlogContentProcessor::processSections() — e.g.
     * ones backfilled straight from the old single-content-field data.
     */
    public function sectionsWithAnchors(): array
    {
        $usedAnchors = [];

        return collect($this->content_sections ?? [])->map(function (array $section) use (&$usedAnchors) {
            $anchor = Str::slug($section['title'] ?? '') ?: 'section';
            $original = $anchor;
            $suffix = 2;
            while (in_array($anchor, $usedAnchors, true)) {
                $anchor = "{$original}-{$suffix}";
                $suffix++;
            }
            $usedAnchors[] = $anchor;

            return ['title' => $section['title'] ?? '', 'content' => $section['content'] ?? '', 'anchor' => $anchor];
        })->all();
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function blogCategory(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class);
    }

    public function blogSlugPrefix(): BelongsTo
    {
        return $this->belongsTo(BlogSlugPrefix::class);
    }

    public function blogSlugSuffix(): BelongsTo
    {
        return $this->belongsTo(BlogSlugSuffix::class);
    }

    /**
     * Manually-picked related posts, shown in the sidebar only when
     * auto_link_related_blogs is off — when it's on, the sidebar instead
     * shows same-category posts ordered by updated_at DESC (computed in
     * Public\BlogController, not stored).
     */
    public function relatedBlogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_related_blog', 'blog_id', 'related_blog_id')
            ->withPivot('sort_order')
            ->orderBy('blog_related_blog.sort_order');
    }
}
