<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_group_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->unique(['permission_group_id', 'permission_id'], 'pg_perm_unique');
        });

        Schema::create('permission_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['permission_group_id', 'user_id'], 'pg_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_group_user');
        Schema::dropIfExists('permission_group_permission');
        Schema::dropIfExists('permission_groups');
    }
};
