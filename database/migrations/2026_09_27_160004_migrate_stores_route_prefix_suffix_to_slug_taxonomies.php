<?php

use App\Models\Store;
use App\Models\StoreSlugPrefix;
use App\Models\StoreSlugSuffix;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * route_prefix/route_suffix move from free-text columns to admin-managed
     * taxonomy dropdowns (store_slug_prefixes/store_slug_suffixes), same shape
     * as store_suffix_id's FK. Every distinct existing free-text value is
     * turned into a taxonomy row (scoped to the store's own region) and every
     * store referencing it is repointed at that row, so no existing
     * prefix/suffix silently disappears from a store's live URL.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->foreignId('store_slug_prefix_id')->nullable()->after('route_suffix')->constrained('store_slug_prefixes')->nullOnDelete();
            $table->foreignId('store_slug_suffix_id')->nullable()->after('store_slug_prefix_id')->constrained('store_slug_suffixes')->nullOnDelete();
        });

        Store::whereNotNull('route_prefix')->get(['id', 'region_id', 'route_prefix'])->each(function (Store $store) {
            $prefix = StoreSlugPrefix::firstOrCreate(['region_id' => $store->region_id, 'value' => $store->route_prefix]);
            $store->update(['store_slug_prefix_id' => $prefix->id]);
        });

        Store::whereNotNull('route_suffix')->get(['id', 'region_id', 'route_suffix'])->each(function (Store $store) {
            $suffix = StoreSlugSuffix::firstOrCreate(['region_id' => $store->region_id, 'value' => $store->route_suffix]);
            $store->update(['store_slug_suffix_id' => $suffix->id]);
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['route_prefix', 'route_suffix']);
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('route_prefix')->nullable()->after('slug');
            $table->string('route_suffix')->nullable()->after('route_prefix');
        });

        Store::with('storeSlugPrefix', 'storeSlugSuffix')->get()->each(function (Store $store) {
            $store->update([
                'route_prefix' => $store->storeSlugPrefix?->value,
                'route_suffix' => $store->storeSlugSuffix?->value,
            ]);
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_slug_prefix_id');
            $table->dropConstrainedForeignId('store_slug_suffix_id');
        });
    }
};
