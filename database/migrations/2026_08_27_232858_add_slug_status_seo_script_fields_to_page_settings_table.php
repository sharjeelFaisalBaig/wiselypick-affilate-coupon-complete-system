<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Raw ALTER to widen the enum (Schema::table()->change() would need
        // doctrine/dbal just for this one column).
        DB::statement("ALTER TABLE page_settings MODIFY page_key ENUM('home', 'stores', 'coupons', 'blogs', 'contact') NOT NULL");

        Schema::table('page_settings', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('page_key');
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
        Schema::table('page_settings', function (Blueprint $table) {
            $table->dropColumn([
                'is_active', 'og_title', 'og_description', 'og_image', 'schema_script',
                'head_start_script', 'head_end_script', 'body_start_script', 'body_end_script',
            ]);
        });

        DB::statement("ALTER TABLE page_settings MODIFY page_key ENUM('stores', 'coupons', 'blogs') NOT NULL");
    }
};
