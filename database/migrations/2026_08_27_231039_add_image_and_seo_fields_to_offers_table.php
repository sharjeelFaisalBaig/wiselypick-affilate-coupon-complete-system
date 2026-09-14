<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('badge_label');
            $table->string('meta_title')->nullable()->after('image_path');
            $table->string('meta_description')->nullable()->after('meta_title');
            $table->string('og_title')->nullable()->after('meta_description');
            $table->string('og_description')->nullable()->after('og_title');
            $table->string('og_image')->nullable()->after('og_description');
            $table->text('schema_script')->nullable()->after('og_image');
            $table->text('head_start_script')->nullable()->after('schema_script');
            $table->text('head_end_script')->nullable()->after('head_start_script');
            $table->text('body_start_script')->nullable()->after('head_end_script');
            $table->text('body_end_script')->nullable()->after('body_start_script');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn([
                'image_path', 'meta_title', 'meta_description', 'og_title', 'og_description',
                'og_image', 'schema_script', 'head_start_script', 'head_end_script', 'body_start_script', 'body_end_script',
            ]);
        });
    }
};
