<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            // Null while the shopper is a guest; set when they log in and the
            // guest cart is merged into their account cart.
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();

            // Opaque handle stored in the cart_token cookie. This is what
            // identifies a guest's cart across requests.
            $table->uuid('token')->unique();

            $table->string('currency', 3)->default('BDT');
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
