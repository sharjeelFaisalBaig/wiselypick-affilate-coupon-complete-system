<?php

namespace Database\Seeders;

use App\Models\AffiliateNetwork;
use App\Models\Region;
use Illuminate\Database\Seeder;

class AffiliateNetworkSeeder extends Seeder
{
    public function run(): void
    {
        Region::all()->each(function (Region $region) {
            foreach (['Amazon Associates', 'eBay Partner Network', 'CJ Affiliate'] as $network) {
                AffiliateNetwork::updateOrCreate(
                    ['region_id' => $region->id, 'network_name' => $network],
                    [
                        'script' => null,
                        'placement' => 'head_end',
                        'is_active' => false,
                    ]
                );
            }
        });
    }
}
