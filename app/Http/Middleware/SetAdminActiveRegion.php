<?php

namespace App\Http\Middleware;

use App\Models\Region;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetAdminActiveRegion
{
    public function handle(Request $request, Closure $next): Response
    {
        $regions = Region::orderBy('sort_order')->get();

        $activeRegion = $regions->firstWhere('id', $request->session()->get('admin_active_region_id'))
            ?? $regions->first();

        if ($activeRegion) {
            $request->session()->put('admin_active_region_id', $activeRegion->id);
        }

        $request->attributes->set('activeRegion', $activeRegion);

        View::share('activeRegion', $activeRegion);
        View::share('allRegions', $regions);

        return $next($request);
    }
}
