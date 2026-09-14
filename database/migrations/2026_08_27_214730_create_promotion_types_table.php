<?php

use App\Models\PromotionType;
use App\Models\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->enum('discount_format', ['percentage', 'flat', 'deal', 'custom_text']);
            $table->boolean('is_system_default')->default(false);
            $table->timestamps();

            $table->unique(['region_id', 'slug']);
        });

        Region::all()->each(fn (Region $region) => PromotionType::seedSystemDefaultsFor($region));
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_types');
    }
};
