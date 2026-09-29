<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Human-facing reference, e.g. ATL-2026-000148.
            $table->string('order_number')->unique();

            // Guests can order, and a customer deleting their account must not
            // erase the financial record -- hence nullable + nullOnDelete.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('email');
            $table->string('phone');

            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');

            $table->string('currency', 3)->default('BDT');

            // All minor units. Every figure is stored rather than recomputed:
            // an invoice must still add up years later, whatever the rules
            // or prices have become since.
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('discount_total')->default(0);
            $table->unsignedBigInteger('shipping_total')->default(0);
            $table->unsignedBigInteger('tax_total')->default(0);
            $table->unsignedBigInteger('grand_total');

            $table->string('coupon_code')->nullable();

            $table->string('shipping_method_name')->nullable();

            // Frozen copies, not foreign keys -- see addresses table.
            $table->json('shipping_address');
            $table->json('billing_address');

            $table->text('customer_note')->nullable();
            $table->text('staff_note')->nullable();

            $table->timestamp('placed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'placed_at']);
            $table->index('status');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
