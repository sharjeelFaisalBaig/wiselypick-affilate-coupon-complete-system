<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('canonical_url');
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn('canonical_url');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('canonical_url')->nullable();
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->string('canonical_url')->nullable();
        });
    }
};
