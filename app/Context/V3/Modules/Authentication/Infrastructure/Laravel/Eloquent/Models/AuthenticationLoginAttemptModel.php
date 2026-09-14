<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/** Eloquent model for the global auth.login_attempts throttle table. */
final class AuthenticationLoginAttemptModel extends Model
{
    protected $connection = 'master_v3';

    protected $table = 'auth.login_attempts';

    public $incrementing = false;

    public $timestamps = false;
}
