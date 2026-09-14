<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A separate curation flag from is_featured/is_popular, and unrelated to
     * the store's Active/Pending State select (which drives frontend
     * visibility) — this one only controls whether the store appears in the
     * "Pending Stores" tab of the Featured & Popular classification screen.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('is_pending')->default(false)->after('is_popular');
            $table->unsignedInteger('pending_order')->default(0)->after('is_pending');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['is_pending', 'pending_order']);
        });
    }
};
