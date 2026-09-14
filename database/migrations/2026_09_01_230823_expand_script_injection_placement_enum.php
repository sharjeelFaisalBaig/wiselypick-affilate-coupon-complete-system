<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The admin module only ever offered 2 of the 4 real injection points
     * (`head` rendered at the END of <head>, `body_end` before </body>) —
     * widening straight to the final enum would make any existing 'head'
     * row temporarily invalid mid-migration, so this widens first, renames
     * the data, then narrows.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE script_injections MODIFY placement ENUM('head','body_end','head_start','head_end','body_start') NOT NULL");

        DB::table('script_injections')->where('placement', 'head')->update(['placement' => 'head_end']);

        DB::statement("ALTER TABLE script_injections MODIFY placement ENUM('head_start','head_end','body_start','body_end') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE script_injections MODIFY placement ENUM('head','head_start','head_end','body_start','body_end') NOT NULL");

        DB::table('script_injections')->where('placement', 'head_end')->update(['placement' => 'head']);

        DB::statement("ALTER TABLE script_injections MODIFY placement ENUM('head','body_end') NOT NULL");
    }
};
