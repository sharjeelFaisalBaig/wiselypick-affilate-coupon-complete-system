<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Region;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        Region::all()->each(function (Region $region) {
            $blogCategories = BlogCategory::where('region_id', $region->id)->get();
            $blogs = collect();

            for ($i = 0; $i < 5; $i++) {
                $blogs->push(Blog::factory()->create([
                    'region_id' => $region->id,
                    'blog_category_id' => $blogCategories->random()->id,
                    'sort_order' => $i + 1,
                ]));
            }

            // Demonstrate the manual picker on one post per region by
            // turning its auto-linking off and hand-picking 2 others.
            $manual = $blogs->first();
            $manual->update(['auto_link_related_blogs' => false]);
            $others = $blogs->where('id', '!=', $manual->id)->take(2)->values();
            foreach ($others as $index => $other) {
                $manual->relatedBlogs()->attach($other->id, ['sort_order' => $index + 1]);
            }
        });
    }
}
