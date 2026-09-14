{{-- Expects: $region, $placement ('head_start'|'head_end'|'body_start'|'body_end'), $pageType, optional $storeId --}}
@php
    $injections = \App\Models\ScriptInjection::where('region_id', $region->id)
        ->where('placement', $placement)
        ->forPage($pageType, $storeId ?? null)
        ->get();

    // is_active here is deliberately NOT a rendering gate — it's read-only
    // "Connected/Inactive" status information for the admin only (per the
    // Affiliate Networks spec), unrelated to whether the script renders.
    $affiliateNetworkScripts = \App\Models\AffiliateNetwork::where('region_id', $region->id)
        ->where('placement', $placement)
        ->whereNotNull('script')
        ->get();
@endphp
@foreach ($injections as $injection)
    {!! $injection->script_content !!}
@endforeach
@foreach ($affiliateNetworkScripts as $network)
    {!! $network->script !!}
@endforeach
