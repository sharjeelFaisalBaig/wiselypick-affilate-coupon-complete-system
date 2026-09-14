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

        return back();
    }
}
