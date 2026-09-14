<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Region;
use App\Models\Store;
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

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $query = Store::where('region_id', $region->id)->with('category');

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
            // Matches classification()'s pending query — either form of
            // pending (Store State or the checkbox) belongs in this tab.
            'pending' => $query->where(fn ($pq) => $pq->where('is_active', false)->orWhere('is_pending', true)),
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

        return view('admin.stores.form', [
            'store' => new Store(),
            'categories' => $categories,
            'storeSuffixes' => $storeSuffixes,
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

        return view('admin.stores.form', [
            'store' => $store,
            'categories' => $categories,
            'storeSuffixes' => $storeSuffixes,
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
        // A store is "pending" here if EITHER its Store State is Pending
        // (is_active false) OR the Pending curation checkbox is on — both
        // now equally hide it from the frontend (Store::scopeVisible()), so
        // both equally belong in this list rather than only the checkbox.
        $pending = Store::where('region_id', $region->id)
            ->where(fn ($q) => $q->where('is_active', false)->orWhere('is_pending', true))
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
            'route_prefix' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+(\/[a-z0-9-]+)*$/', $this->notReservedPrefix()],
            'route_suffix' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+(\/[a-z0-9-]+)*$/'],
            'about' => ['nullable', 'string'],
            'store_suffix_id' => [
                'nullable',
                Rule::exists('store_suffixes', 'id')->where('region_id', $region->id),
            ],
            'affiliate_url' => ['required', 'url:https,http', 'max:2048'],
            'expiry_date' => ['nullable', 'date'],
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
     * The prefix must not collide with one of the other fixed literal route
     * prefixes registered ahead of the catch-all (p/suggest/go/contact, and
     * the two-segment exclusive/category) — a store whose prefix collided
     * would never actually be reachable, since those routes always match
     * first. Note "exclusive" alone is NOT reserved — it's the store's own
     * default prefix (Store::DEFAULT_ROUTE_PREFIX) and must stay assignable.
     */
    private function notReservedPrefix(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (! $value) {
                return;
            }

            $segments = explode('/', strtolower(trim($value, '/')));
            if (in_array($segments[0], ['p', 'suggest', 'go', 'contact'], true)) {
                $fail("The prefix can't start with \"{$segments[0]}\" — that path is already used elsewhere on the site.");
            }
            if (($segments[0] ?? null) === 'exclusive' && ($segments[1] ?? null) === 'category') {
                $fail('The prefix can\'t be "exclusive/category" — that path is already used elsewhere on the site.');
            }
        };
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
