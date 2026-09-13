<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Relacion con la tabla pivote product_tax (DB tenant).
     * No usa belongsToMany porque sri_iva_percentages esta en la DB central.
     */
    public function taxes(): HasMany
    {
        return $this->hasMany(ProductTaxModel::class, 'product_id');
    }

    /**
     * Devuelve los IDs de taxes asignados a este product.
     */
    public function getTaxIds(): array
    {
        return $this->taxes()->pluck('sri_iva_percentage_id')->toArray();
    }
}
