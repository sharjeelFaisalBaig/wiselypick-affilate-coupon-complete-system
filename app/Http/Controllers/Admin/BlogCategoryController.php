<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $blogCategories = BlogCategory::where('region_id', $region->id)->withCount('blogs')->orderBy('sort_order')->get();

        return view('admin.blog-categories.index', compact('blogCategories'));
    }

    public function create(): View
    {
        return view('admin.blog-categories.form', ['blogCategory' => new BlogCategory()]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;
        $data['slug'] = Str::slug($data['name']);

        BlogCategory::create($data);

        return redirect()->route('admin.blog-categories.index')->with('status', 'Blog category created.');
    }

    public function edit(Request $request, BlogCategory $blogCategory): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $blogCategory->region_id);

        return view('admin.blog-categories.form', compact('blogCategory'));
    }

    public function update(Request $request, BlogCategory $blogCategory): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $blogCategory->region_id);

        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']);

        $blogCategory->update($data);

        return redirect()->route('admin.blog-categories.index')->with('status', 'Blog category updated.');
    }

    public function destroy(Request $request, BlogCategory $blogCategory): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $blogCategory->region_id);

        $blogCategory->delete();

        return redirect()->route('admin.blog-categories.index')->with('status', 'Blog category deleted.');
    }

    public function reorder(Request $request): Response
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');
        $ids = $request->validate(['ids' => ['required', 'array']])['ids'];

        foreach ($ids as $index => $id) {
            BlogCategory::where('id', $id)->where('region_id', $region->id)->update(['sort_order' => $index + 1]);
        }

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
