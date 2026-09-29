<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets a store/blog opt out of its slug prefix entirely — its path()
     * becomes just "{slug}[/{suffix}]" with no prefix segment at all, even
     * under a region that itself sits at a non-blank "/{code}" prefix (the
     * region prefix is untouched; only the entity's OWN prefix is dropped).
     * The prefix dropdown is disabled client-side while this is checked
     * (see stores/form.blade.php, blogs/form.blade.php) and the controller
     * also nulls out the prefix id server-side regardless of what was
     * submitted, so the two are mutually exclusive no matter what.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('starts_from_root')->default(false)->after('store_slug_suffix_id');
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->boolean('starts_from_root')->default(false)->after('blog_slug_suffix_id');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('starts_from_root');
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn('starts_from_root');
        });
    }
};
