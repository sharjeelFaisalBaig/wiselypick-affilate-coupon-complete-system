<?php

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Blog>
 *
 * region_id and blog_category_id have no default here — the caller (BlogSeeder)
 * must pass them explicitly.
 */
class BlogFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(6);
        $paragraphs = fake()->paragraphs(8);
        $content = collect($paragraphs)->map(fn ($p) => "<p>{$p}</p>")->implode("\n");

        $toc = [
            ['text' => 'Overview', 'anchor' => 'overview', 'level' => 'h2'],
            ['text' => 'How To Save More', 'anchor' => 'how-to-save-more', 'level' => 'h2'],
            ['text' => 'Frequently Asked Questions', 'anchor' => 'faq', 'level' => 'h2'],
        ];

        $faqs = [
            ['question' => 'How often are new codes added?', 'answer' => fake()->sentence(15)],
            ['question' => 'Do coupon codes expire?', 'answer' => fake()->sentence(15)],
        ];

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'excerpt' => fake()->sentence(20),
            'content' => $content,
            'featured_image' => null,
            'author_name' => fake()->name(),
            'author_avatar' => null,
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'reading_time_minutes' => (int) max(1, round(str_word_count(strip_tags($content)) / 200)),
            'toc' => $toc,
            'faqs' => $faqs,
            'is_published' => true,
            'meta_title' => $title,
            'meta_description' => fake()->sentence(20),
            'og_title' => $title,
            'og_image' => null,
            'robots_index' => true,
            'robots_follow' => true,
            'schema_type' => 'BlogPosting',
            'auto_compress_images' => true,
            'convert_to_webp' => true,
            'enable_amp' => false,
            'auto_link_related_blogs' => true,
        ];
    }
}
