<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['is_verified', 'is_exclusive', 'is_top_deal', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_exclusive')->default(false);
            $table->boolean('is_top_deal')->default(false);
            $table->boolean('is_featured')->default(false);
        });
    }
};
