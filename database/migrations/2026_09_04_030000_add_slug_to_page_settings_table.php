<?php

use App\Models\PageSetting;
use App\Models\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The URL segment under {region}/ that each fixed page resolves at.
     * "" (empty string) means the page serves at the region root "/" —
     * exactly one page per region may hold that value at a time. Backfilled
     * here to today's hardcoded defaults so nothing changes until an admin
     * explicitly renames a page.
     */
    public function up(): void
    {
        Schema::table('page_settings', function (Blueprint $table) {
            $table->string('slug')->default('')->after('page_key');
        });

        $defaultSlugs = ['home' => '', 'stores' => 'stores', 'coupons' => 'coupons', 'blogs' => 'blogs'];

        Region::all()->each(function (Region $region) use ($defaultSlugs) {
            foreach ($defaultSlugs as $pageKey => $slug) {
                $setting = PageSetting::firstOrCreate(
                    ['region_id' => $region->id, 'page_key' => $pageKey],
                    ['is_active' => true]
                );
                $setting->update(['slug' => $slug]);
            }
        });

        Schema::table('page_settings', function (Blueprint $table) {
            $table->unique(['region_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('page_settings', function (Blueprint $table) {
            $table->dropUnique(['region_id', 'slug']);
            $table->dropColumn('slug');
        });
    }
};
