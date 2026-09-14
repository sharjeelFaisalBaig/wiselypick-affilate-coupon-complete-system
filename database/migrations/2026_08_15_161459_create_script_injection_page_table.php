<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('script_injection_page', function (Blueprint $table) {
            $table->id();
            $table->foreignId('script_injection_id')->constrained()->cascadeOnDelete();
            $table->enum('page_type', [
                'home',
                'stores_directory',
                'store_detail',
                'category',
                'coupons',
                'blog_listing',
                'blog_detail',
                'static_page',
            ]);

            $table->unique(['script_injection_id', 'page_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('script_injection_page');
    }
};
