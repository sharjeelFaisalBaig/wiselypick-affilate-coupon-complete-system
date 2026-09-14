<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Offers/promotions have no detail page of their own — they only ever
     * appear as cards + a popup modal, so per-offer SEO metadata and script
     * injection points (borrowed from the Store pattern) never made sense
     * here. Only entities with an actual detail page (Store, and the fixed
     * Pages) keep these fields.
     */
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn([
                'meta_title', 'meta_description', 'og_title', 'og_description', 'og_image',
                'schema_script', 'head_start_script', 'head_end_script', 'body_start_script', 'body_end_script',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->text('schema_script')->nullable();
            $table->text('head_start_script')->nullable();
            $table->text('head_end_script')->nullable();
            $table->text('body_start_script')->nullable();
            $table->text('body_end_script')->nullable();
        });
    }
};
