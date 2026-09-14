<?php

use App\Models\Badge;
use App\Models\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['region_id', 'name']);
        });

        Region::all()->each(fn (Region $region) => Badge::seedDefaultsFor($region));
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
