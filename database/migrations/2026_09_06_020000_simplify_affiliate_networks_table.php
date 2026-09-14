<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Collapses this module to exactly Network Name + Script + Placement +
     * is_active ("Connected/Inactive") — tracking_id/api_key/api_secret/
     * sync_status/last_synced_at are dropped entirely; is_active already
     * existed and already meant "Connected/Active" vs not, so it's untouched.
     */
    public function up(): void
    {
        Schema::table('affiliate_networks', function (Blueprint $table) {
            $table->dropColumn(['tracking_id', 'api_key', 'api_secret', 'last_synced_at', 'sync_status']);
            $table->longText('script')->nullable()->after('network_name');
            $table->enum('placement', ['head_start', 'head_end', 'body_start', 'body_end'])->default('head_end')->after('script');
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_networks', function (Blueprint $table) {
            $table->dropColumn(['script', 'placement']);
            $table->string('tracking_id')->nullable();
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_status')->default('pending');
        });
    }
};
