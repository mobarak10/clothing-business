<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            // The storefront URL segment, e.g. "navy-wool-suit-19220as1031531".
            // Carries the style code so slugs stay unique across seasons.
            $table->string('slug')->unique();

            // Style code shared by every variant of this product.
            $table->string('sku_root')->unique();

            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->text('composition')->nullable();
            $table->text('care_instructions')->nullable();
            $table->string('fit')->nullable();

            // Minor units (poisha). Variants may override; this is the fallback
            // and the value listings sort and filter on.
            $table->unsignedBigInteger('base_price');
            $table->unsignedBigInteger('compare_at_price')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_new_arrival')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'published_at']);
            $table->index('base_price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
