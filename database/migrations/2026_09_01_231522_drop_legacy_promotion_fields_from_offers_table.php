<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Promotions collapse to just Store/Type/Code/Title/Dates/Features per
     * the client's simplification — the admin now types the exact display
     * text (e.g. "15% Off", "$10 Off", "Free Shipping") directly into
     * Title, replacing all discount math and the currency-conversion path.
     * Redirects always come from the store's own affiliate_url now.
     */
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn([
                'description', 'terms', 'image_path', 'destination_url',
                'discount_type', 'discount_value', 'badge_label',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->text('terms')->nullable();
            $table->string('image_path')->nullable();
            $table->string('destination_url')->nullable();
            $table->enum('discount_type', ['flat', 'percentage', 'other'])->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->string('badge_label')->nullable();
        });
    }
};
