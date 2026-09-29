<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per payment attempt, not per order -- a shopper who fails twice
     * and succeeds on the third try leaves three rows, which is exactly what
     * you want when reconciling with the gateway.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            $table->string('gateway');

            // SSLCommerz hands back both a tran_id (ours) and a val_id (theirs);
            // validation needs the second one.
            $table->string('gateway_transaction_id')->nullable();
            $table->string('val_id')->nullable();

            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('BDT');
            $table->string('status')->default('pending');

            $table->string('card_type')->nullable();

            // Full gateway response, kept verbatim for dispute resolution.
            $table->json('raw_payload')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('gateway_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
