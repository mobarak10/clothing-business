<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('name');
            $table->string('slug');

            // Full URL path, e.g. "clothing/suits/blue-suits". Denormalised on
            // purpose: resolving an incoming storefront URL is then a single
            // indexed lookup instead of walking the tree segment by segment.
            // The Category model's save hooks rebuild this, and rewrite every
            // descendant when an ancestor's slug moves.
            $table->string('path')->unique();

            $table->text('description')->nullable();
            $table->string('image_path')->nullable();

            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);

            // Pulls the category into the mega-menu's featured column.
            $table->boolean('is_featured')->default(false);

            $table->string('meta_title')->nullable();
            $table->string('meta_description', 500)->nullable();

            $table->timestamps();

            $table->unique(['parent_id', 'slug']);
            $table->index(['is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
