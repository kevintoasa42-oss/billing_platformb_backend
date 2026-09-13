<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceItemModel extends Model
{
    protected $connection = 'tenant';

    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_header_id',
        'codigo_principal',
        'codigo_auxiliar',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'descuento',
        'precio_total_sin_impuesto',
    ];

    protected $casts = [
        'cantidad' => 'decimal:5',
        'precio_unitario' => 'decimal:5',
        'descuento' => 'decimal:2',
        'precio_total_sin_impuesto' => 'decimal:2',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(InvoiceHeaderModel::class, 'invoice_header_id');
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(InvoiceItemTaxModel::class, 'invoice_item_id');
    }
}
