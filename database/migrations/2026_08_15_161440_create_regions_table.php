<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique(); // us, uk, au, es
            $table->string('name');
            $table->string('currency_code', 3); // USD, GBP, AUD
            $table->string('currency_symbol', 5); // $, £, A$
            $table->decimal('conversion_rate_to_usd', 10, 4)->default(1.0000);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
