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
        'sri_iva_percentage_id',
        'code',
        'percentage_code',
        'rate',
        'tax_base',
        'tax',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'tax_base' => 'decimal:2',
        'tax' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InvoiceItemModel::class, 'invoice_item_id');
    }
}
