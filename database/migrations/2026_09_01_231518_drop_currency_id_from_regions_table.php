<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Regions no longer carry a currency assignment — offers no longer
     * store a numeric discount value to convert, so conversion_rate_to_usd
     * (its only consumer, Region::convertFromUsd()) goes with it.
     */
    public function up(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('currency_id');
        });

        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn('conversion_rate_to_usd');
        });
    }

    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->decimal('conversion_rate_to_usd', 10, 4)->default(1.0000);
        });

        Schema::table('regions', function (Blueprint $table) {
            $table->foreignId('currency_id')->nullable()->after('favicon_path')->constrained()->nullOnDelete();
        });
    }
};
