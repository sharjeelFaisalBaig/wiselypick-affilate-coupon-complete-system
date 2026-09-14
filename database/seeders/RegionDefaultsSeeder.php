<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\ContactPageAgenda;
use App\Models\GeneralSetting;
use App\Models\Menu;
use App\Models\PageSetting;
use App\Models\Region;
use App\Models\StoreSuffix;
use Illuminate\Database\Seeder;

/**
 * Mirrors RegionController::seedDefaultsForNewRegion() (used when an admin
 * creates a region through the UI) for regions created directly by
 * RegionSeeder on a fresh install — without this, a freshly-seeded
 * database ends up with 0 menu items (header/footer nav renders with no
 * links at all — no hardcoded fallback exists), 0 badges (the offer form's
 * Coupon Features checkboxes have nothing to pick), 0 contact page
 * agendas, and 0 page settings (headings silently fall back to hardcoded
 * text instead of the real seeded copy).
 */
class RegionDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        Region::all()->each(function (Region $region) {
            Menu::seedDefaultItemsFor($region);
            Badge::seedDefaultsFor($region);
            ContactPageAgenda::seedDefaultsFor($region);
            PageSetting::seedDefaultsFor($region);
            StoreSuffix::seedDefaultsFor($region);
            GeneralSetting::seedDefaultsFor($region);
        });
    }
}
