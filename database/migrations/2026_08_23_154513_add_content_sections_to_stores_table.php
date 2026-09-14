<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // "Why search for {Store} coupons?" banner — text+button left, image right.
            $table->string('banner_heading')->nullable()->after('faqs');
            $table->text('banner_text')->nullable()->after('banner_heading');
            $table->string('banner_image')->nullable()->after('banner_text');
            $table->string('banner_button_text')->nullable()->after('banner_image');
            $table->string('banner_button_url')->nullable()->after('banner_button_text');

            // "How We Curate Our Codes" — two independently rich-text columns.
            $table->text('curate_left_content')->nullable()->after('banner_button_url');
            $table->text('curate_right_content')->nullable()->after('curate_left_content');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'banner_heading',
                'banner_text',
                'banner_image',
                'banner_button_text',
                'banner_button_url',
                'curate_left_content',
                'curate_right_content',
            ]);
        });
    }
};
