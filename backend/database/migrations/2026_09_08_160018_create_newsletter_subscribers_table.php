<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();

            $table->timestamp('subscribed_at')->nullable();

            // Kept rather than deleted, so a re-subscribe can be distinguished
            // from a first sign-up and opt-outs are auditable.
            $table->timestamp('unsubscribed_at')->nullable();

            $table->string('source')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
    }
};
