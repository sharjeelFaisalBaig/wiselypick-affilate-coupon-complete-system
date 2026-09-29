<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_slug_suffixes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['region_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_slug_suffixes');
    }
};
