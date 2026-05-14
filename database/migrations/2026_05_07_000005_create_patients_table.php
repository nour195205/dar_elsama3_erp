<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('age');
            $table->string('phone');
            $table->string('address')->nullable();
            $table->string('visit_type')->nullable(); // 'Initial' or 'Follow-up'
            $table->date('date');
            $table->foreignId('referring_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('internal_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('test_type_id')->nullable()->constrained('test_types')->nullOnDelete();
            $table->decimal('test_price', 10, 2)->default(0);
            $table->decimal('supplies_cost', 10, 2)->default(0);
            $table->decimal('commission_external', 10, 2)->default(0);
            $table->decimal('commission_internal', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
