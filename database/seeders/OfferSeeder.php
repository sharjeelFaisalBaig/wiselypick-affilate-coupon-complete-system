<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Region;
use App\Models\Store;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        Store::all()->each(function (Store $store) {
            $count = fake()->numberBetween(2, 4);

            for ($i = 0; $i < $count; $i++) {
                Offer::factory()->create([
                    'store_id' => $store->id,
                    'sort_order' => $i + 1,
                ]);
            }
        });

        // A handful of Featured Deals per region so the classification
        // screen's "Featured Deals" tab has real demo content out of the
        // box, rather than looking broken/empty on a fresh install.
        Region::all()->each(function (Region $region) {
            Offer::whereHas('store', fn ($q) => $q->where('region_id', $region->id))
                ->where('is_active', true)
                ->inRandomOrder()
                ->limit(5)
                ->get()
                ->each(function (Offer $offer, int $index) {
                    $offer->update(['is_featured' => true, 'featured_order' => $index + 1]);
                });
        });
    }
}
