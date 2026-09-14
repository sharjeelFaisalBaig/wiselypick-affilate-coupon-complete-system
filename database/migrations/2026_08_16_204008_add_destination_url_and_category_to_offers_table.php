<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->string('destination_url')->nullable()->after('code');
            $table->foreignId('category_id')->nullable()->after('store_id')
                ->constrained('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn('destination_url');
        });
    }
};
