<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('option_value_product_variant', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();

            // Restrict, not cascade: deleting an option value that is already
            // sold under would silently corrupt existing variants.
            $table->foreignId('option_value_id')->constrained()->restrictOnDelete();

            $table->primary(['product_variant_id', 'option_value_id'], 'ovpv_primary');

            // Drives listing filters: "every variant that is Navy".
            $table->index('option_value_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('option_value_product_variant');
    }
};
