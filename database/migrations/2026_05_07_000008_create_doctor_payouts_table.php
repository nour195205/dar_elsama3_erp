<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->enum('doctor_type', ['internal', 'external']);
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('calculation_basis'); // 'Flat' or 'Percentage'
            $table->decimal('calculation_value', 10, 2);
            $table->date('date');
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['doctor_id', 'is_paid']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_payouts');
    }
};
