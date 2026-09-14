<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();

            $table->string('author_name')->nullable();
            $table->string('author_avatar')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('reading_time_minutes')->default(1);

            $table->json('toc')->nullable(); // extracted table-of-contents headings
            $table->json('faqs')->nullable(); // FAQ schema question/answer pairs

            $table->boolean('is_published')->default(false);

            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->enum('schema_type', ['Article', 'BlogPosting'])->default('BlogPosting');

            $table->boolean('auto_compress_images')->default(true);
            $table->boolean('convert_to_webp')->default(true);
            $table->boolean('enable_amp')->default(false);
            $table->boolean('related_stores_auto_link')->default(true);

            $table->timestamps();

            $table->unique(['region_id', 'slug']);
            $table->index(['region_id', 'is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
