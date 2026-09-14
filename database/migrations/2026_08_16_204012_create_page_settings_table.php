<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->enum('page_key', ['stores', 'coupons', 'blogs']);
            $table->string('heading')->nullable();
            $table->text('subheading')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();

            $table->unique(['region_id', 'page_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_settings');
    }
};
