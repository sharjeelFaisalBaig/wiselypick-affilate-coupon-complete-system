<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Admin\Concerns\ValidatesAbsoluteUrlConflicts;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Region;
use App\Models\Store;
use App\Models\StoreSlugPrefix;
use App\Models\StoreSlugSuffix;
use App\Models\StoreSuffix;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoreController extends Controller
{
    use GuardsRegionOwnership;
    use ValidatesAbsoluteUrlConflicts;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $query = Store::where('region_id', $region->id)->with(['category', 'storeSlugPrefix', 'storeSlugSuffix']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status')->value() === 'active');
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->value();
            $query->where('name', 'like', "%{$search}%");
        }

        $stores = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Category::where('region_id', $region->id)->where('type', 'store')->orderBy('name')->get(['id', 'name']);

        if ($request->header('X-Ajax-Filter')) {
            return view('admin.stores._results', compact('stores'));
        }

        return view('admin.stores.index', compact('stores', 'categories'));
    }

    public function suggest(Request $request): \Illuminate\Http\JsonResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');
        $q = $request->string('q')->value();

        $query = Store::where('region_id', $region->id)->where('name', 'like', "%{$q}%");

        // The classification screen's Featured/Popular/Pending tabs each pass
        // their own `scope` so a suggestion never leaks in from a different
        // tab's dataset — matching the row-filter's existing per-tab scoping.
        match ($request->string('scope')->value()) {
            'featured' => $query->where('is_featured', true),
            'popular' => $query->where('is_popular', true),
            // Matches classification()'s pending query — Store State alone
            // decides pending/active (see the comment there for why).
            'pending' => $query->where('is_active', false),
            default => null,
        };

        // The main store list's own category/status filters (sibling fields
        // in the same form, not a static `scope`) narrow suggestions the
        // same way — selecting a category/status doesn't re-filter the list
        // live, but the search box's suggestions still respect it.
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status')->value() === 'active');
        }

        $stores = $query->orderBy('name')->limit(8)->get(['id', 'name']);

        return response()->json($stores->map(fn ($store) => [
            'label' => $store->name,
            'url' => route('admin.stores.edit', $store),
        ]));
    }

    public function create(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');
        $categories = Category::where('region_id', $region->id)->where('type', 'store')->orderBy('name')->get();
        $storeSuffixes = StoreSuffix::where('region_id', $region->id)->where('is_active', true)->orderBy('name')->get();
        $storeSlugPrefixes = StoreSlugPrefix::where('region_id', $region->id)->where('is_active', true)->orderBy('value')->get();
        $storeSlugSuffixes = StoreSlugSuffix::where('region_id', $region->id)->where('is_active', true)->orderBy('value')->get();

        return view('admin.stores.form', [
            'store' => new Store(),
            'categories' => $categories,
            'storeSuffixes' => $storeSuffixes,
            'storeSlugPrefixes' => $storeSlugPrefixes,
            'storeSlugSuffixes' => $storeSlugSuffixes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;
        $data['slug'] = $data['slug'] ?: $this->uniqueSlug($data['name'], $region->id);

        // SEO fields default to sensible, editable copy rather than being
        // left blank — an admin should never see an empty settings form for
        // content that's actually live (it just falls back silently
        // otherwise, per the same pattern as PageSetting/StaticPage).
        $data['meta_title'] = $data['meta_title'] ?: "{$data['name']} Promo Codes, Coupons & Deals";
        $data['meta_description'] = $data['meta_description'] ?: "Save with the latest verified {$data['name']} coupon codes and deals in {$region->name}.";
        $data['og_title'] = $data['og_title'] ?: $data['meta_title'];
        $data['og_description'] = $data['og_description'] ?: $data['meta_description'];

        $this->guardAgainstUrlConflict($this->prospectiveUrl($data, $region), 'store', null);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('stores', 'public');
        }

        Store::create($data);

        return redirect()->route('admin.stores.index')->with('status', 'Store created.');
    }

    public function edit(Request $request, Store $store): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $store->region_id);

        $categories = Category::where('region_id', $store->region_id)->where('type', 'store')->orderBy('name')->get();
        $storeSuffixes = StoreSuffix::where('region_id', $store->region_id)->where('is_active', true)->orderBy('name')->get();
        $storeSlugPrefixes = StoreSlugPrefix::where('region_id', $store->region_id)->where('is_active', true)->orderBy('value')->get();
        $storeSlugSuffixes = StoreSlugSuffix::where('region_id', $store->region_id)->where('is_active', true)->orderBy('value')->get();

        return view('admin.stores.form', [
            'store' => $store,
            'categories' => $categories,
            'storeSuffixes' => $storeSuffixes,
            'storeSlugPrefixes' => $storeSlugPrefixes,
            'storeSlugSuffixes' => $storeSlugSuffixes,
        ]);
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $store->region_id);

        $data = $this->validated($request, $store);
        if ($data['slug']) {
            // Admin explicitly typed a slug — already validated unique below.
        } elseif ($data['name'] !== $store->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $store->region_id, $store->id);
        } else {
            unset($data['slug']);
        }

        $this->guardAgainstUrlConflict(
            $this->prospectiveUrl($data + ['slug' => $data['slug'] ?? $store->slug], $store->region),
            'store', $store->id
        );

        if ($request->hasFile('logo')) {
            if ($store->logo_path) {
                Storage::disk('public')->delete($store->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('stores', 'public');
        }

        $store->update($data);

        return redirect()->route('admin.stores.index')->with('status', 'Store updated.');
    }

    public function destroy(Request $request, Store $store): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $store->region_id);

        if ($store->offers()->where('is_active', true)->exists()) {
            return back()->with('error', 'This store has active coupons or deals and cannot be deleted. Deactivate or remove them first.');
        }

        if ($store->logo_path) {
            Storage::disk('public')->delete($store->logo_path);
        }

        $store->delete();

        return redirect()->route('admin.stores.index')->with('status', 'Store deleted.');
    }

    public function toggleActive(Request $request, Store $store): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $store->region_id);

        $store->update(['is_active' => ! $store->is_active]);

        return back()->with('status', $store->is_active ? 'Store activated.' : 'Store deactivated.');
    }

    public function classification(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $featured = Store::where('region_id', $region->id)->where('is_featured', true)->with('category')->orderBy('featured_order')->get();
        $popular = Store::where('region_id', $region->id)->where('is_popular', true)->with('category')->orderBy('popular_order')->get();
        // Store State alone decides pending/active — a store with Store
        // State = Active belongs in the Active list everywhere in the admin
        // panel, this tab included, regardless of the legacy is_pending
        // column (no form field sets it anymore; see Store::scopeVisible()).
        $pending = Store::where('region_id', $region->id)
            ->where('is_active', false)
            ->with('category')->orderBy('pending_order')->get();

        // Featured Deals: every featured offer across every store in the
        // region together, in its own cross-store order — distinct from
        // offers.sort_order, which only governs a single store's own
        // detail-page ordering.
        $featuredOffers = Offer::with('store')
            ->whereHas('store', fn ($q) => $q->where('region_id', $region->id))
            ->where('is_featured', true)
            ->orderBy('featured_order')
            ->get();

        return view('admin.stores.classification', compact('featured', 'popular', 'pending', 'featuredOffers'));
    }

    public function reorderFeatured(Request $request): Response
    {
        return $this->reorder($request, 'featured_order');
    }

    public function reorderPopular(Request $request): Response
    {
        return $this->reorder($request, 'popular_order');
    }

    public function reorderPending(Request $request): Response
    {
        return $this->reorder($request, 'pending_order');
    }

    public function reorderFeaturedOffers(Request $request): Response
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');
        $ids = $request->validate(['ids' => ['required', 'array']])['ids'];

        foreach ($ids as $index => $id) {
            Offer::where('id', $id)
                ->whereHas('store', fn ($q) => $q->where('region_id', $region->id))
                ->update(['featured_order' => $index + 1]);
        }

        return response()->noContent();
    }

    private function reorder(Request $request, string $column): Response
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');
        $ids = $request->validate(['ids' => ['required', 'array']])['ids'];

        foreach ($ids as $index => $id) {
            Store::where('id', $id)->where('region_id', $region->id)->update([$column => $index + 1]);
        }

        return response()->noContent();
    }

    private function validated(Request $request, ?Store $store = null): array
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('region_id', $region->id)->where('type', 'store'),
            ],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('stores', 'name')->where('region_id', $region->id)->ignore($store),
            ],
            'slug' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                Rule::unique('stores', 'slug')->where('region_id', $region->id)->ignore($store),
            ],
            'store_slug_prefix_id' => [
                'nullable',
                Rule::exists('store_slug_prefixes', 'id')->where('region_id', $region->id),
            ],
            'store_slug_suffix_id' => [
                'nullable',
                Rule::exists('store_slug_suffixes', 'id')->where('region_id', $region->id),
            ],
            'about' => ['nullable', 'string'],
            'store_suffix_id' => [
                'nullable',
                Rule::exists('store_suffixes', 'id')->where('region_id', $region->id),
            ],
            'affiliate_url' => ['required', 'url:https,http', 'max:2048'],
            'start_date' => ['nullable', 'date', 'before_or_equal:expiry_date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'star_rating' => ['required', 'numeric', 'min:0', 'max:5'],
            'reviews_count' => ['nullable', 'integer', 'min:0'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:1024', 'dimensions:width=200,height=200'],
            'status' => ['required', 'in:pending,active'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:255'],
            'head_start_script' => ['nullable', 'string'],
            'head_end_script' => ['nullable', 'string'],
            'body_start_script' => ['nullable', 'string'],
            'body_end_script' => ['nullable', 'string'],
        ]);

        $data['reviews_count'] = $data['reviews_count'] ?? 0;
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_popular'] = $request->boolean('is_popular');
        $data['starts_from_root'] = $request->boolean('starts_from_root');
        // The two are mutually exclusive by construction (the prefix select
        // is disabled client-side while this is checked — see
        // stores/form.blade.php) — nulled server-side too so a stray
        // prefix pick can never survive with starts_from_root regardless of
        // what the client actually submitted.
        if ($data['starts_from_root']) {
            $data['store_slug_prefix_id'] = null;
        }
        // No longer a form field — Pending is driven solely by Store State
        // below now. Deliberately not touched here (vs. reading a checkbox
        // that no longer exists and always resetting it to false), so any
        // row already carrying is_pending=true from before this change
        // keeps it instead of silently losing it on its next save.
        $data['is_active'] = $data['status'] === 'active';
        unset($data['status']);
        $data['robots_index'] = $request->boolean('robots_index');
        $data['robots_follow'] = $request->boolean('robots_follow');

        return $data;
    }

    /**
     * The absolute URL $data would produce if saved, computed via an
     * unsaved Store instance so it always goes through the exact same
     * Store::path()/urlFor() logic the live site itself resolves against —
     * see AbsoluteUrlRegistry for why this then gets checked against every
     * region, not just this one.
     */
    private function prospectiveUrl(array $data, Region $region): string
    {
        $prospective = new Store([
            'slug' => $data['slug'],
            'store_slug_prefix_id' => $data['store_slug_prefix_id'] ?? null,
            'store_slug_suffix_id' => $data['store_slug_suffix_id'] ?? null,
            'starts_from_root' => $data['starts_from_root'] ?? false,
        ]);

        return $prospective->urlFor($region);
    }

    private function uniqueSlug(string $name, int $regionId, ?int $exceptId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (
            Store::where('region_id', $regionId)->where('slug', $slug)
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }
}
