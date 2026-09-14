<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\ScriptInjection;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScriptInjectionController extends Controller
{
    use GuardsRegionOwnership;

    public const PAGE_TYPES = [
        'home' => 'Homepage',
        'stores_directory' => 'Stores Directory',
        'store_detail' => 'Store Detail',
        'category' => 'Category',
        'coupons' => 'Promo Codes',
        'blog_listing' => 'Blog Listing',
        'blog_detail' => 'Blog Detail',
        'static_page' => 'Static Pages',
    ];

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $injections = ScriptInjection::where('region_id', $region->id)
            ->withCount('stores')
            ->with('pageTargets')
            ->latest()
            ->paginate(20);

        return view('admin.script-injections.index', compact('injections'));
    }

    public function create(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');
        $stores = Store::where('region_id', $region->id)->orderBy('name')->get();

        return view('admin.script-injections.form', [
            'injection' => new ScriptInjection(),
            'stores' => $stores,
            'pageTypes' => self::PAGE_TYPES,
            'selectedPages' => [],
            'selectedStores' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;

        $injection = ScriptInjection::create($data);
        $this->syncTargets($request, $injection);

        return redirect()->route('admin.script-injections.index')->with('status', 'Script injection created.');
    }

    public function edit(Request $request, ScriptInjection $scriptInjection): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $scriptInjection->region_id);

        $stores = Store::where('region_id', $scriptInjection->region_id)->orderBy('name')->get();

        return view('admin.script-injections.form', [
            'injection' => $scriptInjection,
            'stores' => $stores,
            'pageTypes' => self::PAGE_TYPES,
            'selectedPages' => $scriptInjection->pageTargets->pluck('page_type')->all(),
            'selectedStores' => $scriptInjection->stores->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, ScriptInjection $scriptInjection): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $scriptInjection->region_id);

        $scriptInjection->update($this->validated($request));
        $this->syncTargets($request, $scriptInjection);

        return redirect()->route('admin.script-injections.index')->with('status', 'Script injection updated.');
    }

    public function destroy(Request $request, ScriptInjection $scriptInjection): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $scriptInjection->region_id);

        $scriptInjection->delete();

        return redirect()->route('admin.script-injections.index')->with('status', 'Script injection deleted.');
    }

    private function syncTargets(Request $request, ScriptInjection $injection): void
    {
        $injection->pageTargets()->delete();
        $injection->stores()->detach();

        if ($injection->target_type === 'specific_pages') {
            foreach ($request->input('page_types', []) as $pageType) {
                if (array_key_exists($pageType, self::PAGE_TYPES)) {
                    $injection->pageTargets()->create(['page_type' => $pageType]);
                }
            }
        }

        if ($injection->target_type === 'specific_stores') {
            $storeIds = collect($request->input('store_ids', []))
                ->filter(fn ($id) => Store::where('id', $id)->where('region_id', $injection->region_id)->exists());

            $injection->stores()->sync($storeIds);
        }
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'placement' => ['required', 'in:head_start,head_end,body_start,body_end'],
            'script_content' => ['required', 'string'],
            'target_type' => ['required', 'in:all_pages,specific_pages,specific_stores'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
