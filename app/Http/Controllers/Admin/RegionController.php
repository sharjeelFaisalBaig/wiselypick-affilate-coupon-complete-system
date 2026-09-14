<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\ContactPageAgenda;
use App\Models\GeneralSetting;
use App\Models\Menu;
use App\Models\PageSetting;
use App\Models\Region;
use App\Models\StaticPage;
use App\Models\StoreSuffix;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegionController extends Controller
{
    public function index(): View
    {
        $regions = Region::withCount([
            'categories', 'stores', 'blogs', 'staticPages',
        ])->orderBy('sort_order')->get();

        return view('admin.regions.index', compact('regions'));
    }

    public function create(): View
    {
        return view('admin.regions.form', [
            'region' => new Region(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        // SRS: newly created regions are disabled by default until an admin
        // explicitly publishes them, and can never be created as the default.
        $data['is_active'] = false;
        $data['is_default'] = false;

        if ($request->hasFile('favicon')) {
            $data['favicon_path'] = $request->file('favicon')->store('regions/favicons', 'public');
        }

        if ($request->hasFile('flag')) {
            $data['flag_path'] = $request->file('flag')->store('regions/flags', 'public');
        }

        $region = Region::create($data);

        $this->seedDefaultsForNewRegion($region);

        return redirect()->route('admin.regions.index')->with('status', 'Region created (disabled until published).');
    }

    public function edit(Region $region): View
    {
        return view('admin.regions.form', [
            'region' => $region,
        ]);
    }

    public function update(Request $request, Region $region): RedirectResponse
    {
        $data = $this->validated($request, $region);

        // The default region can never be disabled — it's the fallback every
        // other region's users get redirected to. Its "Enabled" checkbox is
        // rendered disabled in the form, so force the value here rather than
        // reading the (unsubmitted) checkbox.
        $data['is_active'] = $region->is_default ? true : $request->boolean('is_active');

        if ($request->hasFile('favicon')) {
            if ($region->favicon_path) {
                Storage::disk('public')->delete($region->favicon_path);
            }
            $data['favicon_path'] = $request->file('favicon')->store('regions/favicons', 'public');
        }

        if ($request->hasFile('flag')) {
            if ($region->flag_path) {
                Storage::disk('public')->delete($region->flag_path);
            }
            $data['flag_path'] = $request->file('flag')->store('regions/flags', 'public');
        }

        $region->update($data);

        return redirect()->route('admin.regions.index')->with('status', 'Region updated.');
    }

    public function destroy(Region $region): RedirectResponse
    {
        if ($region->is_default) {
            return back()->with('error', 'The default region cannot be deleted. Make another region the default first.');
        }

        $relations = [
            'categories' => 'categories', 'stores' => 'stores', 'blogs' => 'blog posts',
            'blogCategories' => 'blog categories', 'staticPages' => 'pages',
            'contactMessages' => 'contact messages', 'affiliateNetworks' => 'affiliate networks',
            'scriptInjections' => 'script injections', 'homepageSections' => 'homepage sections',
            'pageSettings' => 'page settings',
        ];

        foreach ($relations as $relation => $label) {
            if ($region->{$relation}()->exists()) {
                return back()->with('error', "This region still has {$label} assigned to it and cannot be deleted. Reassign or remove them first.");
            }
        }

        if ($region->favicon_path) {
            Storage::disk('public')->delete($region->favicon_path);
        }

        if ($region->flag_path) {
            Storage::disk('public')->delete($region->flag_path);
        }

        $region->delete();

        return redirect()->route('admin.regions.index')->with('status', 'Region deleted.');
    }

    /**
     * Publishing a region and setting the default are deliberately separate
     * actions from the edit form (rather than form checkboxes) so the
     * "exactly one default, default is never disabled" rules can't be
     * violated by an ambiguous simultaneous form submission.
     */
    public function makeDefault(Region $region): RedirectResponse
    {
        if (! $region->is_active) {
            return back()->with('error', 'Only an enabled region can be made the default.');
        }

        Region::where('is_default', true)->update(['is_default' => false]);
        $region->update(['is_default' => true]);

        return back()->with('status', "{$region->name} is now the default region.");
    }

    public function toggleActive(Region $region): RedirectResponse
    {
        if ($region->is_default && $region->is_active) {
            return back()->with('error', 'The default region cannot be disabled. Make another region the default first.');
        }

        if ($region->is_active && Region::where('is_active', true)->where('id', '!=', $region->id)->doesntExist()) {
            return back()->with('error', 'At least one region must remain enabled.');
        }

        $region->update(['is_active' => ! $region->is_active]);

        return back()->with('status', $region->is_active ? 'Region enabled.' : 'Region disabled.');
    }

    /**
     * Fresh-region initialization (SRS §1): default navigation menus and
     * page shells with placeholder content. Pages framework (Phase 10)
     * still needs to add its own seeding step here once it lands.
     */
    private function seedDefaultsForNewRegion(Region $region): void
    {
        Menu::seedDefaultItemsFor($region);
        Badge::seedDefaultsFor($region);
        ContactPageAgenda::seedDefaultsFor($region);
        StaticPage::seedDefaultsFor($region);
        PageSetting::seedDefaultsFor($region);
        StoreSuffix::seedDefaultsFor($region);
        GeneralSetting::seedDefaultsFor($region);
    }

    private function validated(Request $request, ?Region $region = null): array
    {
        $data = $request->validate([
            'code' => [
                'required', 'string', 'min:2', 'max:4', 'alpha',
                Rule::unique('regions', 'code')->ignore($region),
            ],
            'name' => ['required', 'string', 'max:255'],
            'favicon' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:512'],
            'flag' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:512'],
            'head_start_script' => ['nullable', 'string'],
            'head_end_script' => ['nullable', 'string'],
            'body_start_script' => ['nullable', 'string'],
            'body_end_script' => ['nullable', 'string'],
            'canonical_base_url' => ['nullable', 'url:https,http', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['code'] = strtolower($data['code']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
