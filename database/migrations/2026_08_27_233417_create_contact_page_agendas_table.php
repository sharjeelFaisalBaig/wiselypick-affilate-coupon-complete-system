<?php

use App\Models\ContactPageAgenda;
use App\Models\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_page_agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->string('label');
            $table->string('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['region_id', 'value']);
        });

        // Replaces the hardcoded bug/suggestion/partnership/general list that
        // used to live directly in ContactController + contact.blade.php.
        Region::all()->each(fn (Region $region) => ContactPageAgenda::seedDefaultsFor($region));
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_page_agendas');
    }
};
