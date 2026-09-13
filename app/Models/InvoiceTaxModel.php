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
        'codigo',
        'codigo_porcentaje',
        'base_imponible',
        'valor',
    ];

    protected $casts = [
        'base_imponible' => 'decimal:2',
        'valor' => 'decimal:2',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(InvoiceHeaderModel::class, 'invoice_header_id');
    }
}
