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
        Schema::table('general_settings', function (Blueprint $table) {
            // One email per line — who gets notified when this region's
            // Contact Us form is submitted (item 3: multiple recipients per
            // region). Nullable/empty means no admin notification is sent
            // for that region (the visitor's own thank-you email is
            // unaffected either way).
            $table->text('contact_notification_emails')->nullable()->after('logo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn('contact_notification_emails');
        });
    }
};
