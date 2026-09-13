<?php

namespace App\Context\Enterprise\Infrastructure\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UserModel extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Roles del user (pivot user_role).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RoleModel::class, 'user_role', 'user_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * Enterprises a las que pertenece el user (pivot user_enterprise).
     */
    public function enterprises(): BelongsToMany
    {
        return $this->belongsToMany(EnterpriseModel::class, 'user_enterprise', 'user_id', 'enterprise_id')
            ->withTimestamps();
    }
}
