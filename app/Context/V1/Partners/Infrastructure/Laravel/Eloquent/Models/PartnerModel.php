<?php

namespace App\Context\V1\Partners\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class PartnerModel extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'partners';

    protected $fillable = [
        'identification_type',
        'identification_number',
        'name',
        'last_name',
        'email',
        'phone',
        'address',
        'status',
    ];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }
}
