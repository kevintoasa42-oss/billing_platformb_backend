<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\EconomicActivity\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class EconomicActivityModel extends Model
{
    protected $connection = 'master_v3';

    protected $table = 'core.economic_activities';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id', 'name', 'catalog_version',
    ];
}
