<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Offer;
use App\Models\Region;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OfferController extends Controller
{
    use GuardsRegionOwnership;

    /**
     * Store-scoped only, per the client's simplification: a required Store
     * filter (auto-selected — the alphabetically-first store that actually
     * has offers, else the alphabetically-first store overall) plus a
     * search box and pagination. Drag-reorder (::) is built directly into
     * this listing rather than a separate manage-order page, since it only
     * ever makes sense scoped to one store's own sort_order sequence.
     */
    public function index(Request $request): View|RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        if ($request->missing('store_id')) {
            $defaultStore = Store::where('region_id', $region->id)->whereHas('offers')->orderBy('name')->first()
                ?? Store::where('region_id', $region->id)->orderBy('name')->first();

            if ($defaultStore) {
                return redirect()->route('admin.offers.index', ['store_id' => $defaultStore->id]);
            }
        }

        $stores = Store::where('region_id', $region->id)->orderBy('name')->get();
        $selectedStore = $request->filled('store_id')
            ? Store::where('region_id', $region->id)->find($request->integer('store_id'))
            : null;

        $query = Offer::with('badges')->where('store_id', $selectedStore?->id ?? 0);

        if ($request->filled('q')) {
            $search = $request->string('q')->value();
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('badges', fn ($bq) => $bq->where('name', 'like', "%{$search}%"));
            });
        }

        $offers = $query->orderBy('sort_order')->get();

        if ($request->header('X-Ajax-Filter')) {
            return view('admin.offers._results', compact('offers', 'selectedStore'));
        }

        return view('admin.offers.index', compact('offers', 'stores', 'selectedStore'));
    }

    public function suggest(Request $request): \Illuminate\Http\JsonResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');
        $q = $request->string('q')->value();

        $offers = Offer::with('store')
            ->whereHas('store', fn ($sq) => $sq->where('region_id', $region->id))
            ->where(fn ($oq) => $oq->where('title', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
            ->when($request->string('scope')->value() === 'featured', fn ($q) => $q->where('is_featured', true))
            // The main coupon list is store-scoped via this same `store_id`
            // sibling field — the list itself only re-filters on Search, but
            // the search box's suggestions still respect the selected store.
            ->when($request->filled('store_id'), fn ($q) => $q->where('store_id', $request->integer('store_id')))
            ->limit(8)->get();

        return response()->json($offers->map(fn ($offer) => [
            'label' => "{$offer->title} — {$offer->store->name}",
            'url' => route('admin.offers.edit', $offer),
        ]));
    }

    public function create(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $stores = Store::where('region_id', $region->id)->orderBy('name')->get();
        $selectedStore = $request->filled('store_id')
            ? Store::where('region_id', $region->id)->find($request->integer('store_id'))
            : null;

        return view('admin.offers.form', [
            'offer' => new Offer(),
            'stores' => $stores,
            'selectedStore' => $selectedStore,
            'badges' => Badge::where('region_id', $region->id)->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request, $region);
        $store = Store::where('region_id', $region->id)->findOrFail($data['store_id']);
        $badgeIds = $data['badge_ids'];
        unset($data['badge_ids']);
        $data['sort_order'] = $store->offers()->max('sort_order') + 1;

        $offer = Offer::create($data);
        $offer->badges()->sync($badgeIds);

        return redirect()->route('admin.offers.index', ['store_id' => $store->id])->with('status', 'Offer created.');
    }

    public function edit(Request $request, Offer $offer): View
    {
        $this->guardOffer($request, $offer);

        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        return view('admin.offers.edit', [
            'offer' => $offer,
            'stores' => Store::where('region_id', $region->id)->orderBy('name')->get(),
            'selectedStore' => $offer->store,
            'badges' => Badge::where('region_id', $region->id)->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Offer $offer): RedirectResponse
    {
        $this->guardOffer($request, $offer);

        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request, $region, $offer);
        $badgeIds = $data['badge_ids'];
        unset($data['badge_ids']);

        $offer->update($data);
        $offer->badges()->sync($badgeIds);

        return redirect()->route('admin.offers.index', ['store_id' => $offer->store_id])->with('status', 'Offer updated.');
    }

    public function destroy(Request $request, Offer $offer): RedirectResponse
    {
        $this->guardOffer($request, $offer);
        $storeId = $offer->store_id;

        $offer->delete();

        return redirect()->route('admin.offers.index', ['store_id' => $storeId])->with('status', 'Offer deleted.');
    }

    /**
     * Store-scoped via the URL segment (not a trusted request-body field —
     * drag-sort.js's drop handler only ever POSTs {ids}, so validating a
     * separate store_id in the body never actually worked).
     */
    public function reorder(Request $request, Store $store): Response
    {
        $this->abortUnlessOwnedByActiveRegion($request, $store->region_id);

        $data = $request->validate(['ids' => ['required', 'array']]);

        foreach ($data['ids'] as $index => $id) {
            Offer::where('id', $id)->where('store_id', $store->id)->update(['sort_order' => $index + 1]);
        }

        return response()->noContent();
    }

    private function guardOffer(Request $request, Offer $offer): void
    {
        $this->abortUnlessOwnedByActiveRegion($request, $offer->store->region_id);
    }

    private function validated(Request $request, Region $region, ?Offer $offer = null): array
    {
        $data = $request->validate([
            'store_id' => ['required', Rule::exists('stores', 'id')->where('region_id', $region->id)],
            'offer_type' => ['required', 'in:coupon,deal'],
            'code' => ['nullable', 'required_if:offer_type,coupon', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'clicks' => ['nullable', 'integer', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'badge_ids' => ['nullable', 'array', 'max:3'],
            'badge_ids.*' => [Rule::exists('badges', 'id')->where('region_id', $region->id)],
        ]);

        if ($data['offer_type'] === 'deal') {
            $data['code'] = null;
        }

        $data['badge_ids'] = $data['badge_ids'] ?? [];
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');

        if ($offer) {
            $data['clicks'] = $data['clicks'] ?? $offer->clicks;
        } else {
            unset($data['clicks']);
        }

        return $data;
    }
}
