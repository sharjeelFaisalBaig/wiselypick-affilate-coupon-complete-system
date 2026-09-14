<?php

use App\Models\Currency;
use App\Models\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('is_active');
            $table->string('favicon_path')->nullable()->after('name');
            $table->foreignId('currency_id')->nullable()->after('currency_symbol')->constrained()->nullOnDelete();
            $table->text('head_start_script')->nullable()->after('conversion_rate_to_usd');
            $table->text('head_end_script')->nullable()->after('head_start_script');
            $table->text('body_start_script')->nullable()->after('head_end_script');
            $table->text('body_end_script')->nullable()->after('body_start_script');
        });

        // Local dev DB only (no production data) — backfill + drop the old
        // raw currency strings in this same migration rather than staging
        // it across separate deploys.
        Region::all()->each(function (Region $region) {
            $currency = Currency::where('iso_code', $region->currency_code)->first();
            if ($currency) {
                $region->update(['currency_id' => $currency->id]);
            }
        });

        $firstRegion = Region::orderBy('sort_order')->first();
        if ($firstRegion && ! Region::where('is_default', true)->exists()) {
            $firstRegion->update(['is_default' => true]);
        }

        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'currency_symbol']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->string('currency_code', 3)->default('USD');
            $table->string('currency_symbol', 5)->default('$');
        });

        Schema::table('regions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('currency_id');
            $table->dropColumn(['is_default', 'favicon_path', 'head_start_script', 'head_end_script', 'body_start_script', 'body_end_script']);
        });
    }
};
