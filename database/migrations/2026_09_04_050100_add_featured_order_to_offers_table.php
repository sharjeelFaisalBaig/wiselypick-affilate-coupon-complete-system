<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Distinct from `sort_order` (the per-store order used on that store's
     * own detail page) — this is the cross-store display order for the
     * "Featured Deals" tab of the classification screen, which lists every
     * featured offer across every store in the region together.
     */
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->unsignedInteger('featured_order')->default(0)->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('featured_order');
        });
    }
};
