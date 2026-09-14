<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Flat list of store categories — no parent/child concept anymore, so
     * the former "parent -> children" groupings are just flattened into one
     * list of independent category names.
     */
    public static array $names = [
        'Electronics', 'Computers & Laptops', 'Phones & Accessories', 'Smart Home', 'Audio & Headphones',
        'Fashion & Apparel', "Men's Clothing", "Women's Clothing", 'Shoes', 'Accessories',
        'Beauty & Personal Care', 'Skin Care', 'Makeup', 'Hair Care', 'Fragrances', 'Nail Care',
        'Health & Wellness', 'Vitamins & Supplements', 'Fitness Equipment', 'Personal Care',
        'Travel', 'Flights', 'Hotels', 'Car Rentals', 'Vacation Packages',
        'Home & Garden', 'Furniture', 'Kitchen & Dining', 'Outdoor & Garden',
        'Food & Restaurants', 'Meal Kits', 'Grocery Delivery', 'Restaurant Deals',
        'Sports & Outdoors', 'Camping & Hiking', 'Team Sports', 'Fitness Apparel',
    ];

    public function run(): void
    {
        Region::all()->each(function (Region $region) {
            foreach (self::$names as $order => $name) {
                Category::updateOrCreate(
                    ['region_id' => $region->id, 'type' => 'store', 'slug' => Str::slug($name)],
                    [
                        'name' => $name,
                        'sort_order' => $order + 1,
                        'is_active' => true,
                    ]
                );
            }
        });
    }
}
