<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Manages "store" categories — the taxonomy used to organize/browse Stores
 * (see /stores and /category/{slug}). Promotions have no category of their
 * own; they inherit their category from the assigned store (SRS §9), so
 * this is the only category taxonomy in the system. Flat/single-level —
 * no parent-child concept anywhere.
 */
class CategoryController extends Controller
{
    use GuardsRegionOwnership;

    private const TYPE = 'store';

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $query = Category::where('region_id', $region->id)->where('type', self::TYPE)
            ->withCount(['stores as active_stores_count' => fn ($q) => $q->where('is_active', true)]);

        if ($request->filled('q')) {
            $search = $request->string('q')->value();
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status')->value() === 'active');
        }

        $categories = $query->orderBy('sort_order')->paginate(20)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function suggest(Request $request): \Illuminate\Http\JsonResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');
        $q = $request->string('q')->value();

        $categories = Category::where('region_id', $region->id)->where('type', self::TYPE)
            ->where('name', 'like', "%{$q}%")
            ->when($request->filled('status'), fn ($sq) => $sq->where('is_active', $request->string('status')->value() === 'active'))
            ->orderBy('name')->limit(8)->get(['id', 'name']);

        return response()->json($categories->map(fn ($category) => [
            'label' => $category->name,
            'url' => route('admin.categories.edit', $category),
        ]));
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new Category(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;
        $data['type'] = self::TYPE;
        $data['slug'] = $data['slug'] ?: $this->uniqueSlug($data['name'], $region->id);

        if ($request->hasFile('icon_image')) {
            $data['icon_path'] = $request->file('icon_image')->store('categories/icons', 'public');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Request $request, Category $category): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $category->region_id);

        return view('admin.categories.form', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $category->region_id);

        $data = $this->validated($request, $category);

        if ($data['slug']) {
            // Admin explicitly typed a slug — already validated unique above.
        } elseif ($category->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], $category->region_id, $category->id);
        } else {
            unset($data['slug']);
        }

        if ($request->hasFile('icon_image')) {
            if ($category->icon_path) {
                Storage::disk('public')->delete($category->icon_path);
            }
            $data['icon_path'] = $request->file('icon_image')->store('categories/icons', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $category->region_id);

        if ($category->stores()->exists()) {
            return back()->with('error', 'This category still has stores assigned to it and cannot be deleted. Reassign them first.');
        }

        if ($category->icon_path) {
            Storage::disk('public')->delete($category->icon_path);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }

    public function reorder(Request $request): Response
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');
        $ids = $request->validate(['ids' => ['required', 'array']])['ids'];

        foreach ($ids as $index => $id) {
            Category::where('id', $id)->where('region_id', $region->id)->where('type', self::TYPE)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->noContent();
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                Rule::unique('categories', 'slug')->where('region_id', $region->id)->where('type', self::TYPE)->ignore($category),
            ],
            'icon' => ['nullable', 'string', 'max:255'],
            'icon_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:512'],
            'description' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function uniqueSlug(string $name, int $regionId, ?int $exceptId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (
            Category::where('region_id', $regionId)->where('type', self::TYPE)->where('slug', $slug)
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }
}
