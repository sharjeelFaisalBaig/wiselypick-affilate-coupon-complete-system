<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Region;
use App\Models\Store;
use App\Models\StoreSuffix;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StoreSeeder extends Seeder
{
    /**
     * Realistic (but not real-brand) store names, grouped under each of
     * CategorySeeder::$names' category names so every seeded store reads as
     * a plausible retailer for the category it's filed under, rather than
     * Faker's generic fake-company-name generator.
     */
    public static array $storesByCategory = [
        'Electronics' => ['TechNova', 'CircuitHub', 'ByteWorks'],
        'Computers & Laptops' => ['LaptopLoft', 'CoreByte Computers', 'SwiftDesk PC'],
        'Phones & Accessories' => ['MobileEdge', 'PhoneCase Co.', 'SnapCharge'],
        'Smart Home' => ['HomeSense Smart', 'NestWise', 'SmartDwell'],
        'Audio & Headphones' => ['EchoTone Audio', 'BassCraft', 'PureSound Co.'],
        'Fashion & Apparel' => ['Urban Threads', 'Vestura', 'Drift Apparel Co.'],
        "Men's Clothing" => ['Tailored Line', 'GentClo', 'Rugged Fit Co.'],
        "Women's Clothing" => ['Bloom Boutique', 'Aria Style', 'Willow & Co.'],
        'Shoes' => ['StrideWalk', 'SoleCraft', 'PaceRunner Shoes'],
        'Accessories' => ['Lumen Accessories', 'Clasp & Co.', 'Satchel Studio'],
        'Beauty & Personal Care' => ['GlowLab Beauty', 'PureSkin Co.', 'Radiance Beauty'],
        'Skin Care' => ['DermaGlow', 'ClearDay Skincare', 'Botanica Skin'],
        'Makeup' => ['Palette Pro', 'VelvetGlow Cosmetics', 'Muse Makeup'],
        'Hair Care' => ['SilkStrand', 'LustreLocks', 'RootCare Co.'],
        'Fragrances' => ['Scent Atelier', 'Bloomwood Fragrances', 'Amberly Perfumes'],
        'Nail Care' => ['PolishPoint', 'LacquerLane', 'NailNook'],
        'Health & Wellness' => ['VitalWell', 'PureBalance', 'ThriveWell Co.'],
        'Vitamins & Supplements' => ['NutraCore', 'DailyDose Vitamins', 'VitaBoost'],
        'Fitness Equipment' => ['IronForge Fitness', 'FlexGear', 'PowerBench Co.'],
        'Personal Care' => ['Everyday Essentials', 'CareWell Co.', 'FreshStart Personal Care'],
        'Travel' => ['Wanderlux Travel', 'Skyline Journeys', 'RoamWell'],
        'Flights' => ['JetFare', 'SkyRoute Air Deals', 'AeroSaver'],
        'Hotels' => ['StayNest Hotels', 'HavenRest', 'Rooms & Retreats'],
        'Car Rentals' => ['DriveEasy Rentals', 'RoadReady Cars', 'WheelWay Rentals'],
        'Vacation Packages' => ['Sunset Escapes', 'Getaway Collective', 'Horizon Vacations'],
        'Home & Garden' => ['HomeHaven', 'GreenNest Living', 'Hearth & Home Co.'],
        'Furniture' => ['Oakwood Furnishings', 'ModernNest Furniture', 'CraftLine Home'],
        'Kitchen & Dining' => ['KitchenCraft', 'TableSet Co.', 'SavorHome Kitchen'],
        'Outdoor & Garden' => ['GardenGrove', 'TerraBloom Outdoor', 'YardCraft'],
        'Food & Restaurants' => ['TasteTown', 'FlavorFleet', 'DineDirect'],
        'Meal Kits' => ['FreshPlate Kits', 'ChefBox', 'PrepEasy Meals'],
        'Grocery Delivery' => ['QuickCart Grocery', 'FreshBasket Delivery', 'PantryDash'],
        'Restaurant Deals' => ['TableSaver', 'DineOut Deals', 'ForkFinds'],
        'Sports & Outdoors' => ['PeakGear', 'TrailForce Sports', 'SummitLine Outdoors'],
        'Camping & Hiking' => ['Basecamp Co.', 'WildTrail Gear', 'PineRidge Camping'],
        'Team Sports' => ['CourtSide Sports', 'FieldForce', 'ProLeague Gear'],
        'Fitness Apparel' => ['FlexWear', 'MotionLine Apparel', 'PulseFit Clothing'],
    ];

    /**
     * Rotating About-copy templates (rather than Lorem Ipsum) — genuinely
     * readable, category-aware sentences so every store's info panel reads
     * like real retailer copy.
     */
    private static array $aboutTemplates = [
        '<p>%1$s is an online destination for %2$s, offering a curated selection of quality products at competitive prices. Customers trust %1$s for reliable service, fast shipping, and regularly refreshed deals across its catalog.</p><p>Whether you\'re shopping for something specific or just browsing, %1$s makes it easy to find what you need.</p>',
        '<p>Since opening its doors online, %1$s has built a reputation for %2$s that combines value with quality. The brand focuses on a hassle-free shopping experience, transparent pricing, and responsive customer support.</p><p>%1$s regularly rotates its promotions, so shoppers can count on fresh savings throughout the year.</p>',
        '<p>%1$s specializes in %2$s, curating products that balance style, function, and affordability. With a growing catalog and a loyal customer base, %1$s has become a go-to option for shoppers looking for dependable quality without the premium price tag.</p>',
        '<p>As a trusted name in %2$s, %1$s combines thoughtful product selection with everyday value. The team behind %1$s is committed to fast fulfillment and easy returns, making it a low-risk choice for first-time shoppers and repeat customers alike.</p>',
    ];

    public function run(): void
    {
        Region::all()->each(function (Region $region) {
            $defaultSuffixId = StoreSuffix::where('region_id', $region->id)
                ->where('name', 'Promo Codes, Coupons & Deals')->value('id');

            $featuredCount = 0;
            $popularCount = 0;
            $storeIndex = 0;

            foreach (self::$storesByCategory as $categoryName => $storeNames) {
                $category = Category::where('region_id', $region->id)->where('type', 'store')
                    ->where('name', $categoryName)->first();

                if (! $category) {
                    continue;
                }

                foreach ($storeNames as $name) {
                    $about = sprintf(
                        self::$aboutTemplates[$storeIndex % count(self::$aboutTemplates)],
                        $name,
                        strtolower($categoryName)
                    );

                    $isFeatured = $storeIndex % 5 === 0;
                    $isPopular = $storeIndex % 4 === 1;

                    $store = Store::create([
                        'region_id' => $region->id,
                        'category_id' => $category->id,
                        'store_suffix_id' => $defaultSuffixId,
                        'name' => $name,
                        'slug' => Str::slug($name),
                        'about' => $about,
                        'affiliate_url' => 'https://www.'.Str::slug($name).'.com/?ref=affiliate-demo',
                        'star_rating' => round(3.5 + ($storeIndex % 4) * 0.5, 1),
                        'reviews_count' => 50 + ($storeIndex * 37) % 4800,
                        'is_featured' => $isFeatured,
                        'featured_order' => $isFeatured ? ++$featuredCount : 0,
                        'is_popular' => $isPopular,
                        'popular_order' => $isPopular ? ++$popularCount : 0,
                        'is_active' => true,
                        'meta_title' => "{$name} Promo Codes, Coupons & Deals",
                        'meta_description' => "Save with the latest verified {$name} coupon codes and deals.",
                        'og_title' => "{$name} Promo Codes, Coupons & Deals",
                        'og_description' => "Save with the latest verified {$name} coupon codes and deals.",
                        'robots_index' => true,
                        'robots_follow' => true,
                    ]);

                    $storeIndex++;
                }
            }

            // A handful of Pending stores per region so the classification
            // screen's "Pending Stores" tab has real demo content out of the
            // box, rather than looking broken/empty on a fresh install.
            Store::where('region_id', $region->id)->inRandomOrder()->limit(4)->get()
                ->each(function (Store $store, int $index) {
                    $store->update(['is_active' => false, 'pending_order' => $index + 1]);
                });
        });
    }
}
