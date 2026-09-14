<?php

use App\Models\Badge;
use App\Models\Region;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Region::all()->each(function (Region $region) {
            Badge::firstOrCreate(['region_id' => $region->id, 'name' => 'Top Deals']);
        });
    }

    public function down(): void
    {
        Badge::where('name', 'Top Deals')->delete();
    }
};
