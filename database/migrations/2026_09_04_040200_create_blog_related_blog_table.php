<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replaces the old blog_store pivot (a "Related Stores" picker that
     * never actually rendered on the frontend) with a proper self-
     * referencing "Related Blogs" picker, used only when a blog's
     * auto_link_related_blogs flag is off.
     */
    public function up(): void
    {
        Schema::create('blog_related_blog', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_blog_id')->constrained('blogs')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);

            $table->unique(['blog_id', 'related_blog_id']);
        });

        Schema::dropIfExists('blog_store');
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_related_blog');

        Schema::create('blog_store', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);

            $table->unique(['blog_id', 'store_id']);
        });
    }
};
