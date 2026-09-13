<?php

namespace App\Models;

use App\Context\V1\BranchOffices\Infrastructure\Laravel\Eloquent\Models\BranchOfficeModel;
use App\Context\V1\EmissionPoints\Infrastructure\Laravel\Eloquent\Models\EmissionPointModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceHeaderModel extends Model
{
    protected $connection = 'tenant';

    protected $table = 'invoice_headers';

    protected $fillable = [
        'carrier_id',
        'branch_office_id',
        'emission_point_id',
        'environment',
        'emission_type',
        'ruc',
        'legal_name',
        'tradename',
        'access_key',
        'document_code',
        'establishment',
        'emission_point',
        'sequential',
        'matrix_address',
        'issue_date',
        'establishment_address',
        'accounting_required',
        'buyer_identification_type',
        'buyer_name',
        'buyer_identification',
        'buyer_address',
        'buyer_phone',
        'buyer_email',
        'subtotal',
        'discount',
        'tax_base',
        'tax',
        'tip',
        'total',
        'currency',
        'plate',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_base' => 'decimal:2',
        'tax' => 'decimal:2',
        'tip' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(CarrierModel::class, 'carrier_id');
    }

    public function branchOffice(): BelongsTo
    {
        return $this->belongsTo(BranchOfficeModel::class, 'branch_office_id');
    }

    public function emissionPoint(): BelongsTo
    {
        return $this->belongsTo(EmissionPointModel::class, 'emission_point_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItemModel::class, 'invoice_header_id');
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(InvoiceTaxModel::class, 'invoice_header_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePaymentModel::class, 'invoice_header_id');
    }

    public function additionalInfo(): HasMany
    {
        return $this->hasMany(InvoiceAdditionalInfoModel::class, 'invoice_header_id');
    }
}
