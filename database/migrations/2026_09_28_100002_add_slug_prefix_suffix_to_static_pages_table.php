<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Static pages move off the fixed "/p/{slug}" literal route (removed
     * from routes/web.php) onto the same admin-managed
     * prefix/suffix-taxonomy pattern Store/Blog already use — default is
     * blank (root-mounted bare slug), matching what "/p/" already
     * degenerated to via PageRouterController's fallback resolution.
     */
    public function up(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            $table->foreignId('page_slug_prefix_id')->nullable()->after('slug')->constrained('page_slug_prefixes')->nullOnDelete();
            $table->foreignId('page_slug_suffix_id')->nullable()->after('page_slug_prefix_id')->constrained('page_slug_suffixes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('static_pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('page_slug_prefix_id');
            $table->dropConstrainedForeignId('page_slug_suffix_id');
        });
    }
};
