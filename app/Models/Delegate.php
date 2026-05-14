<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delegate extends Model
{
    protected $fillable = [
        'name',
        'region',
        'company',
        'phone',
        'notes',
    ];

    public function visits()
    {
        return $this->hasMany(DelegateVisit::class);
    }
}
