<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageSetting;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageSettingController extends Controller
{
    public const PAGES = [
        'home' => 'Homepage (/exclusive)',
        'stores' => 'Stores Directory (/exclusive/stores)',
        'coupons' => 'Promo Codes (/exclusive/coupons)',
        'blogs' => 'Blog Listing (/)',
    ];

    /**
     * Each page's heading/SEO/scripts lives on its own screen — a single
     * page mixing all 4 pages' settings together read as one generic,
     * confusing "Page Settings" catch-all.
     */
    public function edit(Request $request, string $pageKey): View
    {
        abort_unless(array_key_exists($pageKey, self::PAGES), 404);

        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $settings = PageSetting::forPage($region->id, $pageKey) ?? new PageSetting(['page_key' => $pageKey]);

        return view('admin.page-settings.edit', [
            'settings' => $settings,
            'pageKey' => $pageKey,
            'pageLabel' => self::PAGES[$pageKey],
        ]);
    }

    public function update(Request $request, string $pageKey): RedirectResponse
    {
        abort_unless(array_key_exists($pageKey, self::PAGES), 404);

        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            // Slashes are allowed now (e.g. "coupons/usa-promotions") — no
            // leading/trailing slash, and no doubled "//" segments.
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^([a-z0-9-]+(\/[a-z0-9-]+)*)?$/'],
            'heading' => ['nullable', 'string', 'max:255'],
            'subheading' => ['nullable', 'string', 'max:1000'],
            'hero_search_placeholder' => ['nullable', 'string', 'max:255'],
            'hero_search_button_text' => ['nullable', 'string', 'max:50'],
            'hero_badge_text' => ['nullable', 'string', 'max:100'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:255'],
            'schema_script' => ['nullable', 'string'],
            'head_start_script' => ['nullable', 'string'],
            'head_end_script' => ['nullable', 'string'],
            'body_start_script' => ['nullable', 'string'],
            'body_end_script' => ['nullable', 'string'],
        ]);

        // "" is a real, meaningful value here (serve at the region root),
        // not "no value" — but Laravel's global ConvertEmptyStringsToNull
        // middleware already turned a blank field into null before
        // validation ran, so uniqueness against that real (possibly blank)
        // string has to be checked by hand rather than via Rule::unique
        // (which would compare against the wrong, already-nulled value).
        $data['slug'] = $data['slug'] ?? '';

        $conflict = PageSetting::where('region_id', $region->id)
            ->where('slug', $data['slug'])
            ->where('page_key', '!=', $pageKey)
            ->exists();

        if ($conflict) {
            $message = $data['slug'] === ''
                ? 'Another page is already set as this region\'s root — change its slug first.'
                : 'This URL slug is already used by another page in this region.';

            return back()->withInput()->withErrors(['slug' => $message]);
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['robots_index'] = $request->boolean('robots_index');
        $data['robots_follow'] = $request->boolean('robots_follow');

        PageSetting::updateOrCreate(
            ['region_id' => $region->id, 'page_key' => $pageKey],
            $data
        );

        return redirect()->route('admin.page-settings.edit', $pageKey)->with('status', self::PAGES[$pageKey].' settings updated.');
    }
}
