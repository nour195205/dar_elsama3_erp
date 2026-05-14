<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'name',
        'type',
        'address',
        'commission_type',
        'commission_value',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
    ];

    public function referredPatients()
    {
        return $this->hasMany(Patient::class, 'referring_doctor_id');
    }

    public function internalPatients()
    {
        return $this->hasMany(Patient::class, 'internal_doctor_id');
    }

    public function delegateVisits()
    {
        return $this->hasMany(DelegateVisit::class);
    }

    public function payouts()
    {
        return $this->hasMany(DoctorPayout::class);
    }
}
