<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('og_title')->nullable()->after('canonical_url');
            $table->string('og_description')->nullable()->after('og_title');
            $table->text('head_start_script')->nullable()->after('og_description');
            $table->text('head_end_script')->nullable()->after('head_start_script');
            $table->text('body_start_script')->nullable()->after('head_end_script');
            $table->text('body_end_script')->nullable()->after('body_start_script');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['og_title', 'og_description', 'head_start_script', 'head_end_script', 'body_start_script', 'body_end_script']);
        });
    }
};
