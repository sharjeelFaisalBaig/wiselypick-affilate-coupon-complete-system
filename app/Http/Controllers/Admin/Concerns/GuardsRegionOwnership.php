<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Region;
use Illuminate\Http\Request;

trait GuardsRegionOwnership
{
    /**
     * Abort with 403 if the given model's region_id doesn't match the
     * currently active admin region — prevents editing another region's
     * record by guessing its route-bound id.
     */
    protected function abortUnlessOwnedByActiveRegion(Request $request, int $regionId): void
    {
        /** @var Region $activeRegion */
        $activeRegion = $request->attributes->get('activeRegion');

        abort_if($regionId !== $activeRegion->id, 403, 'This record belongs to a different region.');
    }
}
