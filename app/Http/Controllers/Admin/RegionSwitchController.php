<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegionSwitchController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'region_id' => ['required', 'exists:regions,id'],
        ]);

        $request->session()->put('admin_active_region_id', (int) $data['region_id']);

        // Redirect to the dashboard rather than back() — the previous page
        // is very often a specific entity's edit screen (a store, a blog
        // post, ...), which belongs to the region just switched AWAY from
        // and immediately 403s once GuardsRegionOwnership sees the new
        // active region on the very next request.
        return redirect()->route('admin.dashboard');
    }
}
