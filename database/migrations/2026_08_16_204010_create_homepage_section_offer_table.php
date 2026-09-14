<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_section_offer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homepage_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);

            $table->unique(['homepage_section_id', 'offer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_section_offer');
    }
};
