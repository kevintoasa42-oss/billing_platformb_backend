<?php

namespace App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentPaymentModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'fiscal.document_payments';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id', 'legacy_id', 'document_id', 'position',
        'payment_method_code', 'total', 'term', 'time_unit', 'due_date',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'position' => 'integer',
        'term' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(DocumentModel::class, 'document_id', 'id');
    }
}
