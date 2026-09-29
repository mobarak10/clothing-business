<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Editorial and legal pages -- About, Care, Terms, Privacy.
     * Resolved through the storefront's catch-all route.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('excerpt')->nullable();

            $table->longText('body')->nullable();

            // Optional richer layout for editorial pages, where flat prose
            // is not enough.
            $table->json('blocks')->nullable();

            $table->boolean('is_published')->default(false);

            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
