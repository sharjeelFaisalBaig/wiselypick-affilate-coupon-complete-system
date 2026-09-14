<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Blog extends Model
{
    use HasFactory;

    public const DEFAULT_ROUTE_PREFIX = 'blog';

    protected $fillable = [
        'region_id',
        'blog_category_id',
        'sort_order',
        'title',
        'slug',
        'route_prefix',
        'route_suffix',
        'excerpt',
        'content',
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
            'is_published' => 'boolean',
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
     * "blog"/(none) defaults).
     */
    public function path(): string
    {
        $prefix = trim($this->route_prefix ?: self::DEFAULT_ROUTE_PREFIX, '/');
        $path = "{$prefix}/{$this->slug}";

        if ($this->route_suffix) {
            $path .= '/'.trim($this->route_suffix, '/');
        }

        return $path;
    }

    public function urlFor(Region $region): string
    {
        return url("/{$region->code}/{$this->path()}");
    }

    /**
     * See Store::resolveByPath() — identical strategy, scoped to published
     * blogs instead of Store::scopeVisible().
     */
    public static function resolveByPath(Region $region, string $path): ?self
    {
        $path = trim($path, '/');
        $segments = explode('/', $path);
        if (count($segments) < 2) {
            return null;
        }

        $blog = self::where('region_id', $region->id)->where('slug', end($segments))->where('is_published', true)->first();
        if ($blog && $blog->path() === $path) {
            return $blog;
        }

        if (count($segments) >= 3) {
            $blog = self::where('region_id', $region->id)->where('slug', $segments[count($segments) - 2])->where('is_published', true)->first();
            if ($blog && $blog->path() === $path) {
                return $blog;
            }
        }

        return null;
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function blogCategory(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class);
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
