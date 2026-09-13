<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItemTaxModel extends Model
{
    protected $connection = 'tenant';

    protected $table = 'invoice_item_taxes';

    protected $fillable = [
        'invoice_item_id',
        'codigo',
        'codigo_porcentaje',
        'tarifa',
        'base_imponible',
        'valor',
    ];

    protected $casts = [
        'tarifa' => 'decimal:2',
        'base_imponible' => 'decimal:2',
        'valor' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InvoiceItemModel::class, 'invoice_item_id');
    }
}
