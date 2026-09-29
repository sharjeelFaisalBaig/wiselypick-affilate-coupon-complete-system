<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\PageSlugPrefix;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageSlugPrefixController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $pageSlugPrefixes = PageSlugPrefix::where('region_id', $region->id)->withCount('staticPages')->orderBy('value')->get();

        return view('admin.page-slug-prefixes.index', compact('pageSlugPrefixes'));
    }

    public function create(): View
    {
        return view('admin.page-slug-prefixes.form', ['pageSlugPrefix' => new PageSlugPrefix()]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;

        PageSlugPrefix::create($data);

        return redirect()->route('admin.page-slug-prefixes.index')->with('status', 'Slug prefix created.');
    }

    public function edit(Request $request, PageSlugPrefix $pageSlugPrefix): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $pageSlugPrefix->region_id);

        return view('admin.page-slug-prefixes.form', compact('pageSlugPrefix'));
    }

    public function update(Request $request, PageSlugPrefix $pageSlugPrefix): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $pageSlugPrefix->region_id);

        $pageSlugPrefix->update($this->validated($request, $pageSlugPrefix));

        return redirect()->route('admin.page-slug-prefixes.index')->with('status', 'Slug prefix updated.');
    }

    public function destroy(Request $request, PageSlugPrefix $pageSlugPrefix): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $pageSlugPrefix->region_id);

        if ($pageSlugPrefix->staticPages()->exists()) {
            return back()->with('error', 'This slug prefix is assigned to one or more pages and cannot be deleted.');
        }

        $pageSlugPrefix->delete();

        return redirect()->route('admin.page-slug-prefixes.index')->with('status', 'Slug prefix deleted.');
    }

    private function validated(Request $request, ?PageSlugPrefix $pageSlugPrefix = null): array
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'value' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9-]+(\/[a-z0-9-]+)*$/',
                $this->notReservedPrefix(),
                Rule::unique('page_slug_prefixes', 'value')->where('region_id', $region->id)->ignore($pageSlugPrefix),
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
