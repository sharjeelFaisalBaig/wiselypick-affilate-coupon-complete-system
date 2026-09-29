<?php

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
        Schema::table('general_settings', function (Blueprint $table) {
            // logo_link_page (existing) is the target used everywhere else
            // (the "main/exclusive site"); this is the target used only
            // while browsing the Blog section (blog listing/detail, and the
            // static pages — Contact Us/Terms/Privacy — which read as part
            // of the blog site now that it's the region's default landing
            // experience). Defaults to the Blog Listing page itself, i.e.
            // the region root.
            $table->string('logo_link_page_blog', 20)->default('blogs')->after('logo_link_page');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn('logo_link_page_blog');
        });
    }
};
