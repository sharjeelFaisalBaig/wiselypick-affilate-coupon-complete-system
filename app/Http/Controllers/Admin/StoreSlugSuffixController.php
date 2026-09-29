<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\StoreSlugSuffix;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoreSlugSuffixController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $storeSlugSuffixes = StoreSlugSuffix::where('region_id', $region->id)->withCount('stores')->orderBy('value')->get();

        return view('admin.store-slug-suffixes.index', compact('storeSlugSuffixes'));
    }

    public function create(): View
    {
        return view('admin.store-slug-suffixes.form', ['storeSlugSuffix' => new StoreSlugSuffix()]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;

        StoreSlugSuffix::create($data);

        return redirect()->route('admin.store-slug-suffixes.index')->with('status', 'Slug suffix created.');
    }

    public function edit(Request $request, StoreSlugSuffix $storeSlugSuffix): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $storeSlugSuffix->region_id);

        return view('admin.store-slug-suffixes.form', compact('storeSlugSuffix'));
    }

    public function update(Request $request, StoreSlugSuffix $storeSlugSuffix): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $storeSlugSuffix->region_id);

        $storeSlugSuffix->update($this->validated($request, $storeSlugSuffix));

        return redirect()->route('admin.store-slug-suffixes.index')->with('status', 'Slug suffix updated.');
    }

    public function destroy(Request $request, StoreSlugSuffix $storeSlugSuffix): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $storeSlugSuffix->region_id);

        if ($storeSlugSuffix->stores()->exists()) {
            return back()->with('error', 'This slug suffix is assigned to one or more stores and cannot be deleted.');
        }

        $storeSlugSuffix->delete();

        return redirect()->route('admin.store-slug-suffixes.index')->with('status', 'Slug suffix deleted.');
    }

    private function validated(Request $request, ?StoreSlugSuffix $storeSlugSuffix = null): array
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'value' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9-]+(\/[a-z0-9-]+)*$/',
                Rule::unique('store_slug_suffixes', 'value')->where('region_id', $region->id)->ignore($storeSlugSuffix),
            ],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
