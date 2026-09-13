<?php

namespace App\Context\V1\Enterprise\Infrastructure\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RoleModel extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Users con este rol (pivot user_role).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(UserModel::class, 'user_role', 'role_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Menús asignados a este rol (pivot menu_role).
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Context\V1\Menu\Infrastructure\Eloquent\Models\MenuModel::class,
            'menu_role',
            'role_id',
            'menu_id'
        )->withTimestamps();
    }
}
