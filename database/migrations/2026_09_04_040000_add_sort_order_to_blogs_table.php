<?php

use App\Models\Blog;
use App\Models\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('blog_category_id');
        });

        // Backfill so existing posts keep their current effective order
        // (newest published first, matching the admin listing's prior
        // `latest('published_at')` ordering) once the listing switches to
        // ordering by this column.
        Region::all()->each(function (Region $region) {
            Blog::where('region_id', $region->id)->orderByDesc('published_at')->get()
                ->each(function (Blog $blog, int $index) {
                    $blog->update(['sort_order' => $index + 1]);
                });
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
