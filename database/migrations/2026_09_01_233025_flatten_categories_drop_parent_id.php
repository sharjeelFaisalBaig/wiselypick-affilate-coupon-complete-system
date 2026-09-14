<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Non-destructive: every category row survives untouched, only the
     * parent/child relationship is severed (set to NULL) before the column
     * itself is dropped — no category is deleted, no store's category_id
     * is reassigned.
     */
    public function up(): void
    {
        DB::table('categories')->update(['parent_id' => null]);

        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }

    /**
     * Restores the column itself, but not the original hierarchy — that
     * data was destroyed by up() and cannot be recovered from here.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('region_id')->constrained('categories')->nullOnDelete();
        });
    }
};
