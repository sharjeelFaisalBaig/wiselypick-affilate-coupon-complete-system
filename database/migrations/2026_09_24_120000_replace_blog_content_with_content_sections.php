<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replaces the single `content` RTE field with a `content_sections`
     * repeater ([{title, content}, ...]) so the admin can split a post into
     * multiple sections, each with its own sidebar Quick Link. Every
     * existing blog's content becomes a single "Overview" section — the
     * same label the old hardcoded Quick Link already used — so published
     * posts render identically until an admin edits them.
     */
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->json('content_sections')->nullable()->after('content');
        });

        DB::table('blogs')->orderBy('id')->select('id', 'content')->each(function ($blog) {
            DB::table('blogs')->where('id', $blog->id)->update([
                'content_sections' => json_encode([
                    ['title' => 'Overview', 'content' => $blog->content ?? ''],
                ]),
            ]);
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->json('content_sections')->nullable(false)->change();
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->longText('content')->nullable()->after('excerpt');
        });

        // Lossy for a blog with more than one section — concatenates them
        // back into one field, which is the best a rollback can do.
        DB::table('blogs')->orderBy('id')->select('id', 'content_sections')->each(function ($blog) {
            $sections = json_decode($blog->content_sections ?? '[]', true) ?: [];
            DB::table('blogs')->where('id', $blog->id)->update([
                'content' => collect($sections)->pluck('content')->implode(''),
            ]);
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->longText('content')->nullable(false)->change();
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn('content_sections');
        });
    }
};
