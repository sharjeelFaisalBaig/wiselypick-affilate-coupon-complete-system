<?php

use App\Models\Menu;
use App\Models\Region;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Only actually does anything when regions already exist at migration
     * time (e.g. an existing installation gaining the Menu Manager module).
     * On a brand-new install, migrations run before any region is seeded,
     * so this is a no-op there — Menu::seedDefaultItemsFor() is what
     * actually guarantees every region gets its default links, called from
     * both RegionController::store() (new regions via the admin UI) and
     * the RegionSeeder (fresh installs).
     */
    public function up(): void
    {
        Region::all()->each(fn (Region $region) => Menu::seedDefaultItemsFor($region));
    }

    public function down(): void
    {
        // Data-only migration — nothing to reverse beyond dropping the tables
        // themselves, which the create_menus_table/create_menu_items_table
        // migrations already handle.
    }
};
