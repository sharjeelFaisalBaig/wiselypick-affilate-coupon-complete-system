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
        Schema::table('regions', function (Blueprint $table) {
            // Each nullable text column holds one absolute URL per line,
            // appended to this region's own dynamically-generated robots.txt
            // as "Allow: {url}" / "Disallow: {url}" lines.
            $table->text('robots_extra_allow')->nullable()->after('canonical_base_url');
            $table->text('robots_extra_disallow')->nullable()->after('robots_extra_allow');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn(['robots_extra_allow', 'robots_extra_disallow']);
        });
    }
};
