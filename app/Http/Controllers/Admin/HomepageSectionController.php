<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Offer;
use App\Models\Region;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class HomepageSectionController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $sections = HomepageSection::where('region_id', $region->id)
            ->withCount(['offers', 'stores', 'categories'])
            ->orderBy('sort_order')
            ->get();

        return view('admin.homepage-sections.index', compact('sections'));
    }

    public function create(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        return view('admin.homepage-sections.form', [
            'section' => new HomepageSection(),
            'selectedOffers' => collect(),
            'selectedStores' => collect(),
            'selectedCategories' => collect(),
            ...$this->pickerOptions($region->id, $request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;
        $data['sort_order'] = HomepageSection::where('region_id', $region->id)->max('sort_order') + 1;

        $section = HomepageSection::create($data);
        $this->syncSelections($request, $section);

        return redirect()->route('admin.homepage-sections.index')->with('status', 'Homepage section created.');
    }

    public function edit(Request $request, HomepageSection $homepageSection): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $homepageSection->region_id);

        return view('admin.homepage-sections.form', [
            'section' => $homepageSection,
            'selectedOffers' => $homepageSection->offers()->with('store')->get(),
            'selectedStores' => $homepageSection->stores,
            'selectedCategories' => $homepageSection->categories,
            ...$this->pickerOptions($homepageSection->region_id, $request),
        ]);
    }

    public function update(Request $request, HomepageSection $homepageSection): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $homepageSection->region_id);

        $data = $this->validated($request);
        $homepageSection->update($data);
        $this->syncSelections($request, $homepageSection);

        return redirect()->route('admin.homepage-sections.index')->with('status', 'Homepage section updated.');
    }

    public function destroy(Request $request, HomepageSection $homepageSection): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $homepageSection->region_id);

        $homepageSection->delete();

        return redirect()->route('admin.homepage-sections.index')->with('status', 'Homepage section deleted.');
    }

    public function reorder(Request $request): Response
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');
        $ids = $request->validate(['ids' => ['required', 'array']])['ids'];

        foreach ($ids as $index => $id) {
            HomepageSection::where('id', $id)->where('region_id', $region->id)->update(['sort_order' => $index + 1]);
        }

        return response()->noContent();
    }

    private function syncSelections(Request $request, HomepageSection $section): void
    {
        $section->offers()->detach();
        $section->stores()->detach();
        $section->categories()->detach();

        if (in_array($section->content_type, ['coupon', 'deal', 'mixed', 'ranked'])) {
            $offerIds = collect($request->input('offer_ids', []))
                ->filter(fn ($id) => Offer::where('id', $id)->whereHas('store', fn ($q) => $q->where('region_id', $section->region_id))->exists())
                ->take(HomepageSection::MAX_OFFERS)
                ->values();

            $section->offers()->sync($offerIds->mapWithKeys(fn ($id, $i) => [$id => ['sort_order' => $i + 1]])->all());
        }

        if ($section->content_type === 'store') {
            $storeIds = collect($request->input('store_ids', []))
                ->filter(fn ($id) => Store::where('id', $id)->where('region_id', $section->region_id)->exists())
                ->take(HomepageSection::MAX_STORES)
                ->values();

            $section->stores()->sync($storeIds->mapWithKeys(fn ($id, $i) => [$id => ['sort_order' => $i + 1]])->all());
        }

        if ($section->content_type === 'categories') {
            $categoryIds = collect($request->input('category_ids', []))
                ->filter(fn ($id) => Category::where('id', $id)->where('region_id', $section->region_id)->exists())
                ->take(HomepageSection::MAX_CATEGORIES)
                ->values();

            $section->categories()->sync($categoryIds->mapWithKeys(fn ($id, $i) => [$id => ['sort_order' => $i + 1]])->all());
        }
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content_type' => ['required', 'in:coupon,deal,store,mixed,ranked,categories'],
            'cta_label' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'cta_target' => ['required', 'in:same_tab,new_tab'],
            'offer_ids' => ['nullable', 'array', 'max:'.HomepageSection::MAX_OFFERS],
            'offer_ids.*' => ['integer'],
            'store_ids' => ['nullable', 'array', 'max:'.HomepageSection::MAX_STORES],
            'store_ids.*' => ['integer'],
            'category_ids' => ['nullable', 'array', 'max:'.HomepageSection::MAX_CATEGORIES],
            'category_ids.*' => ['integer'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        unset($data['offer_ids'], $data['store_ids'], $data['category_ids']);

        return $data;
    }

    /**
     * Filter-dropdown option lists shared by all 3 picker modals (Category/
     * Store/Badge for offers, Category for stores) — a small, bounded set
     * loaded once with the page. The actual result rows are fetched live
     * from pickerResults() as the admin filters inside the modal.
     */
    private function pickerOptions(int $regionId, Request $request): array
    {
        return [
            'storeOptions' => Store::where('region_id', $regionId)->orderBy('name')->get(['id', 'name']),
            'storeCategoryOptions' => Category::where('region_id', $regionId)->where('type', 'store')->orderBy('name')->get(),
        ];
    }

    /**
     * Live results feed for the modal picker: same filter substance as the
     * old inline pickers (Category/Store/Badge for offers, Category/Search
     * for stores), now fetched via AJAX as the admin adjusts filters inside
     * the modal instead of a full page reload.
     */
    public function pickerResults(Request $request): \Illuminate\Http\JsonResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $type = $request->string('type')->value();
        abort_unless(in_array($type, ['coupon', 'deal', 'store', 'mixed', 'ranked', 'categories'], true), 422);

        if ($type === 'store') {
            $query = Store::where('region_id', $region->id)->where('is_active', true);

            if ($request->filled('filter_category_id')) {
                $query->where('category_id', $request->integer('filter_category_id'));
            }
            if ($request->filled('filter_search')) {
                $query->where('name', 'like', '%'.$request->string('filter_search').'%');
            }

            $items = $query->orderBy('name')->limit(100)->get()->map(fn (Store $store) => [
                'id' => $store->id,
                'label' => $store->name,
                'meta' => $store->category?->name,
            ]);

            return response()->json(['items' => $items]);
        }

        if ($type === 'categories') {
            $query = Category::where('region_id', $region->id)->where('type', 'store')->where('is_active', true);

            if ($request->filled('filter_search')) {
                $query->where('name', 'like', '%'.$request->string('filter_search').'%');
            }

            $items = $query->orderBy('name')->limit(100)->get()->map(fn (Category $category) => [
                'id' => $category->id,
                'label' => $category->name,
                'meta' => null,
            ]);

            return response()->json(['items' => $items]);
        }

        $query = Offer::with('store')->where('is_active', true)
            ->when(! in_array($type, ['mixed', 'ranked'], true), fn ($q) => $q->where('offer_type', $type))
            ->whereHas('store', fn ($q) => $q->where('region_id', $region->id));

        if ($request->filled('filter_store_id')) {
            $query->where('store_id', $request->integer('filter_store_id'));
        }
        if ($request->filled('filter_search')) {
            $search = $request->string('filter_search')->value();
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('store', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $items = $query->orderBy('title')->limit(100)->get()->map(fn (Offer $offer) => [
            'id' => $offer->id,
            'label' => $offer->store->name.' — '.$offer->title,
            'meta' => $offer->title,
        ]);

        return response()->json(['items' => $items]);
    }
}
