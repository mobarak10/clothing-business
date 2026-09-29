<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Boutique visits and made-to-measure fittings booked from the storefront.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();

            // Set when a logged-in customer books; guests book too.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', ['boutique', 'made_to_measure'])->default('boutique');

            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');

            $table->timestamp('preferred_at');
            $table->text('notes')->nullable();

            $table->string('status')->default('requested');

            $table->timestamps();

            $table->index(['store_id', 'preferred_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
