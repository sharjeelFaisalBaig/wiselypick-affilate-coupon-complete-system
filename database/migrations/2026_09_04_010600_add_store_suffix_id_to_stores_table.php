<?php

use App\Models\Store;
use App\Models\StoreSuffix;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->foreignId('store_suffix_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
        });

        // Backfill every existing store to its region's "Promo Codes, Coupons
        // & Deals" suffix — the exact literal string every store heading
        // rendered before this taxonomy existed, so nothing changes visually.
        StoreSuffix::where('name', 'Promo Codes, Coupons & Deals')->get()->each(function (StoreSuffix $suffix) {
            Store::where('region_id', $suffix->region_id)->whereNull('store_suffix_id')->update(['store_suffix_id' => $suffix->id]);
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_suffix_id');
        });
    }
};
