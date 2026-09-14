<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SRS: a store with active assigned promotions cannot be deleted.
     * Previously offers.store_id cascade-deleted, silently wiping a store's
     * offers on store deletion — the opposite of the SRS's requirement.
     * StoreController::destroy() now also checks this explicitly up front
     * (for a friendly error message); this FK change is the belt-and-
     * suspenders backstop in case a store row is ever deleted outside the
     * app layer.
     */
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('stores')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
        });
    }
};
