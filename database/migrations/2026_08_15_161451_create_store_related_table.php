<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_related', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_store_id')->constrained('stores')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);

            $table->unique(['store_id', 'related_store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_related');
    }
};
