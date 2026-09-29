<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Admin\Concerns\ValidatesAbsoluteUrlConflicts;
use App\Http\Controllers\Controller;
use App\Models\PageSlugPrefix;
use App\Models\PageSlugSuffix;
use App\Models\Region;
use App\Models\StaticPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaticPageController extends Controller
{
    use GuardsRegionOwnership;
    use ValidatesAbsoluteUrlConflicts;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $pages = StaticPage::where('region_id', $region->id)->orderBy('title')->get();

        return view('admin.static-pages.index', compact('pages'));
    }

    public function create(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        return view('admin.static-pages.form', [
            'page' => new StaticPage(),
            'pageSlugPrefixes' => PageSlugPrefix::where('region_id', $region->id)->where('is_active', true)->orderBy('value')->get(),
            'pageSlugSuffixes' => PageSlugSuffix::where('region_id', $region->id)->where('is_active', true)->orderBy('value')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request, $region);
        $data['region_id'] = $region->id;
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        $this->guardAgainstUrlConflict($this->prospectiveUrl($data, $region), 'static_page', null);

        StaticPage::create($data);

        return redirect()->route('admin.static-pages.index')->with('status', 'Page created.');
    }

    public function edit(Request $request, StaticPage $staticPage): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $staticPage->region_id);

        return view('admin.static-pages.form', [
            'page' => $staticPage,
            'pageSlugPrefixes' => PageSlugPrefix::where('region_id', $staticPage->region_id)->where('is_active', true)->orderBy('value')->get(),
            'pageSlugSuffixes' => PageSlugSuffix::where('region_id', $staticPage->region_id)->where('is_active', true)->orderBy('value')->get(),
        ]);
    }

    public function update(Request $request, StaticPage $staticPage): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $staticPage->region_id);

        $data = $this->validated($request, $staticPage->region, $staticPage);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        $this->guardAgainstUrlConflict(
            $this->prospectiveUrl($data, $staticPage->region),
            'static_page', $staticPage->id
        );

        $staticPage->update($data);

        return redirect()->route('admin.static-pages.index')->with('status', 'Page updated.');
    }

    public function destroy(Request $request, StaticPage $staticPage): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $staticPage->region_id);

        $staticPage->delete();

        return redirect()->route('admin.static-pages.index')->with('status', 'Page deleted.');
    }

    private function validated(Request $request, Region $region, ?StaticPage $page = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                Rule::unique('static_pages', 'slug')->where('region_id', $region->id)->ignore($page),
            ],
            'page_slug_prefix_id' => ['nullable', Rule::exists('page_slug_prefixes', 'id')->where('region_id', $region->id)],
            'page_slug_suffix_id' => ['nullable', Rule::exists('page_slug_suffixes', 'id')->where('region_id', $region->id)],
            'content' => ['required', 'string'],
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

        $data['is_active'] = $request->boolean('is_active');
        $data['robots_index'] = $request->boolean('robots_index');
        $data['robots_follow'] = $request->boolean('robots_follow');

        return $data;
    }

    /** See Admin\StoreController::prospectiveUrl() for the rationale. */
    private function prospectiveUrl(array $data, Region $region): string
    {
        $prospective = new StaticPage([
            'slug' => $data['slug'],
            'page_slug_prefix_id' => $data['page_slug_prefix_id'] ?? null,
            'page_slug_suffix_id' => $data['page_slug_suffix_id'] ?? null,
        ]);

        return $prospective->urlFor($region);
    }
}
