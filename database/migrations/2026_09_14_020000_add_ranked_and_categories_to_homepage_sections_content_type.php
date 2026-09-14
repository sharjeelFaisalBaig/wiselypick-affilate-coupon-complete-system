<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Two new homepage section widgets (see HomepageSectionController):
     * 'ranked' — a numbered "Top Performing Coupons" leaderboard (reuses the
     * offers pivot, same as coupon/deal/mixed); 'categories' — an icon-grid
     * of store categories (new homepage_section_category pivot).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE homepage_sections MODIFY COLUMN content_type ENUM('coupon', 'deal', 'store', 'mixed', 'ranked', 'categories') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE homepage_sections MODIFY COLUMN content_type ENUM('coupon', 'deal', 'store', 'mixed') NOT NULL");
    }
};
