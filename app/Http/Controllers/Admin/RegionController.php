<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ValidatesAbsoluteUrlConflicts;
use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\ContactPageAgenda;
use App\Models\GeneralSetting;
use App\Models\Menu;
use App\Models\Offer;
use App\Models\PageSetting;
use App\Models\Region;
use App\Models\StaticPage;
use App\Models\StoreSuffix;
use App\Support\RegionCopier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegionController extends Controller
{
    use ValidatesAbsoluteUrlConflicts;

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

        $this->guardAgainstUrlConflict((new Region(['code' => $data['code']]))->publicUrl(), 'region', null, 'code');

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

        $this->guardAgainstUrlConflict((new Region(['code' => $data['code']]))->publicUrl(), 'region', $region->id, 'code');

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

        $codeChanged = $region->is_default && $data['code'] !== $region->code;

        $region->update($data);

        // Only the default region's code affects the root ("/") route
        // shape (item 18) — clear the cache so a blank-out/fill-in takes
        // effect on the very next request, same pattern as
        // AdminSettingController::update() for the admin panel path.
        if ($codeChanged) {
            Artisan::call('route:clear');
        }

        return redirect()->route('admin.regions.index')->with('status', 'Region updated.');
    }

    /**
     * The disclaimer + password/slug/name re-confirmation screen — deleting
     * a region cascades everything it owns (see destroy()), so this is
     * deliberately its own page rather than a JS confirm() dialog on the
     * index row, matching what was asked: an explicit disclaimer shown on
     * the delete click, not just a generic "are you sure?".
     */
    public function confirmDelete(Region $region): View|RedirectResponse
    {
        if ($region->is_default) {
            return redirect()->route('admin.regions.index')->with('error', 'The default region cannot be deleted. Make another region the default first.');
        }

        return view('admin.regions.confirm-delete', compact('region'));
    }

    /**
     * Cascades the region and every row that belongs to it. Every
     * region-scoped table's `region_id` foreign key is already
     * ->cascadeOnDelete() at the DB level (the one deliberate exception is
     * contact_messages, which ->nullOnDelete()s instead — historical
     * correspondence outlives the region it was sent about) — so a single
     * `$region->delete()` after the 3-factor confirmation below is enough
     * for every DB row; only the on-disk files it owns (directly, and via
     * every child store/blog/category/page it has) need cleaning up by
     * hand first, since cascading a row doesn't touch its file uploads.
     */
    public function destroy(Request $request, Region $region): RedirectResponse
    {
        if ($region->is_default) {
            return back()->with('error', 'The default region cannot be deleted. Make another region the default first.');
        }

        $request->validate([
            'password' => ['required', 'current_password'],
            'confirm_code' => ['required', 'string'],
            'confirm_name' => ['required', 'string'],
        ]);

        if ($request->string('confirm_code')->value() !== (string) $region->code) {
            return back()->withErrors(['confirm' => 'The slug you typed doesn\'t match this region\'s slug — nothing was deleted.'])->withInput();
        }

        if ($request->string('confirm_name')->value() !== $region->name) {
            return back()->withErrors(['confirm' => 'The name you typed doesn\'t match this region\'s name exactly — nothing was deleted.'])->withInput();
        }

        $regionName = $region->name;

        $this->deleteRegionFiles($region);

        // offers.store_id is deliberately restrictOnDelete (not cascade) —
        // see change_offers_store_id_to_restrict_on_delete's own docblock:
        // it's the DB-level backstop for "a store with active promotions
        // can't be deleted" (StoreController::destroy() enforces the same
        // rule at the app layer). A full region wipeout is exactly the case
        // that rule needs to be overridden for — the confirmation above IS
        // the admin's explicit consent to delete the offers too — so they
        // have to go first, or the region's own cascade to its stores would
        // hit that restrict and roll back the entire delete.
        Offer::whereIn('store_id', $region->stores()->pluck('id'))->delete();

        $region->delete();

        return redirect()->route('admin.regions.index')->with('status', "\"{$regionName}\" and everything that belonged to it has been permanently deleted.");
    }

    /**
     * Every on-disk file this region (or something it owns) references —
     * deleting the DB rows via cascade never touches these, so they'd
     * otherwise leak on disk forever. Mirrors the exact file fields
     * RegionCopier::copyFile() already knows about for this same region
     * shape.
     */
    private function deleteRegionFiles(Region $region): void
    {
        $disk = Storage::disk('public');

        foreach ([$region->favicon_path, $region->flag_path] as $path) {
            if ($path) {
                $disk->delete($path);
            }
        }

        if ($generalSetting = $region->generalSetting) {
            if ($generalSetting->logo_path) {
                $disk->delete($generalSetting->logo_path);
            }
        }

        foreach ($region->categories()->get(['icon_path']) as $category) {
            if ($category->icon_path) {
                $disk->delete($category->icon_path);
            }
        }

        foreach ($region->stores()->get(['logo_path', 'og_image']) as $store) {
            foreach ([$store->logo_path, $store->og_image] as $path) {
                if ($path) {
                    $disk->delete($path);
                }
            }
        }

        foreach ($region->blogs()->get(['featured_image', 'og_image']) as $blog) {
            foreach ([$blog->featured_image, $blog->og_image] as $path) {
                if ($path) {
                    $disk->delete($path);
                }
            }
        }

        foreach ($region->staticPages()->get(['og_image']) as $page) {
            if ($page->og_image) {
                $disk->delete($page->og_image);
            }
        }

        foreach ($region->pageSettings()->get(['og_image']) as $pageSetting) {
            if ($pageSetting->og_image) {
                $disk->delete($pageSetting->og_image);
            }
        }
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

        // A blank `code` is only meaningful for the CURRENT default (item
        // 18: it's what makes that one region root-mounted at "/"). If the
        // outgoing default was left blank and this switch went through
        // unchecked, it would become a non-default region with no URL
        // prefix at all — unreachable, and not root-mounted either since
        // it's no longer the default. Block the switch until it has one.
        $currentDefault = Region::where('is_default', true)->first();
        if ($currentDefault && ! $currentDefault->code) {
            return back()->with('error', "Give {$currentDefault->name} a URL prefix (slug) before making another region the default — it would otherwise be left with no URL at all.");
        }

        Region::where('is_default', true)->update(['is_default' => false]);
        $region->update(['is_default' => true]);

        // The root-mounted route group (routes/web.php) is only registered
        // when the (now-previous) default's code was blank — that's no
        // longer true, and/or the new default may itself be blank, so the
        // route set at "/" needs to be rebuilt from the new DB state.
        Artisan::call('route:clear');

        return back()->with('status', "{$region->name} is now the default region.");
    }

    /**
     * Deep-copies $region (every store, blog, page, menu, script, etc. and
     * their files — see RegionCopier) into a brand-new region under the
     * given slug/name. Wrapped in a transaction so a failure partway
     * through (e.g. a PHP execution-time limit on a very large region)
     * leaves no half-copied region behind; RegionCopier itself removes any
     * files it had already duplicated before the exception propagates here.
     */
    public function copy(Request $request, Region $region): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'min:2', 'max:4', 'alpha', Rule::unique('regions', 'code')],
            'name' => ['required', 'string', 'max:255', Rule::unique('regions', 'name')],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in(array_keys(RegionCopier::MODULES))],
        ]);

        // A large region (many stores + media files) can take a while to
        // duplicate synchronously — this host has no queue worker to defer
        // it to, so give it more room than the default execution-time
        // limit rather than let it get killed mid-copy. Silently a no-op
        // where the host disables set_time_limit() entirely.
        @set_time_limit(300);

        $outcome = (new RegionCopier)->copy($region, strtolower($data['code']), $data['name'], $data['modules'] ?? []);

        $request->session()->flash('copy_result', [
            'source_name' => $region->name,
            'new_region_id' => $outcome['region']->id,
            'new_name' => $outcome['region']->name,
            'modules' => $outcome['results'],
        ]);

        return redirect()->route('admin.regions.index');
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
                // Blank is only valid for the CURRENT default region — that's
                // what root-mounts it at "/" instead of "/{code}" (item 18).
                // Every other region (including a region being newly
                // created, which is never default — see store() above)
                // still needs a real prefix to be reachable at all.
                $region && $region->is_default ? 'nullable' : 'required',
                'string', 'min:2', 'max:4', 'alpha',
                Rule::unique('regions', 'code')->ignore($region),
            ],
            'name' => ['required', 'string', 'max:255'],
            'favicon' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:512', 'dimensions:width=32,height=32'],
            'flag' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:512', 'dimensions:width=24,height=16'],
            'head_start_script' => ['nullable', 'string'],
            'head_end_script' => ['nullable', 'string'],
            'body_start_script' => ['nullable', 'string'],
            'body_end_script' => ['nullable', 'string'],
            'canonical_base_url' => ['nullable', 'url:https,http', 'max:2048'],
            'robots_extra_allow' => ['nullable', 'string', 'max:5000', $this->eachLineIsAbsolutePath()],
            'robots_extra_disallow' => ['nullable', 'string', 'max:5000', $this->eachLineIsAbsolutePath()],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['code'] = $data['code'] ? strtolower($data['code']) : null;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }

    /**
     * The robots-extra-allow/disallow fields are a one-path-per-line
     * textarea (see Region::robotsExtraAllowLines()), fed verbatim into an
     * "Allow: {value}" / "Disallow: {value}" robots.txt line by
     * SitemapController::buildRobotsResponse() — per the robots.txt spec
     * that value must be a site-relative path starting with "/"; a full
     * absolute URL (scheme + host) there is invalid and most crawlers
     * won't honor it. Every non-blank line must be such a path.
     */
    private function eachLineIsAbsolutePath(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            foreach (preg_split('/\r\n|\r|\n/', (string) $value) as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                if (! str_starts_with($line, '/') || str_contains($line, ' ')) {
                    $fail("Each line must be a site path starting with \"/\" (e.g. /go/) — \"{$line}\" isn't.");

                    return;
                }
            }
        };
    }
}
