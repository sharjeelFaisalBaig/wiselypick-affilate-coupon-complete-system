<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-entity (not per-region) URL customization: each store/blog can
     * override the default "store"/"blog" path segment that comes before
     * its slug, and optionally add a trailing segment after it. Null means
     * "use the default" — nothing changes for existing rows until an admin
     * explicitly edits one.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('route_prefix')->nullable()->after('slug');
            $table->string('route_suffix')->nullable()->after('route_prefix');
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->string('route_prefix')->nullable()->after('slug');
            $table->string('route_suffix')->nullable()->after('route_prefix');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['route_prefix', 'route_suffix']);
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn(['route_prefix', 'route_suffix']);
        });
    }
};
