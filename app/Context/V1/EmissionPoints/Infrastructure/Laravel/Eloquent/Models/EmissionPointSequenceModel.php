<?php

namespace App\Context\V1\EmissionPoints\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V1\Partners\Infrastructure\Laravel\Eloquent\Models\PartnerModel;
use Illuminate\Database\Eloquent\Model;

/** Persistence counter; it is not a domain entity. */
final class EmissionPointSequenceModel extends Model
{
    protected $connection = 'tenant';

    protected $table = 'emission_point_sequences';

    protected $fillable = ['branch_office_id', 'emission_point_id', 'partner_id', 'next_sequential'];

    protected function casts(): array
    {
        return ['next_sequential' => 'integer'];
    }

    public function partner()
    {
        return $this->belongsTo(PartnerModel::class, 'partner_id');
    }
}
