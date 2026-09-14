<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The 'mixed' content type (coupons and deals together) was added to
     * the admin UI and controller validation without ever widening this
     * column's DB-level enum, so saving a 'mixed' section failed outright.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE homepage_sections MODIFY COLUMN content_type ENUM('coupon', 'deal', 'store', 'mixed') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE homepage_sections MODIFY COLUMN content_type ENUM('coupon', 'deal', 'store') NOT NULL");
    }
};
