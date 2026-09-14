<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('script_injection_store', function (Blueprint $table) {
            $table->id();
            $table->foreignId('script_injection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            $table->unique(['script_injection_id', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('script_injection_store');
    }
};
