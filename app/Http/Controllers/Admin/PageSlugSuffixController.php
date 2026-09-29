<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\PageSlugSuffix;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageSlugSuffixController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $pageSlugSuffixes = PageSlugSuffix::where('region_id', $region->id)->withCount('staticPages')->orderBy('value')->get();

        return view('admin.page-slug-suffixes.index', compact('pageSlugSuffixes'));
    }

    public function create(): View
    {
        return view('admin.page-slug-suffixes.form', ['pageSlugSuffix' => new PageSlugSuffix()]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;

        PageSlugSuffix::create($data);

        return redirect()->route('admin.page-slug-suffixes.index')->with('status', 'Slug suffix created.');
    }

    public function edit(Request $request, PageSlugSuffix $pageSlugSuffix): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $pageSlugSuffix->region_id);

        return view('admin.page-slug-suffixes.form', compact('pageSlugSuffix'));
    }

    public function update(Request $request, PageSlugSuffix $pageSlugSuffix): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $pageSlugSuffix->region_id);

        $pageSlugSuffix->update($this->validated($request, $pageSlugSuffix));

        return redirect()->route('admin.page-slug-suffixes.index')->with('status', 'Slug suffix updated.');
    }

    public function destroy(Request $request, PageSlugSuffix $pageSlugSuffix): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $pageSlugSuffix->region_id);

        if ($pageSlugSuffix->staticPages()->exists()) {
            return back()->with('error', 'This slug suffix is assigned to one or more pages and cannot be deleted.');
        }

        $pageSlugSuffix->delete();

        return redirect()->route('admin.page-slug-suffixes.index')->with('status', 'Slug suffix deleted.');
    }

    private function validated(Request $request, ?PageSlugSuffix $pageSlugSuffix = null): array
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'value' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9-]+(\/[a-z0-9-]+)*$/',
                Rule::unique('page_slug_suffixes', 'value')->where('region_id', $region->id)->ignore($pageSlugSuffix),
            ],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
