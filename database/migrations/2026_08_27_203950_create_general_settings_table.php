<?php

use App\Models\GeneralSetting;
use App\Models\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->unique()->constrained()->cascadeOnDelete();
            $table->longText('footer_text')->nullable();
            $table->longText('footer_disclaimer')->nullable();
            $table->string('rights_text')->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });

        // Seed one row per existing region from today's hardcoded footer
        // copy so the visual cutover in a later phase doesn't regress.
        Region::all()->each(function (Region $region) {
            GeneralSetting::firstOrCreate(['region_id' => $region->id], [
                'footer_text' => "<p>dealhub finds coupon codes, discount sales and promotions for e-commerce stores listed in our store directory. While promo codes are time-sensitive and may expire, we have human editors verify discount codes at retailer websites to ensure they work at the time of test.</p>"
                    ."<p>To redeem a promo code for a discount, simply copy the code to your clipboard, then paste it into the coupon code entry box during checkout on the retailer's website. Look for a confirmation message that your discount has been applied.</p>"
                    ."<p>dealhub may earn a commission when you make a purchase through our links. This is part of our affiliate marketing relationship with certain retailers.</p>",
                'footer_disclaimer' => 'dealhub is a third-party shopping platform. All trademarks, service marks, logos, and brand names are the property of their respective owners and are used for identification purposes only. The use of these names, trademarks, and brands does not imply any affiliation with or endorsement by dealhub.',
                'rights_text' => '© 2015 – '.date('Y').' dealhub. All rights reserved.',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
