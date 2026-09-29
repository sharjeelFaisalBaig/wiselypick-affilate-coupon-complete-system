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
            // Blank only ever for the default region (item 18) — that's
            // what makes it root-mounted at "/" instead of "/{code}". The
            // existing unique index still allows this: MySQL never treats
            // two NULLs as duplicates against a UNIQUE index.
            $table->string('code', 5)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->string('code', 5)->nullable(false)->change();
        });
    }
};
