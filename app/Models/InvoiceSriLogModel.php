<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceSriLogModel extends Model
{
    protected $connection = 'tenant';

    protected $table = 'invoice_sri_logs';

    protected $fillable = [
        'invoice_header_id',
        'access_key',
        'operation_type',
        'status',
        'sri_state',
        'response_message',
        'raw_response',
        'environment',
        'authorization_date',
    ];

    protected $casts = [
        'status' => 'boolean',
        'raw_response' => 'array',
        'authorization_date' => 'datetime',
    ];

    public function invoiceHeader(): BelongsTo
    {
        return $this->belongsTo(InvoiceHeaderModel::class, 'invoice_header_id');
    }
}
