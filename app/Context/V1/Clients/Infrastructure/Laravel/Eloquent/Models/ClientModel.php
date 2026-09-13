<?php

namespace App\Context\V1\Clients\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ClientModel extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'clients';

    protected $fillable = [
        'identification_type', 'identification_number', 'name', 'last_name',
        'status', 'address', 'phone', 'email', 'type', 'plates',
    ];
}
