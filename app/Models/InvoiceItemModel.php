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
        'product_id',
        'main_code',
        'auxiliary_code',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'tax_base',
    ];

    protected $casts = [
        'quantity' => 'decimal:5',
        'unit_price' => 'decimal:5',
        'discount' => 'decimal:2',
        'tax_base' => 'decimal:2',
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
