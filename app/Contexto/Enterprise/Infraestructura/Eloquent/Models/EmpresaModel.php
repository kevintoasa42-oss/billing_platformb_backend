<?php

namespace App\Contexto\Enterprise\Infraestructura\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EmpresaModel extends Model
{
    protected $table = 'enterprises';

    protected $fillable = [
        'nombre',
        'ruc',
        'tradename',
        'matrixname',
        'telefono',
        'correo_corporativo',
        'db_name',
    ];

    /**
     * Usuarios asociados a esta empresa (pivot usuario_empresa).
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(UsuarioModel::class, 'usuario_empresa', 'enterprise_id', 'usuario_id')
            ->withTimestamps();
    }
}
