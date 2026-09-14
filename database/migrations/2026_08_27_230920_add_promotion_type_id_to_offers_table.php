<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backfills promotion_type_id from the legacy discount_type enum,
     * per-region, matching offer_type for the 'other' case (a deal with a
     * text-only discount becomes "Deals", a coupon becomes "Special
     * Coupons"). discount_type itself is kept (not dropped) — still read by
     * Offer::displayLabelFor() and the admin form's dynamic-field JS.
     */
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->foreignId('promotion_type_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
        });

        $slugByDiscountType = [
            'flat' => 'flat-rate-discount',
            'percentage' => 'percentage-discount',
        ];

        DB::table('offers')->join('stores', 'stores.id', '=', 'offers.store_id')
            ->select('offers.id', 'offers.discount_type', 'offers.offer_type', 'stores.region_id')
            ->orderBy('offers.id')
            ->chunk(500, function ($rows) use ($slugByDiscountType) {
                $typesByRegion = [];

                foreach ($rows as $row) {
                    $slug = $slugByDiscountType[$row->discount_type]
                        ?? ($row->offer_type === 'deal' ? 'deals' : 'special-coupons');

                    $typesByRegion[$row->region_id] ??= DB::table('promotion_types')
                        ->where('region_id', $row->region_id)->pluck('id', 'slug');

                    $typeId = $typesByRegion[$row->region_id][$slug] ?? null;
                    if ($typeId) {
                        DB::table('offers')->where('id', $row->id)->update(['promotion_type_id' => $typeId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotion_type_id');
        });
    }
};
