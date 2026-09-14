<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Offer;
use App\Models\PageSetting;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function suggest(Request $request, Region $region): \Illuminate\Http\JsonResponse
    {
        $q = $request->string('q')->value();
        $storeCategoryId = $request->integer('store_category_id');

        $offers = Offer::with('store')
            ->whereHas('store', fn ($sq) => $sq->where('region_id', $region->id)->visible()
                ->when($storeCategoryId, fn ($csq) => $csq->where('category_id', $storeCategoryId)))
            ->where('is_active', true)
            ->where(fn ($oq) => $oq->where('title', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
            ->limit(8)->get();

        return response()->json($offers->map(fn ($offer) => [
            'label' => "{$offer->title} — {$offer->store->name}",
            'url' => $offer->store->urlFor($region),
        ]));
    }

    public function index(Request $request, Region $region): View
    {
        $settings = PageSetting::forPage($region->id, 'coupons');
        abort_if($settings && ! $settings->is_active, 404);

        $query = Offer::with(['store.category', 'badges'])
            ->withCount(['badges as verified_priority' => fn ($q) => $q->where('name', 'Verified')])
            ->whereHas('store', fn ($q) => $q->where('region_id', $region->id)->visible())
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>', now()));

        // Promotions inherit their category from the assigned store (SRS
        // §9) — there is no independent promo-code category taxonomy.
        if ($request->filled('store_category_id')) {
            $category = Category::where('region_id', $region->id)->where('type', 'store')
                ->find($request->integer('store_category_id'));

            if ($category) {
                $query->whereHas('store', fn ($q) => $q->where('category_id', $category->id));
            }
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->value();
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('store', fn ($sq) => $sq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('store.category', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $query->orderByDesc('verified_priority')->orderByDesc('created_at');

        $offers = $query->paginate(18)->withQueryString();

        $storeCategories = Category::where('region_id', $region->id)->where('type', 'store')->where('is_active', true)
            ->orderBy('sort_order')->get(['id', 'name']);

        $viewData = [
            'region' => $region,
            'pageType' => 'coupons',
            'offers' => $offers,
            'storeCategories' => $storeCategories,
            'selectedStoreCategoryId' => $request->integer('store_category_id') ?: null,
            'heading' => $settings?->heading ?: "Today's Top Promo Codes & Coupons (".now()->format('F j, Y').')',
            'subheading' => $settings?->subheading,
            'seoTitle' => $settings?->meta_title ?: "Today's Top Promo Codes & Coupons — {$region->name} (".now()->format('F j, Y').')',
            'seoDescription' => $settings?->meta_description ?: ('Browse all active promo codes and coupons for stores in '.$region->name.', verified regularly.'),
            'ogTitle' => $settings?->og_title,
            'ogImage' => $settings?->og_image,
            'robotsIndex' => $settings?->robots_index ?? true,
            'robotsFollow' => $settings?->robots_follow ?? true,
        ];

        // AJAX-driven filtering (no page reload) — the JS layer pushes the
        // filtered URL via history.pushState so it stays fully shareable;
        // this just returns the results fragment instead of the full page.
        if ($request->header('X-Ajax-Filter')) {
            return view('public.partials.coupons-results', $viewData);
        }

        return view('public.coupons', $viewData);
    }
}
