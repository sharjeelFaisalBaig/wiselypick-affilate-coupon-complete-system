<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Region;
use App\Models\Store;
use App\Models\StoreSuffix;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        Region::all()->each(function (Region $region) {
            $categories = Category::where('region_id', $region->id)->where('type', 'store')->get();
            $defaultSuffixId = StoreSuffix::where('region_id', $region->id)
                ->where('name', 'Promo Codes, Coupons & Deals')->value('id');

            $featuredCount = 0;
            $popularCount = 0;

            foreach ($categories as $category) {
                for ($i = 0; $i < 3; $i++) {
                    $store = Store::factory()->create([
                        'region_id' => $region->id,
                        'category_id' => $category->id,
                        'store_suffix_id' => $defaultSuffixId,
                    ]);

                    if ($store->is_featured) {
                        $store->update(['featured_order' => ++$featuredCount]);
                    }

                    if ($store->is_popular) {
                        $store->update(['popular_order' => ++$popularCount]);
                    }
                }
            }

            // A handful of Pending stores per region so the classification
            // screen's "Pending Stores" tab has real demo content out of the
            // box, rather than looking broken/empty on a fresh install.
            Store::where('region_id', $region->id)->inRandomOrder()->limit(4)->get()
                ->each(function (Store $store, int $index) {
                    $store->update(['is_pending' => true, 'pending_order' => $index + 1]);
                });
        });
    }
}
