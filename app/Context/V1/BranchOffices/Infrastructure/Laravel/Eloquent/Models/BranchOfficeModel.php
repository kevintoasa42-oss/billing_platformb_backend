<?php

namespace App\Context\V1\BranchOffices\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V1\EmissionPoints\Infrastructure\Laravel\Eloquent\Models\EmissionPointModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class BranchOfficeModel extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'branch_offices';

    protected $fillable = ['name', 'code_sri', 'status', 'type', 'default'];

    protected function casts(): array
    {
        return ['status' => 'boolean', 'default' => 'boolean'];
    }

    public function emissionPoints()
    {
        return $this->hasMany(EmissionPointModel::class, 'branch_office_id');
    }
}
