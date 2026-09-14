<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\StaticPage;
use Illuminate\Database\Seeder;

class StaticPageSeeder extends Seeder
{
    public function run(): void
    {
        Region::all()->each(fn (Region $region) => StaticPage::seedDefaultsFor($region));
    }
}
