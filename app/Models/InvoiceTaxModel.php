<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceTaxModel extends Model
{
    protected $connection = 'tenant';

    protected $table = 'invoice_taxes';

    protected $fillable = [
        'invoice_header_id',
        'sri_iva_percentage_id',
        'code',
        'percentage_code',
        'taxable_base',
        'value',
    ];

    protected $casts = [
        'taxable_base' => 'decimal:2',
        'value' => 'decimal:2',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(InvoiceHeaderModel::class, 'invoice_header_id');
    }
}
