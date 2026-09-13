<?php

namespace App\Context\V1\EmissionPoints\Infrastructure\Laravel\Eloquent\Models;

use App\Models\CarrierModel;
use Illuminate\Database\Eloquent\Model;

/** Persistence counter; it is not a domain entity. */
final class EmissionPointSequenceModel extends Model
{
    protected $connection = 'tenant';

    protected $table = 'emission_point_sequences';

    protected $fillable = [
        'branch_office_id',
        'emission_point_id',
        'carrier_id',
        'document_code',
        'document_label',
        'next_sequential',
    ];

    protected function casts(): array
    {
        return ['next_sequential' => 'integer'];
    }

    public function carrier()
    {
        return $this->belongsTo(CarrierModel::class, 'carrier_id');
    }
}
