<?php

use App\Models\GeneralSetting;
use App\Models\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->longText('store_page_disclaimer')->nullable()->after('footer_disclaimer');
        });

        // Backfill with the exact copy that was previously hardcoded directly
        // into public/store.blade.php, so the visual cutover doesn't regress.
        $default = "Dealhub is a shopping community that curates offers for brands we think you'll love. When you buy through our links, we may earn a commission.";

        Region::all()->each(function (Region $region) use ($default) {
            $settings = GeneralSetting::forRegion($region->id);
            $settings->store_page_disclaimer = $default;
            $settings->region_id = $region->id;
            $settings->save();
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn('store_page_disclaimer');
        });
    }
};
