<?php

namespace App\Context\V3\Modules\Core\Company\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Company\Domain\Models\Company;
use App\Context\V3\Modules\Core\Company\Domain\Repository\CompanyRepositoryInterface;
use App\Context\V3\Modules\Core\Company\Infrastructure\Laravel\Eloquent\Models\CompanyActivityModel;
use App\Context\V3\Modules\Core\Company\Infrastructure\Laravel\Eloquent\Models\CompanyModel;
use App\Context\V3\Modules\Core\Company\Infrastructure\Mappers\CompanyMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyRepository implements CompanyRepositoryInterface
{
    public function __construct(
        private readonly CompanyMapper $mapper,
    ) {}

    public function all(): array
    {
        $records = CompanyModel::query()
            ->with('activities')
            ->orderBy('name')
            ->get();

        return $this->mapper->toDomainList($records);
    }

    public function find(string $id): ?Company
    {
        $record = CompanyModel::query()
            ->with('activities')
            ->find($id);

        return $record !== null ? $this->mapper->toDomain($record) : null;
    }

    public function create(Company $company): Company
    {
        return DB::connection('master_v3')->transaction(function () use ($company): Company {
            $record = CompanyModel::query()->create(
                $this->mapper->toDatabaseArray($company)
            );

            $this->syncActivities($record, $company->activityIds);

            return $this->mapper->toDomain($record->load('activities'));
        });
    }

    public function update(string $id, Company $company): ?Company
    {
        return DB::connection('master_v3')->transaction(function () use ($id, $company): ?Company {
            $record = CompanyModel::query()->find($id);
            if ($record === null) {
                return null;
            }

            $record->update($this->mapper->toDatabaseArray($company));

            $this->syncActivities($record, $company->activityIds);

            return $this->mapper->toDomain($record->load('activities'));
        });
    }

    /**
     * Reconcile the current activity set without deleting its history.
     *
     * @param  array<int, string>|null  $activityIds
     */
    private function syncActivities(CompanyModel $record, ?array $activityIds): void
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

        $table = $connection->table('core.company_activities');

        $current = $table
            ->where('tenant_id', $record->tenant_id)
            ->whereRaw('validity @> CURRENT_DATE')
            ->lockForUpdate()
            ->get();

        $currentByActivity = $current->keyBy('activity_id');

        $table->where('tenant_id', $record->tenant_id)
            ->whereRaw('validity @> CURRENT_DATE')
            ->update(['is_primary' => false]);

        foreach ($current as $row) {
            if (! in_array((string) $row->activity_id, $ids, true)) {
                $table->where('tenant_id', $record->tenant_id)
                    ->where('id', $row->id)
                    ->update(['validity' => DB::raw("daterange(lower(validity), CURRENT_DATE, '[)')")]);
            }
        }

        foreach ($ids as $index => $activityId) {
            $values = ['is_primary' => $index === 0];
            if (isset($currentByActivity[$activityId])) {
                $table->where('tenant_id', $record->tenant_id)
                    ->where('id', $currentByActivity[$activityId]->id)
                    ->update($values);
                continue;
            }

            $table->insert([
                'id' => Str::uuid()->toString(),
                'tenant_id' => $record->tenant_id,
                'activity_id' => $activityId,
                'validity' => DB::raw("daterange(CURRENT_DATE, NULL, '[)')"),
                ...$values,
            ]);
        }
    }
}
