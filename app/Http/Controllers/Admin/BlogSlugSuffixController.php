<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\BlogSlugSuffix;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogSlugSuffixController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $blogSlugSuffixes = BlogSlugSuffix::where('region_id', $region->id)->withCount('blogs')->orderBy('value')->get();

        return view('admin.blog-slug-suffixes.index', compact('blogSlugSuffixes'));
    }

    public function create(): View
    {
        return view('admin.blog-slug-suffixes.form', ['blogSlugSuffix' => new BlogSlugSuffix()]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;

        BlogSlugSuffix::create($data);

        return redirect()->route('admin.blog-slug-suffixes.index')->with('status', 'Slug suffix created.');
    }

    public function edit(Request $request, BlogSlugSuffix $blogSlugSuffix): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $blogSlugSuffix->region_id);

        return view('admin.blog-slug-suffixes.form', compact('blogSlugSuffix'));
    }

    public function update(Request $request, BlogSlugSuffix $blogSlugSuffix): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $blogSlugSuffix->region_id);

        $blogSlugSuffix->update($this->validated($request, $blogSlugSuffix));

        return redirect()->route('admin.blog-slug-suffixes.index')->with('status', 'Slug suffix updated.');
    }

    public function destroy(Request $request, BlogSlugSuffix $blogSlugSuffix): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $blogSlugSuffix->region_id);

        if ($blogSlugSuffix->blogs()->exists()) {
            return back()->with('error', 'This slug suffix is assigned to one or more blog posts and cannot be deleted.');
        }

        $blogSlugSuffix->delete();

        return redirect()->route('admin.blog-slug-suffixes.index')->with('status', 'Slug suffix deleted.');
    }

    private function validated(Request $request, ?BlogSlugSuffix $blogSlugSuffix = null): array
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'value' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9-]+(\/[a-z0-9-]+)*$/',
                Rule::unique('blog_slug_suffixes', 'value')->where('region_id', $region->id)->ignore($blogSlugSuffix),
            ],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
