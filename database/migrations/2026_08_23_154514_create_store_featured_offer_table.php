<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin-curated selection of offers to show in the store detail page's
     * "More Verified {Store} Discount Codes" section — deliberately separate
     * from the store's full offer list (which already paginates above it).
     */
    public function up(): void
    {
        Schema::create('store_featured_offer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);

            $table->unique(['store_id', 'offer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_featured_offer');
    }
};
