<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneralSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'footer_text',
        'footer_disclaimer',
        'store_page_disclaimer',
        'rights_text',
        'logo_path',
        'primary_color',
        'deal_color',
        'dark_surface_color',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public static function forRegion(int $regionId): self
    {
        return self::firstOrNew(['region_id' => $regionId]);
    }

    /**
     * Only actually does anything when regions already exist at migration
     * time — on a brand-new install, migrations run before any region is
     * seeded, so the identical backfill loops in create_general_settings_table
     * and add_store_page_disclaimer_to_general_settings_table are no-ops
     * there (see Menu::seedDefaultItemsFor()'s docblock for the same
     * architectural reason); this is what guarantees a fresh install's
     * regions actually get real footer/disclaimer copy instead of the
     * public views' `@if ($generalSettings->footer_disclaimer)` guards
     * silently rendering nothing.
     */
    public static function seedDefaultsFor(Region $region): void
    {
        self::firstOrCreate(['region_id' => $region->id], [
            'footer_text' => "<p>WisleyPick finds coupon codes, discount sales and promotions for e-commerce stores listed in our store directory. While promo codes are time-sensitive and may expire, we have human editors verify discount codes at retailer websites to ensure they work at the time of test.</p>"
                ."<p>To redeem a promo code for a discount, simply copy the code to your clipboard, then paste it into the coupon code entry box during checkout on the retailer's website. Look for a confirmation message that your discount has been applied.</p>"
                ."<p>WisleyPick may earn a commission when you make a purchase through our links. This is part of our affiliate marketing relationship with certain retailers.</p>",
            'footer_disclaimer' => 'WisleyPick is a third-party shopping platform. All trademarks, service marks, logos, and brand names are the property of their respective owners and are used for identification purposes only. The use of these names, trademarks, and brands does not imply any affiliation with or endorsement by WisleyPick.',
            'store_page_disclaimer' => "WisleyPick is a shopping community that curates offers for brands we think you'll love. When you buy through our links, we may earn a commission.",
            'rights_text' => '© 2015 – '.date('Y').' WisleyPick. All rights reserved.',
        ]);
    }
}
