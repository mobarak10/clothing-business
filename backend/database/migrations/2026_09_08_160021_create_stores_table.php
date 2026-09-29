<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();

            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('city');
            $table->string('district')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country_code', 2)->default('BD');

            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // 7 decimal places ~ 1cm precision; ample for map pins.
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // e.g. {"mon": ["10:00", "20:00"], "sun": null}
            $table->json('opening_hours')->nullable();

            $table->string('image_path')->nullable();

            $table->boolean('offers_made_to_measure')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'country_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
