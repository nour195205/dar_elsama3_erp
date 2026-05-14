<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorPayout extends Model
{
    protected $fillable = [
        'doctor_id',
        'doctor_type',
        'patient_id',
        'amount',
        'calculation_basis',
        'calculation_value',
        'date',
        'is_paid',
        'paid_at',
    ];

    protected $casts = [
        'amount'            => 'decimal:2',
        'calculation_value' => 'decimal:2',
        'date'              => 'date',
        'is_paid'           => 'boolean',
        'paid_at'           => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
