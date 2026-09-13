<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePaymentModel extends Model
{
    protected $connection = 'tenant';

    protected $table = 'invoice_payments';

    protected $fillable = [
        'invoice_header_id',
        'sri_payment_method_id',
        'payment_code',
        'total',
        'term',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(InvoiceHeaderModel::class, 'invoice_header_id');
    }
}
