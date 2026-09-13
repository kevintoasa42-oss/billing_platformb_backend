<?php

namespace App\Context\V1\Modules\SriVoucherTypes\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Eloquent persistence model for the shared landlord SRI voucher catalogue. */
final class SriVoucherTypeModel extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql';

    protected $table = 'sri_vouchers_types';

    protected $fillable = [
        'document',
        'code',
        'sustentation_code',
        'start_date',
        'end_date',
        'retention',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'retention' => 'boolean',
        ];
    }
}
