<?php

namespace App\Contexto\Product\Infraestructura\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de referencia a la tabla sri_iva_percentages en la DB central.
 * Se usa para la relacion producto_impuesto desde la DB tenant.
 */
class SriIvaPercentageRefModel extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'sri_iva_percentages';

    protected $fillable = [
        'codigo',
        'nombre',
        'porcentaje',
        'descripcion',
    ];

    protected $casts = [
        'porcentaje' => 'decimal:2',
    ];
}
