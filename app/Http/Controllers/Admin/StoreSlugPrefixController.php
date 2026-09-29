<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\StoreSlugPrefix;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoreSlugPrefixController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $storeSlugPrefixes = StoreSlugPrefix::where('region_id', $region->id)->withCount('stores')->orderBy('value')->get();

        return view('admin.store-slug-prefixes.index', compact('storeSlugPrefixes'));
    }

    public function create(): View
    {
        return view('admin.store-slug-prefixes.form', ['storeSlugPrefix' => new StoreSlugPrefix()]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;

        StoreSlugPrefix::create($data);

        return redirect()->route('admin.store-slug-prefixes.index')->with('status', 'Slug prefix created.');
    }

    public function edit(Request $request, StoreSlugPrefix $storeSlugPrefix): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $storeSlugPrefix->region_id);

        return view('admin.store-slug-prefixes.form', compact('storeSlugPrefix'));
    }

    public function update(Request $request, StoreSlugPrefix $storeSlugPrefix): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $storeSlugPrefix->region_id);

        $storeSlugPrefix->update($this->validated($request, $storeSlugPrefix));

        return redirect()->route('admin.store-slug-prefixes.index')->with('status', 'Slug prefix updated.');
    }

    public function destroy(Request $request, StoreSlugPrefix $storeSlugPrefix): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $storeSlugPrefix->region_id);

        if ($storeSlugPrefix->stores()->exists()) {
            return back()->with('error', 'This slug prefix is assigned to one or more stores and cannot be deleted.');
        }

        $storeSlugPrefix->delete();

        return redirect()->route('admin.store-slug-prefixes.index')->with('status', 'Slug prefix deleted.');
    }

    private function validated(Request $request, ?StoreSlugPrefix $storeSlugPrefix = null): array
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'value' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9-]+(\/[a-z0-9-]+)*$/',
                $this->notReservedPrefix(),
                Rule::unique('store_slug_prefixes', 'value')->where('region_id', $region->id)->ignore($storeSlugPrefix),
            ],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /** See Admin\StoreController::notReservedPrefix() for the rationale. */
    private function notReservedPrefix(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            $first = strtolower(explode('/', trim($value, '/'))[0]);
            if (in_array($first, ['category', 'suggest', 'go', 'contact'], true)) {
                $fail("The prefix can't start with \"{$first}\" — that path is already used elsewhere on the site.");
            }
        };
    }
}
