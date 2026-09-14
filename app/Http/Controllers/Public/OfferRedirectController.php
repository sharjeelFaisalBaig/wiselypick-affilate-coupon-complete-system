<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;

class OfferRedirectController extends Controller
{
    public function __invoke(Region $region, Offer $offer): RedirectResponse
    {
        abort_unless($offer->store->region_id === $region->id, 404);

        $offer->recordClick();

        return redirect()->away($offer->redirectUrl());
    }
}
