<?php

namespace App\Context\Product\Infrastructure\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    protected $connection = 'tenant';

    protected $table = 'products';

    protected $fillable = [
        'codigo_barras',
        'codigo_auxiliar',
        'nombre',
        'descripcion',
        'estado',
        'precio_base',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'precio_base' => 'decimal:2',
    ];

    /**
     * Devuelve los IDs de impuestos asignados a este product.
     * No usa belongsToMany porque la tabla sri_iva_percentages esta en la DB central.
     *
     * @return int[]
     */
    public function getImpuestoIds(): array
    {
        return \DB::connection('tenant')
            ->table('product_tax')
            ->where('product_id', $this->id)
            ->pluck('sri_iva_percentage_id')
            ->toArray();
    }
}
