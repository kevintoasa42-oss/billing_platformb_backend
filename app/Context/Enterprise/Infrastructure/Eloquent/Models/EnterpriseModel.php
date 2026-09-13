<?php

namespace App\Context\Enterprise\Infrastructure\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EnterpriseModel extends Model
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
     * Users asociados a esta enterprise (pivot user_enterprise).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(UserModel::class, 'user_enterprise', 'enterprise_id', 'user_id')
            ->withTimestamps();
    }
}
