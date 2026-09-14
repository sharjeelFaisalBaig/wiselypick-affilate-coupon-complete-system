<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SRS §6 Row 9 wants an admin-managed repeater of titled rich-text
     * sections, replacing the earlier two-fixed-column "How We Curate Our
     * Codes" design. curate_left_content is folded into this repeater as
     * its first titled item so no admin-entered content is lost;
     * curate_right_content is kept as-is (it maps to Row 6's separate
     * right-column paragraph, not this repeater).
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->json('custom_sections')->nullable()->after('curate_right_content');
        });

        DB::table('stores')->whereNotNull('curate_left_content')->where('curate_left_content', '!=', '')
            ->orderBy('id')->chunkById(200, function ($stores) {
                foreach ($stores as $store) {
                    DB::table('stores')->where('id', $store->id)->update([
                        'custom_sections' => json_encode([
                            ['title' => 'How We Curate Our Codes', 'content' => $store->curate_left_content],
                        ]),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('custom_sections');
        });
    }
};
