<?php

namespace App\Contexto\Enterprise\Infraestructura\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RolModel extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * Usuarios con este rol (pivot usuario_rol).
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(UsuarioModel::class, 'usuario_rol', 'rol_id', 'usuario_id')
            ->withTimestamps();
    }

    /**
     * Menús asignados a este rol (pivot menu_rol).
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Contexto\Menu\Infraestructura\Eloquent\Models\MenuModel::class,
            'menu_rol',
            'rol_id',
            'menu_id'
        )->withTimestamps();
    }
}
