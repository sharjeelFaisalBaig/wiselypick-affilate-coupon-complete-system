<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            $table->enum('offer_type', ['coupon', 'deal']);
            $table->string('code')->nullable(); // required when offer_type = coupon
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('terms')->nullable();

            $table->enum('discount_type', ['flat', 'percentage', 'other']);
            $table->decimal('discount_value', 10, 2)->nullable(); // flat = USD base value, percentage = 0-100
            $table->string('badge_label')->nullable(); // override display text, e.g. "BOGO", "Free Shipping"

            $table->boolean('is_verified')->default(false);
            $table->boolean('is_exclusive')->default(false);
            $table->boolean('is_top_deal')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);

            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('clicks')->default(0);

            $table->timestamps();

            $table->index(['store_id', 'is_active', 'sort_order']);
            $table->index(['store_id', 'offer_type']);
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
