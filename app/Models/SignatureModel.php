<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignatureModel extends Model
{
    protected $connection = 'tenant';

    protected $table = 'signatures';

    protected $fillable = [
        'carrier_id',
        'file_name',
        'file_path',
        'password',
        'expires_at',
        'environment',
        'emission_type',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'emission_type' => 'boolean',
        'status' => 'boolean',
    ];

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(CarrierModel::class, 'carrier_id');
    }
}
