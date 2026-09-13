<?php

namespace App\Context\V1\EmissionPoints\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V1\BranchOffices\Infrastructure\Laravel\Eloquent\Models\BranchOfficeModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class EmissionPointModel extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $table = 'emission_points';

    protected $fillable = ['branch_office_id', 'name', 'emission_point', 'status', 'default'];

    protected function casts(): array
    {
        return ['status' => 'boolean', 'default' => 'boolean'];
    }

    public function branchOffice()
    {
        return $this->belongsTo(BranchOfficeModel::class, 'branch_office_id');
    }

    public function sequence()
    {
        return $this->hasOne(EmissionPointSequenceModel::class, 'emission_point_id');
    }
}
