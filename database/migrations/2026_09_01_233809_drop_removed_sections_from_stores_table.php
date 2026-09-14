<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Removes the "Why search for" banner, FAQs, and the "How We Curate"
     * remnants (curate_right_content / custom_sections), plus Title
     * Prefix/Suffix — all per the client's Store simplification. SEO/script
     * fields and is_featured/is_popular are explicitly kept.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'title_prefix', 'title_suffix',
                'faqs',
                'banner_heading', 'banner_text', 'banner_image', 'banner_button_text', 'banner_button_url',
                'curate_right_content', 'custom_sections',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('title_prefix', 100)->nullable()->after('name');
            $table->string('title_suffix', 100)->nullable()->after('title_prefix');
            $table->json('faqs')->nullable()->after('about');
            $table->string('banner_heading')->nullable();
            $table->text('banner_text')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('banner_button_text')->nullable();
            $table->string('banner_button_url')->nullable();
            $table->text('curate_right_content')->nullable();
            $table->json('custom_sections')->nullable();
        });
    }
};
