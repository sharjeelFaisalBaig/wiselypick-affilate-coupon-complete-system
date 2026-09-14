<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\StoreSuffix;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoreSuffixController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $storeSuffixes = StoreSuffix::where('region_id', $region->id)->withCount('stores')->orderBy('name')->get();

        return view('admin.store-suffixes.index', compact('storeSuffixes'));
    }

    public function create(): View
    {
        return view('admin.store-suffixes.form', ['storeSuffix' => new StoreSuffix()]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;

        StoreSuffix::create($data);

        return redirect()->route('admin.store-suffixes.index')->with('status', 'Store suffix created.');
    }

    public function edit(Request $request, StoreSuffix $storeSuffix): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $storeSuffix->region_id);

        return view('admin.store-suffixes.form', compact('storeSuffix'));
    }

    public function update(Request $request, StoreSuffix $storeSuffix): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $storeSuffix->region_id);

        $storeSuffix->update($this->validated($request, $storeSuffix));

        return redirect()->route('admin.store-suffixes.index')->with('status', 'Store suffix updated.');
    }

    public function destroy(Request $request, StoreSuffix $storeSuffix): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $storeSuffix->region_id);

        if ($storeSuffix->stores()->exists()) {
            return back()->with('error', 'This suffix is assigned to one or more stores and cannot be deleted.');
        }

        $storeSuffix->delete();

        return redirect()->route('admin.store-suffixes.index')->with('status', 'Store suffix deleted.');
    }

    private function validated(Request $request, ?StoreSuffix $storeSuffix = null): array
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('store_suffixes', 'name')->where('region_id', $region->id)->ignore($storeSuffix)],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
