<?php

namespace App\Context\V1\Product\Infrastructure\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    protected $connection = 'tenant';

    protected $table = 'products';

    protected $fillable = [
        'barcode',
        'auxiliary_code',
        'name',
        'description',
        'status',
        'base_price',
    ];

    protected $casts = [
        'status' => 'boolean',
        'base_price' => 'decimal:2',
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
