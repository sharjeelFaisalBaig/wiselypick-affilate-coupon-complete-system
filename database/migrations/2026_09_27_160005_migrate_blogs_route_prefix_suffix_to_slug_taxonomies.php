<?php

use App\Models\Blog;
use App\Models\BlogSlugPrefix;
use App\Models\BlogSlugSuffix;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** See migrate_stores_route_prefix_suffix_to_slug_taxonomies — identical strategy, for blogs. */
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->foreignId('blog_slug_prefix_id')->nullable()->after('route_suffix')->constrained('blog_slug_prefixes')->nullOnDelete();
            $table->foreignId('blog_slug_suffix_id')->nullable()->after('blog_slug_prefix_id')->constrained('blog_slug_suffixes')->nullOnDelete();
        });

        Blog::whereNotNull('route_prefix')->get(['id', 'region_id', 'route_prefix'])->each(function (Blog $blog) {
            $prefix = BlogSlugPrefix::firstOrCreate(['region_id' => $blog->region_id, 'value' => $blog->route_prefix]);
            $blog->update(['blog_slug_prefix_id' => $prefix->id]);
        });

        Blog::whereNotNull('route_suffix')->get(['id', 'region_id', 'route_suffix'])->each(function (Blog $blog) {
            $suffix = BlogSlugSuffix::firstOrCreate(['region_id' => $blog->region_id, 'value' => $blog->route_suffix]);
            $blog->update(['blog_slug_suffix_id' => $suffix->id]);
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn(['route_prefix', 'route_suffix']);
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('route_prefix')->nullable()->after('slug');
            $table->string('route_suffix')->nullable()->after('route_prefix');
        });

        Blog::with('blogSlugPrefix', 'blogSlugSuffix')->get()->each(function (Blog $blog) {
            $blog->update([
                'route_prefix' => $blog->blogSlugPrefix?->value,
                'route_suffix' => $blog->blogSlugSuffix?->value,
            ]);
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('blog_slug_prefix_id');
            $table->dropConstrainedForeignId('blog_slug_suffix_id');
        });
    }
};
