<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();

            $table->unsignedSmallInteger('quantity')->default(1);

            // Price captured when the line was added, so a mid-session
            // repricing never silently changes what the shopper agreed to.
            // Revalidated against the live price at checkout.
            $table->unsignedBigInteger('unit_price');

            $table->timestamps();

            // One line per variant; adding the same variant bumps quantity.
            $table->unique(['cart_id', 'product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
