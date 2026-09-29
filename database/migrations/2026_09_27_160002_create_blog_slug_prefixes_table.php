<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_slug_prefixes', function (Blueprint $table) {
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
        Schema::dropIfExists('blog_slug_prefixes');
    }
};
