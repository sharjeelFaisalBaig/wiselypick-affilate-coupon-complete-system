<?php

use App\Models\Region;
use App\Models\StoreSuffix;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_suffixes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['region_id', 'name']);
        });

        // Only actually does anything when regions already exist at
        // migration time (e.g. an existing installation gaining this
        // taxonomy) — on a brand-new install, migrations run before any
        // region is seeded, so this is a no-op there; RegionDefaultsSeeder
        // is what guarantees a fresh install's regions get these rows.
        Region::all()->each(fn (Region $region) => StoreSuffix::seedDefaultsFor($region));
    }

    public function down(): void
    {
        Schema::dropIfExists('store_suffixes');
    }
};
