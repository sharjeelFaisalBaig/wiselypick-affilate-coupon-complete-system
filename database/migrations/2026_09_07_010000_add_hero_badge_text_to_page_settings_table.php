<?php

use App\Models\PageSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Homepage-only field (the small pill badge above the hero heading),
     * same pattern as hero_search_placeholder/hero_search_button_text —
     * lives on the shared page_settings table, harmless unused null on the
     * other 3 page keys.
     */
    public function up(): void
    {
        Schema::table('page_settings', function (Blueprint $table) {
            $table->string('hero_badge_text')->nullable()->after('hero_search_button_text');
        });

        PageSetting::where('page_key', 'home')->update([
            'hero_badge_text' => 'Verified daily by our editors',
        ]);
    }

    public function down(): void
    {
        Schema::table('page_settings', function (Blueprint $table) {
            $table->dropColumn('hero_badge_text');
        });
    }
};
