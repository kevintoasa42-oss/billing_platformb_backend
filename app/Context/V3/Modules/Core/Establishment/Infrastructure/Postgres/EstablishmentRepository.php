<?php

namespace App\Context\V3\Modules\Core\Establishment\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Establishment\Domain\Models\Establishment;
use App\Context\V3\Modules\Core\Establishment\Domain\Repository\EstablishmentRepositoryInterface;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EstablishmentModel;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Mappers\EstablishmentMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EstablishmentRepository implements EstablishmentRepositoryInterface
{
    public function __construct(
        private readonly EstablishmentMapper $mapper,
    ) {}

    public function all(): array
    {
        $records = EstablishmentModel::query()
            ->with('activities')
            ->orderBy('name')
            ->get();

        return $this->mapper->toDomainList($records);
    }

    public function find(string $id): ?Establishment
    {
        $record = EstablishmentModel::query()
            ->with('activities')
            ->find($id);

        return $record !== null ? $this->mapper->toDomain($record) : null;
    }

    public function create(Establishment $establishment): Establishment
    {
        return DB::connection('master_v3')->transaction(function () use ($establishment): Establishment {
            $record = EstablishmentModel::query()->create(
                $this->mapper->toDatabaseArray($establishment)
            );

            $this->syncActivities($record, $establishment->activityIds);

            return $this->mapper->toDomain($record->load('activities'));
        });
    }

    public function update(string $id, Establishment $establishment): ?Establishment
    {
        return DB::connection('master_v3')->transaction(function () use ($id, $establishment): ?Establishment {
            $record = EstablishmentModel::query()->find($id);
            if ($record === null) {
                return null;
            }

            $record->update($this->mapper->toDatabaseArray($establishment));

            $this->syncActivities($record, $establishment->activityIds);

            return $this->mapper->toDomain($record->load('activities'));
        });
    }

    /**
     * @param  array<int, string>|null  $activityIds
     */
    private function syncActivities(EstablishmentModel $record, ?array $activityIds): void
    {
        if ($activityIds === null) {
            return;
        }

        $ids = array_values(array_unique(array_filter(array_map(
            static fn ($id): string => trim((string) $id), $activityIds
        ))));

        $connection = DB::connection('master_v3');

        $known = $connection->table('core.economic_activities')
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();

        if (count($known) !== count($ids)) {
            throw new \InvalidArgumentException('One or more economic activities do not exist.');
        }

        $table = $connection->table('core.establishment_activities');

        $current = $table
            ->where('tenant_id', $record->tenant_id)
            ->where('establishment_id', $record->id)
            ->whereRaw('validity @> CURRENT_DATE')
            ->lockForUpdate()
            ->get();

        $currentByActivity = $current->keyBy('activity_id');

        foreach ($current as $row) {
            if (! in_array((string) $row->activity_id, $ids, true)) {
                $table->where('tenant_id', $record->tenant_id)
                    ->where('id', $row->id)
                    ->update(['validity' => DB::raw("daterange(lower(validity), CURRENT_DATE, '[)')")]);
            }
        }

        foreach ($ids as $activityId) {
            if (isset($currentByActivity[$activityId])) {
                continue;
            }

            $table->insert([
                'id' => Str::uuid()->toString(),
                'tenant_id' => $record->tenant_id,
                'establishment_id' => $record->id,
                'activity_id' => $activityId,
                'validity' => DB::raw("daterange(CURRENT_DATE, NULL, '[)')"),
            ]);
        }
    }
}
