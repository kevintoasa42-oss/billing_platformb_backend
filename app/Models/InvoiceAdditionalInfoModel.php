<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceAdditionalInfoModel extends Model
{
    protected $connection = 'tenant';

    protected $table = 'invoice_additional_info';

    protected $fillable = [
        'invoice_header_id',
        'name',
        'value',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(InvoiceHeaderModel::class, 'invoice_header_id');
    }
}
