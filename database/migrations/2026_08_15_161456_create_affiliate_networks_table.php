<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_networks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('network_name'); // Amazon, eBay, CJ Affiliate, ShareASale...
            $table->string('tracking_id')->nullable();
            $table->text('api_key')->nullable(); // encrypted via cast
            $table->text('api_secret')->nullable(); // encrypted via cast
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('pending');
            $table->timestamps();

            $table->unique(['region_id', 'network_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_networks');
    }
};
