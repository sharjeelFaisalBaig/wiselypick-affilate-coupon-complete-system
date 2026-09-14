<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('script_injections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // admin label, e.g. "Amazon Associates Tag"
            $table->enum('placement', ['head', 'body_end']);
            $table->longText('script_content'); // raw HTML/JS
            $table->enum('target_type', ['all_pages', 'specific_pages', 'specific_stores']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['region_id', 'target_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('script_injections');
    }
};
