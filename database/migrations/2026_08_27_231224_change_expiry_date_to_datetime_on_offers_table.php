<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Raw ALTER (rather than Schema::table()->change()) to avoid requiring
     * doctrine/dbal just for this one column-type change.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE offers MODIFY expiry_date DATETIME NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE offers MODIFY expiry_date DATE NULL');
    }
};
