<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SRS correction: "Promotions inherit their Category classification
     * directly from the assigned Store/Brand entity" — there is no
     * independent promo-code category taxonomy. offers.category_id (which
     * pointed at the now-removed "promo_code" category type) is obsolete.
     */
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('store_id')->constrained()->nullOnDelete();
        });
    }
};
