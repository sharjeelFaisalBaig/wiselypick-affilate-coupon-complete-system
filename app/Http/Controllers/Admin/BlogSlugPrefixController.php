<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\BlogSlugPrefix;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogSlugPrefixController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $blogSlugPrefixes = BlogSlugPrefix::where('region_id', $region->id)->withCount('blogs')->orderBy('value')->get();

        return view('admin.blog-slug-prefixes.index', compact('blogSlugPrefixes'));
    }

    public function create(): View
    {
        return view('admin.blog-slug-prefixes.form', ['blogSlugPrefix' => new BlogSlugPrefix()]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;

        BlogSlugPrefix::create($data);

        return redirect()->route('admin.blog-slug-prefixes.index')->with('status', 'Slug prefix created.');
    }

    public function edit(Request $request, BlogSlugPrefix $blogSlugPrefix): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $blogSlugPrefix->region_id);

        return view('admin.blog-slug-prefixes.form', compact('blogSlugPrefix'));
    }

    public function update(Request $request, BlogSlugPrefix $blogSlugPrefix): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $blogSlugPrefix->region_id);

        $blogSlugPrefix->update($this->validated($request, $blogSlugPrefix));

        return redirect()->route('admin.blog-slug-prefixes.index')->with('status', 'Slug prefix updated.');
    }

    public function destroy(Request $request, BlogSlugPrefix $blogSlugPrefix): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $blogSlugPrefix->region_id);

        if ($blogSlugPrefix->blogs()->exists()) {
            return back()->with('error', 'This slug prefix is assigned to one or more blog posts and cannot be deleted.');
        }

        $blogSlugPrefix->delete();

        return redirect()->route('admin.blog-slug-prefixes.index')->with('status', 'Slug prefix deleted.');
    }

    private function validated(Request $request, ?BlogSlugPrefix $blogSlugPrefix = null): array
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'value' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9-]+(\/[a-z0-9-]+)*$/',
                $this->notReservedPrefix(),
                Rule::unique('blog_slug_prefixes', 'value')->where('region_id', $region->id)->ignore($blogSlugPrefix),
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
