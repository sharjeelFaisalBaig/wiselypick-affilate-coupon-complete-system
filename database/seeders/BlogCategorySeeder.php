<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogCategorySeeder extends Seeder
{
    public static array $names = [
        'Shopping Tips',
        'Deals News',
        'Seasonal Sales',
    ];

    public function run(): void
    {
        Region::all()->each(function (Region $region) {
            foreach (self::$names as $index => $name) {
                BlogCategory::updateOrCreate(
                    ['region_id' => $region->id, 'slug' => Str::slug($name)],
                    ['name' => $name, 'sort_order' => $index + 1, 'is_active' => true]
                );
            }
        });
    }
}
