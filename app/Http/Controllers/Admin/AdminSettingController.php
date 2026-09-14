<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class AdminSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.admin-settings.edit', [
            'settings' => AdminSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'admin_panel_path' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/', 'not_in:api'],
        ]);

        $settings = AdminSetting::current();
        $settings->admin_panel_path = $data['admin_panel_path'];
        $settings->save();

        // The prefix is baked into the route collection at boot — clear the
        // cache so an uncached request picks up the new value immediately.
        // A production `route:cache` (part of the normal deploy flow) still
        // needs re-running afterwards to bake it into a cached route file.
        Artisan::call('route:clear');

        $newPath = $data['admin_panel_path'];

        return redirect("/{$newPath}/admin-settings")->with('status', "Admin panel URL updated to /{$newPath}. Bookmarks pointing at the old URL will stop working.");
    }
}
