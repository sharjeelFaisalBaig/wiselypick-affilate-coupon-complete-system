<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Store>
 *
 * region_id and category_id have no default here — the caller (StoreSeeder)
 * must always pass them explicitly.
 */
class StoreFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        $about = collect([
            'Overview' => fake()->paragraph(4),
            'Products & Services' => fake()->paragraph(3),
            'Why Shop Here' => fake()->paragraph(2),
        ])->map(fn ($paragraph, $heading) => "<h3>{$name} {$heading}</h3><p>{$paragraph}</p>")->implode('');

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'logo_path' => null,
            'about' => $about,
            'affiliate_url' => 'https://www.'.Str::slug($name).'.com/?ref=affiliate-demo',
            'expiry_date' => fake()->optional(0.3)->dateTimeBetween('now', '+1 year'),
            'star_rating' => fake()->randomFloat(1, 3.5, 5.0),
            'reviews_count' => fake()->numberBetween(10, 5000),
            'is_featured' => fake()->boolean(20),
            'featured_order' => 0,
            'is_popular' => fake()->boolean(30),
            'popular_order' => 0,
            'is_active' => true,
            'meta_title' => "{$name} Promo Codes, Coupons & Deals",
            'meta_description' => "Save with the latest verified {$name} coupon codes and deals.",
            'meta_keywords' => null,
            'og_title' => "{$name} Promo Codes, Coupons & Deals",
            'og_description' => "Save with the latest verified {$name} coupon codes and deals.",
            'og_image' => null,
            'robots_index' => true,
            'robots_follow' => true,
        ];
    }
}
