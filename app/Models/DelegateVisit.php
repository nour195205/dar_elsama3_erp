<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DelegateVisit extends Model
{
    protected $fillable = [
        'delegate_id',
        'doctor_id',
        'region',
        'day',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function delegate()
    {
        return $this->belongsTo(Delegate::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
