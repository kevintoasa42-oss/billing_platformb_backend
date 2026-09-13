<?php

namespace App\Context\V1\Product\Infrastructure\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent para la tabla pivote product_tax (DB tenant).
 * No usa belongsToMany con sri_iva_percentages porque esa tabla esta en la DB central.
 */
class ProductTaxModel extends Model
{
    protected $connection = 'tenant';

    protected $table = 'product_tax';

    protected $fillable = [
        'product_id',
        'sri_iva_percentage_id',
    ];

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }
}
