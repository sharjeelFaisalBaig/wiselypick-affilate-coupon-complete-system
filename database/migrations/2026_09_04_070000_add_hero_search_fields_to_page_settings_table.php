<?php

use App\Models\PageSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Homepage-only fields (the hero search box), but kept on the shared
     * page_settings table rather than a new one-row table — harmless unused
     * nulls on the other 3 page keys, consistent with how every other
     * per-page field already lives here.
     */
    public function up(): void
    {
        Schema::table('page_settings', function (Blueprint $table) {
            $table->string('hero_search_placeholder')->nullable()->after('subheading');
            $table->string('hero_search_button_text')->nullable()->after('hero_search_placeholder');
        });

        PageSetting::where('page_key', 'home')->update([
            'hero_search_placeholder' => 'Search for a store or brand...',
            'hero_search_button_text' => 'Search',
        ]);
    }

    public function down(): void
    {
        Schema::table('page_settings', function (Blueprint $table) {
            $table->dropColumn(['hero_search_placeholder', 'hero_search_button_text']);
        });
    }
};
