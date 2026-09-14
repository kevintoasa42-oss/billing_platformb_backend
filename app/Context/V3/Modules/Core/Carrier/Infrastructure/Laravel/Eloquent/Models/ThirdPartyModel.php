<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for core.third_parties (relation target only).
 */
class ThirdPartyModel extends Model
{
    protected $connection = 'master_v3';

    protected $table = 'core.third_parties';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'id', 'name', 'identification', 'identification_type',
        'person_type', 'must_invoice', 'legacy_id', 'address', 'phone',
        'email', 'customer_type_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'must_invoice' => 'boolean',
    ];
}
