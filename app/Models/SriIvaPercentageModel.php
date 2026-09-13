<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SriIvaPercentageModel extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'sri_iva_percentages';

    protected $fillable = [
        'code',
        'name',
        'percentage',
        'description',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
    ];
}
