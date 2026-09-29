<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->boolean('marketing_opt_in')->default(false)->after('phone');
            $table->timestamp('last_login_at')->nullable()->after('marketing_opt_in');

            // Staff reach Filament; customers never do.
            $table->boolean('is_staff')->default(false)->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'marketing_opt_in', 'last_login_at', 'is_staff']);
        });
    }
};
