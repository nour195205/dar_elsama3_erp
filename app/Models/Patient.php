<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'name',
        'age',
        'phone',
        'address',
        'visit_type',
        'date',
        'referring_doctor_id',
        'internal_doctor_id',
        'test_type_id',
        'test_price',
        'supplies_cost',
        'commission_external',
        'commission_internal',
    ];

    protected $casts = [
        'date'                => 'date',
        'age'                 => 'integer',
        'test_price'          => 'decimal:2',
        'supplies_cost'       => 'decimal:2',
        'commission_external' => 'decimal:2',
        'commission_internal' => 'decimal:2',
    ];

    public function referringDoctor()
    {
        return $this->belongsTo(Doctor::class, 'referring_doctor_id');
    }

    public function internalDoctor()
    {
        return $this->belongsTo(Doctor::class, 'internal_doctor_id');
    }

    public function testType()
    {
        return $this->belongsTo(TestType::class);
    }
}
