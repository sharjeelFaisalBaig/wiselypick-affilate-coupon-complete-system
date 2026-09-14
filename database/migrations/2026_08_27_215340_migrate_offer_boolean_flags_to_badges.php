<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Converts the legacy is_verified/is_exclusive/is_top_deal/is_featured
     * booleans into real Badge assignments before those columns are dropped
     * in the next migration. Uses the query builder (not the Offer/Badge
     * Eloquent models) so this stays correct even after the models stop
     * referencing the old boolean columns.
     */
    public function up(): void
    {
        $flagToBadgeName = [
            'is_verified' => 'Verified',
            'is_exclusive' => 'Exclusive',
            'is_top_deal' => 'Top Code',
            'is_featured' => "Editor's Pick",
        ];

        $badgeIdsByRegionAndName = DB::table('badges')->get()
            ->groupBy('region_id')
            ->map(fn ($badges) => $badges->pluck('id', 'name'));

        $now = now();

        DB::table('offers')->join('stores', 'stores.id', '=', 'offers.store_id')
            ->select('offers.id as offer_id', 'stores.region_id', 'offers.is_verified', 'offers.is_exclusive', 'offers.is_top_deal', 'offers.is_featured')
            ->orderBy('offers.id')
            ->chunk(500, function ($rows) use ($flagToBadgeName, $badgeIdsByRegionAndName, $now) {
                $pivotRows = [];

                foreach ($rows as $row) {
                    $badgeNamesByRegion = $badgeIdsByRegionAndName->get($row->region_id);
                    if (! $badgeNamesByRegion) {
                        continue;
                    }

                    foreach ($flagToBadgeName as $flag => $badgeName) {
                        if ($row->{$flag} && $badgeNamesByRegion->has($badgeName)) {
                            $pivotRows[] = [
                                'offer_id' => $row->offer_id,
                                'badge_id' => $badgeNamesByRegion->get($badgeName),
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                }

                if ($pivotRows) {
                    DB::table('offer_badge')->insertOrIgnore($pivotRows);
                }
            });
    }

    public function down(): void
    {
        // Irreversible by design — the booleans this reads from are dropped
        // in the very next migration.
    }
};
