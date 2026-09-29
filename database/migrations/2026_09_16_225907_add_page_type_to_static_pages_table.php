<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            // Decouples "this is THE contact/terms/privacy page" from the
            // freely-editable `slug` column — StaticPageController::show()
            // and PagesOverviewController used to match on the literal slug
            // string 'contact' (etc.), which silently broke (the contact
            // form stopped rendering, the admin overview's deep-link went
            // stale) the moment an admin renamed that page's URL.
            $table->string('page_type', 20)->nullable()->after('slug');
        });

        // Backfill existing rows created under the old seed defaults, which
        // always used these exact slugs — a fresh install seeds page_type
        // directly (see StaticPage::SEED_DEFAULTS), this is only for rows
        // that already existed before this column did.
        DB::table('static_pages')->where('slug', 'contact')->update(['page_type' => 'contact']);
        DB::table('static_pages')->where('slug', 'terms-of-use')->update(['page_type' => 'terms']);
        DB::table('static_pages')->where('slug', 'privacy-policy')->update(['page_type' => 'privacy']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            $table->dropColumn('page_type');
        });
    }
};
