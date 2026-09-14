<?php

namespace App\Context\V3\Modules\Core\EconomicActivity\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\EconomicActivity\Domain\Mappers\EconomicActivityMapperInterface;
use App\Context\V3\Modules\Core\EconomicActivity\Domain\Models\EconomicActivity;
use App\Context\V3\Modules\Core\EconomicActivity\Infrastructure\Laravel\Eloquent\Models\EconomicActivityModel;

class EconomicActivityMapper implements EconomicActivityMapperInterface
{
    public function toDomain(EconomicActivityModel $record): EconomicActivity
    {
        return EconomicActivity::fromArray([
            'id' => $record->id,
            'name' => $record->name,
            'catalog_version' => $record->catalog_version,
        ]);
    }

    public function toDomainList(iterable $records): array
    {
        $list = [];
        foreach ($records as $record) {
            $list[] = $this->toDomain($record);
        }

        return $list;
    }

    public function toDatabaseArray(EconomicActivity $activity): array
    {
        $data = [
            'name' => $activity->name,
            'catalog_version' => $activity->catalogVersion,
        ];

        if ($activity->id !== null) {
            $data['id'] = $activity->id;
        }

        return $data;
    }
}
