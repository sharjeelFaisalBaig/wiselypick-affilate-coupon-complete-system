<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('title_prefix')->nullable()->after('name');
            $table->string('title_suffix')->nullable()->after('title_prefix');
            $table->unsignedInteger('reviews_count')->default(0)->after('star_rating');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['title_prefix', 'title_suffix', 'reviews_count']);
        });
    }
};
