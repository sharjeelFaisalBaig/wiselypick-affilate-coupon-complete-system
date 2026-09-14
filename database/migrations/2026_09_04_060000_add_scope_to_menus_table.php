<?php

use App\Models\Menu;
use App\Models\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The blog section gets its own fully independent set of the same 4
     * fixed menus (1 header + 3 footer) — not an extra 5th menu supplementing
     * the global header/footer, but a real replacement while browsing blog
     * pages. `scope` distinguishes the two parallel sets sharing the same
     * `slot` value. Existing rows become 'global'; the 'blog' counterparts
     * are created here for any region that already exists (an upgrade path)
     * — on a brand-new install this is a no-op, same reason as every other
     * region-scoped seed in this app (see Menu::seedDefaultItemsFor()'s
     * docblock) — RegionDefaultsSeeder is what guarantees a fresh install
     * gets them.
     */
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->string('scope')->default('global')->after('slot');
        });

        // region_id's own foreign key needs SOME index on it at all times —
        // dropping the (region_id, slot) unique index (the only index that
        // happened to cover region_id) in the same breath as adding its
        // replacement fails in MySQL/InnoDB, so a plain index bridges the gap.
        Schema::table('menus', function (Blueprint $table) {
            $table->index('region_id', 'menus_region_id_index');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropUnique(['region_id', 'slot']);
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->unique(['region_id', 'slot', 'scope']);
        });

        Region::all()->each(fn (Region $region) => Menu::seedDefaultItemsFor($region));

        // Cleans up the earlier, superseded "Blog Page Menu" 5th-slot rows
        // (slot='blog') from before this migration — replaced entirely by
        // the scope-based approach above.
        DB::table('menu_items')->whereIn('menu_id', DB::table('menus')->where('slot', 'blog')->pluck('id'))->delete();
        DB::table('menus')->where('slot', 'blog')->delete();
    }

    public function down(): void
    {
        DB::table('menu_items')->whereIn('menu_id', DB::table('menus')->where('scope', 'blog')->pluck('id'))->delete();
        DB::table('menus')->where('scope', 'blog')->delete();

        Schema::table('menus', function (Blueprint $table) {
            $table->dropUnique(['region_id', 'slot', 'scope']);
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->unique(['region_id', 'slot']);
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex('menus_region_id_index');
            $table->dropColumn('scope');
        });
    }
};
