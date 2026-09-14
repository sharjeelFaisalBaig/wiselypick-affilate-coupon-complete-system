<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['code' => 'us', 'name' => 'United States', 'sort_order' => 1, 'is_default' => true],
            ['code' => 'uk', 'name' => 'United Kingdom', 'sort_order' => 2, 'is_default' => false],
            ['code' => 'au', 'name' => 'Australia', 'sort_order' => 3, 'is_default' => false],
        ];

        foreach ($regions as $region) {
            Region::updateOrCreate(['code' => $region['code']], $region + [
                'is_active' => true,
            ]);
        }
    }
}
