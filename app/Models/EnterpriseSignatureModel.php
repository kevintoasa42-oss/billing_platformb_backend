<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnterpriseSignatureModel extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'enterprise_signatures';

    protected $fillable = [
        'enterprise_id',
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

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(EnterpriseModel::class, 'enterprise_id');
    }
}
