<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarrierModel extends Model
{
    protected $connection = 'tenant';

    protected $table = 'carriers';

    protected $fillable = [
        'ruc',
        'plate',
        'name',
        'tradename',
        'matrix_address',
        'special_taxpayer',
        'accounting_required',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'accounting_required' => 'boolean',
    ];
}
