<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Offer;
use App\Models\Region;
use App\Models\Store;
use Illuminate\Database\Seeder;

class HomepageSectionSeeder extends Seeder
{
    public function run(): void
    {
        Region::all()->each(function (Region $region) {
            $order = 0;

            $trendingDeals = HomepageSection::create([
                'region_id' => $region->id,
                'title' => 'Trending Deals',
                'content_type' => 'deal',
                'cta_label' => 'Explore all',
                'cta_url' => '/exclusive/coupons',
                'sort_order' => ++$order,
                'is_active' => true,
            ]);
            $this->attachOffers($trendingDeals, $region->id, 'deal');

            $topCoupons = HomepageSection::create([
                'region_id' => $region->id,
                'title' => 'Top Coupons',
                'content_type' => 'coupon',
                'cta_label' => 'Show all',
                'cta_url' => '/exclusive/coupons',
                'sort_order' => ++$order,
                'is_active' => true,
            ]);
            $this->attachOffers($topCoupons, $region->id, 'coupon');

            $trendingStores = HomepageSection::create([
                'region_id' => $region->id,
                'title' => 'Trending Stores',
                'content_type' => 'store',
                'cta_label' => 'Browse stores',
                'cta_url' => '/exclusive/stores',
                'sort_order' => ++$order,
                'is_active' => true,
            ]);

            $stores = Store::where('region_id', $region->id)->where('is_active', true)
                ->orderByDesc('is_featured')->orderByDesc('is_popular')
                ->take(HomepageSection::MAX_STORES)->get();

            foreach ($stores as $index => $store) {
                $trendingStores->stores()->attach($store->id, ['sort_order' => $index + 1]);
            }

            $topPerforming = HomepageSection::create([
                'region_id' => $region->id,
                'title' => 'Top Performing Coupons',
                'content_type' => 'ranked',
                'cta_label' => 'View all',
                'cta_url' => '/exclusive/coupons',
                'sort_order' => ++$order,
                'is_active' => true,
            ]);
            $this->attachOffers($topPerforming, $region->id, 'coupon');

            $topCategories = HomepageSection::create([
                'region_id' => $region->id,
                'title' => 'Top Categories',
                'content_type' => 'categories',
                'cta_label' => 'All categories',
                'cta_url' => '/exclusive/stores',
                'sort_order' => ++$order,
                'is_active' => true,
            ]);

            $categories = Category::where('region_id', $region->id)->where('type', 'store')->where('is_active', true)
                ->orderBy('sort_order')->take(HomepageSection::MAX_CATEGORIES)->get();

            foreach ($categories as $index => $category) {
                $topCategories->categories()->attach($category->id, ['sort_order' => $index + 1]);
            }
        });
    }

    private function attachOffers(HomepageSection $section, int $regionId, string $offerType): void
    {
        $offers = Offer::where('offer_type', $offerType)->where('is_active', true)
            ->whereHas('store', fn ($q) => $q->where('region_id', $regionId))
            ->inRandomOrder()
            ->take(HomepageSection::MAX_OFFERS)
            ->get();

        foreach ($offers as $index => $offer) {
            $section->offers()->attach($offer->id, ['sort_order' => $index + 1]);
        }
    }
}
