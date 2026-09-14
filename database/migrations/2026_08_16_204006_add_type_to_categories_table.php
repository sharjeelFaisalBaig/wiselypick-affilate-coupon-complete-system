<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->enum('type', ['store', 'promo_code'])->default('store')->after('region_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            // Add the new unique index before dropping the old one — MySQL
            // won't drop an index on region_id while it's the only index
            // backing the categories.region_id -> regions.id foreign key.
            $table->unique(['region_id', 'type', 'slug']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['region_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['region_id', 'slug']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['region_id', 'type', 'slug']);
            $table->dropColumn('type');
        });
    }
};
