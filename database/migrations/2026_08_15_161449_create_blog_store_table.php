<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_store', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);

            $table->unique(['blog_id', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_store');
    }
};
