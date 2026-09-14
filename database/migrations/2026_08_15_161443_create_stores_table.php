<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('logo_path')->nullable();
            $table->text('about')->nullable();
            $table->string('website_url')->nullable();
            $table->string('affiliate_url');
            $table->date('expiry_date')->nullable();
            $table->decimal('star_rating', 2, 1)->default(0.0);

            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('featured_order')->default(0);
            $table->boolean('is_popular')->default(false);
            $table->unsignedInteger('popular_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);

            $table->timestamps();

            $table->unique(['region_id', 'slug']);
            $table->index(['region_id', 'is_featured', 'featured_order']);
            $table->index(['region_id', 'is_popular', 'popular_order']);
            $table->index(['region_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
