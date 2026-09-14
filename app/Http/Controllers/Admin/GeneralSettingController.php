<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GeneralSettingController extends Controller
{
    public function edit(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        return view('admin.general-settings.edit', [
            'settings' => GeneralSetting::forRegion($region->id),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'footer_text' => ['nullable', 'string'],
            'footer_disclaimer' => ['nullable', 'string'],
            'store_page_disclaimer' => ['nullable', 'string'],
            'rights_text' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:512'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'deal_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'dark_surface_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $settings = GeneralSetting::forRegion($region->id);

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('regions/logos', 'public');
        }

        unset($data['logo']);
        $data['region_id'] = $region->id;

        GeneralSetting::updateOrCreate(['region_id' => $region->id], $data);

        return redirect()->route('admin.general-settings.edit')->with('status', 'General settings updated.');
    }
}
