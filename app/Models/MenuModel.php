<?php

namespace App\Models;

use App\Models\RoleModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuModel extends Model
{
    protected $table = 'menus';

    protected $fillable = [
        'name',
        'route',
        'icon',
        'parent_id',
        'order',
    ];

    /**
     * Menu padre (jerarquia).
     */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(MenuModel::class, 'parent_id');
    }

    /**
     * Submenus hijos.
     */
    public function hijos(): HasMany
    {
        return $this->hasMany(MenuModel::class, 'parent_id')->orderBy('order');
    }

    /**
     * Roles asignados a este menu (pivot menu_role).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RoleModel::class, 'menu_role', 'menu_id', 'role_id')
            ->withTimestamps();
    }
}
