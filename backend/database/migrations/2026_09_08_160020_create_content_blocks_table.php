<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The homepage, assembled in the admin rather than hardcoded.
     *
     * Each row is one full-width section (campaign hero, look grid, category
     * tiles, editorial split). `payload` holds whatever that type needs, so a
     * new section type costs a Filament form and a Vue component -- no migration.
     */
    public function up(): void
    {
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();

            // Lets the same table drive other assembled pages later.
            $table->string('placement')->default('homepage');

            $table->string('type');
            $table->string('admin_label')->nullable();

            $table->json('payload');

            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);

            // Campaign scheduling: a Fall/Winter hero can be queued in advance.
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->timestamps();

            $table->index(['placement', 'is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
    }
};
