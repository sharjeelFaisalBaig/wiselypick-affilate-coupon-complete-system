<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            $table->enum('cta_target', ['same_tab', 'new_tab'])->default('same_tab')->after('cta_url');
        });
    }

    public function down(): void
    {
        Schema::table('homepage_sections', function (Blueprint $table) {
            $table->dropColumn('cta_target');
        });
    }
};
