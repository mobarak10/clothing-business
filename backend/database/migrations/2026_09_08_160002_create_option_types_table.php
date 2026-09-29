<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Variant axes -- Size, Color, and anything added later.
     *
     * Deliberately not full EAV: products vary along a handful of named axes,
     * so two small tables buy all the flexibility this catalog needs without
     * the join cost and query gymnastics of a generic attribute store.
     */
    public function up(): void
    {
        Schema::create('option_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();

            // How the storefront renders the choices: colour chips vs. text pills.
            $table->enum('presentation', ['swatch', 'text'])->default('text');

            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('option_types');
    }
};
