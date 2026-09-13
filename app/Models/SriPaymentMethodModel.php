<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SriPaymentMethodModel extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'sri_payment_methods';

    protected $fillable = [
        'code',
        'name',
        'description',
    ];
}
