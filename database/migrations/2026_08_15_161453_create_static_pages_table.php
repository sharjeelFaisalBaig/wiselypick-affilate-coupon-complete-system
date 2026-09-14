<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('static_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('slug'); // terms-of-use, privacy-policy, etc. ("contact" row holds intro copy only, form is functional code)
            $table->string('title');
            $table->longText('content');

            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['region_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('static_pages');
    }
};
