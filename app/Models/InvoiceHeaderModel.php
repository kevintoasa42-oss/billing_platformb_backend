<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceHeaderModel extends Model
{
    protected $connection = 'tenant';

    protected $table = 'invoice_headers';

    protected $fillable = [
        'carrier_id',
        'ambiente',
        'tipo_emision',
        'ruc',
        'razon_social',
        'nombre_comercial',
        'clave_acceso',
        'cod_doc',
        'estab',
        'pto_emi',
        'secuencial',
        'dir_matriz',
        'fecha_emision',
        'dir_establecimiento',
        'obligado_contabilidad',
        'tipo_identificacion_comprador',
        'razon_social_comprador',
        'identificacion_comprador',
        'direccion_comprador',
        'total_sin_impuestos',
        'total_descuento',
        'propina',
        'importe_total',
        'moneda',
        'placa',
        'status',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'total_sin_impuestos' => 'decimal:2',
        'total_descuento' => 'decimal:2',
        'propina' => 'decimal:2',
        'importe_total' => 'decimal:2',
    ];

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(CarrierModel::class, 'carrier_id');
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
