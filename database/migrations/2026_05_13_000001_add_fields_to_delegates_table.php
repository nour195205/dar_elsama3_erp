<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns to delegates
        Schema::table('delegates', function (Blueprint $table) {
            $table->string('company')->nullable()->after('name');
            $table->string('phone')->nullable()->after('company');
            $table->text('notes')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('delegates', function (Blueprint $table) {
            $table->dropColumn(['company', 'phone', 'notes']);
        });
    }
};
