<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_type_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('slug');

            // Swatch fill for colours; null for text options like sizes.
            $table->string('hex', 7)->nullable();

            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['option_type_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('option_values');
    }
};
