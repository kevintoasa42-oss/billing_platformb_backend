<?php

namespace App\Context\Product\Infrastructure\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de referencia a la tabla sri_iva_percentages en la DB central.
 * Se usa para la relacion product_tax desde la DB tenant.
 */
class SriIvaPercentageRefModel extends Model
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
