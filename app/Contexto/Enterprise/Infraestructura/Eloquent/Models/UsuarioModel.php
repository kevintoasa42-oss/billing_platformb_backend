<?php

namespace App\Contexto\Enterprise\Infraestructura\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UsuarioModel extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
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
     * Roles del usuario (pivot usuario_rol).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RolModel::class, 'usuario_rol', 'usuario_id', 'rol_id')
            ->withTimestamps();
    }

    /**
     * Empresas a las que pertenece el usuario (pivot usuario_empresa).
     */
    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(EmpresaModel::class, 'usuario_empresa', 'usuario_id', 'enterprise_id')
            ->withTimestamps();
    }
}
