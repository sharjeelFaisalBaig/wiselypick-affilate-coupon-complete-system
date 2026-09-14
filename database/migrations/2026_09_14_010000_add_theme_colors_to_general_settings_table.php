<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->string('primary_color', 7)->default('#10b981')->after('logo_path');
            $table->string('deal_color', 7)->default('#ff7900')->after('primary_color');
            $table->string('dark_surface_color', 7)->default('#0f172a')->after('deal_color');
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['primary_color', 'deal_color', 'dark_surface_color']);
        });
    }
};
