<?php

namespace App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentLineModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'fiscal.document_lines';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id', 'document_id', 'product_id', 'description_snapshot',
        'quantity', 'unit_price', 'subtotal', 'original_line_id',
        'discount', 'tax', 'total', 'taxable_base', 'sri_principal_code',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(DocumentModel::class, 'document_id', 'id');
    }
}
