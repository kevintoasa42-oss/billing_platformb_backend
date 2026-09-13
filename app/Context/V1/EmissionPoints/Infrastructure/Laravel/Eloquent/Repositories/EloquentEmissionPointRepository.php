<?php

namespace App\Context\V1\EmissionPoints\Infrastructure\Laravel\Eloquent\Repositories;

use App\Context\V1\EmissionPoints\Domain\Exceptions\EmissionPointNotFoundException;
use App\Context\V1\EmissionPoints\Domain\Mappers\EmissionPointMapperInterface;
use App\Context\V1\EmissionPoints\Domain\Models\EmissionPoint;
use App\Context\V1\EmissionPoints\Domain\Models\EmissionPointSequential;
use App\Context\V1\EmissionPoints\Domain\Ports\NextSequentialGeneratorInterface;
use App\Context\V1\EmissionPoints\Domain\Repositories\EmissionPointRepositoryInterface;
use App\Context\V1\EmissionPoints\Infrastructure\Laravel\Eloquent\Models\EmissionPointModel;
use App\Context\V1\EmissionPoints\Infrastructure\Laravel\Eloquent\Models\EmissionPointSequenceModel;
use Illuminate\Support\Facades\DB;

final class EloquentEmissionPointRepository implements EmissionPointRepositoryInterface, NextSequentialGeneratorInterface
{
    public function __construct(private readonly EmissionPointMapperInterface $mapper) {}

    public function listPaginated(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $query = EmissionPointModel::query();
        if (! empty($filters['search'])) {
            $value = '%'.$filters['search'].'%';
            $query->where(fn ($q) => $q->where('name', 'like', $value)->orWhere('emission_point', 'like', $value));
        }
        if (! empty($filters['branch_office_id'])) {
            $query->where('branch_office_id', (int) $filters['branch_office_id']);
        }
        foreach (['status', 'default'] as $field) {
            if (array_key_exists($field, $filters) && $filters[$field] !== null) {
                $query->where($field, (bool) $filters[$field]);
            }
        }
        $paginator = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->getCollection()->map(fn (EmissionPointModel $model) => $this->mapper->toDomain($model->toArray()))->all(),
            'total' => $paginator->total(), 'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(), 'lastPage' => $paginator->lastPage(),
        ];
    }

    public function findById(int $id): ?EmissionPoint
    {
        $model = EmissionPointModel::find($id);

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function delete(int $id): bool
    {
        return EmissionPointModel::find($id)?->delete() ?? false;
    }

    public function nextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null, ?int $carrierId = null, string $documentCode = '01', string $documentLabel = 'Factura'): EmissionPointSequential
    {
        $query = EmissionPointModel::query()
            ->with('branchOffice:id,code_sri')
            ->where('branch_office_id', $branchOfficeId);
        $emissionPointId !== null
            ? $query->whereKey($emissionPointId)
            : $query->where('emission_point', $emissionPoint);

        $point = $query->first();
        if (! $point || ! $point->branchOffice) {
            throw new EmissionPointNotFoundException($branchOfficeId, $emissionPointId, $emissionPoint);
        }

        $counterQuery = EmissionPointSequenceModel::query()->where('emission_point_id', $point->id);
        $carrierId === null
            ? $counterQuery->whereNull('carrier_id')
            : $counterQuery->where('carrier_id', $carrierId);
        $counterQuery->where('document_code', $documentCode);
        $sequential = $counterQuery->value('next_sequential');

        return new EmissionPointSequential(
            branch_office_code_sri: (string) $point->branchOffice->code_sri,
            emission_point: (string) $point->emission_point,
            sequential: $sequential === null ? 1 : (int) $sequential,
        );
    }

    public function takeNextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null, ?int $carrierId = null, string $documentCode = '01', string $documentLabel = 'Factura'): EmissionPointSequential
    {
        return DB::connection('tenant')->transaction(function () use ($branchOfficeId, $emissionPointId, $emissionPoint, $carrierId, $documentCode, $documentLabel): EmissionPointSequential {
            $query = EmissionPointModel::query()
                ->with('branchOffice:id,code_sri')
                ->where('branch_office_id', $branchOfficeId);
            $emissionPointId !== null
                ? $query->whereKey($emissionPointId)
                : $query->where('emission_point', $emissionPoint);

            $point = $query->lockForUpdate()->first();
            if (! $point || ! $point->branchOffice) {
                throw new EmissionPointNotFoundException($branchOfficeId, $emissionPointId, $emissionPoint);
            }

            // The emission-point lock makes first creation and increment atomic.
            $counterQuery = EmissionPointSequenceModel::query()
                ->where('emission_point_id', $point->id);
            $carrierId === null
                ? $counterQuery->whereNull('carrier_id')
                : $counterQuery->where('carrier_id', $carrierId);
            $counterQuery->where('document_code', $documentCode);
            $counter = $counterQuery->lockForUpdate()->first();
            if (! $counter) {
                $counter = EmissionPointSequenceModel::create([
                    'branch_office_id' => $branchOfficeId,
                    'emission_point_id' => $point->id,
                    'carrier_id' => $carrierId,
                    'document_code' => $documentCode,
                    'document_label' => $documentLabel,
                    'next_sequential' => 1,
                ]);
            }

            $sequential = (int) $counter->next_sequential;
            $counter->update(['next_sequential' => $sequential + 1]);

            return new EmissionPointSequential(
                branch_office_code_sri: (string) $point->branchOffice->code_sri,
                emission_point: (string) $point->emission_point,
                sequential: $sequential,
            );
        });
    }

    public function create(EmissionPoint $emissionPoint): EmissionPoint
    {
        return DB::connection('tenant')->transaction(function () use ($emissionPoint): EmissionPoint {
            $model = EmissionPointModel::create($this->mapper->toPersistence($emissionPoint));
            EmissionPointSequenceModel::create([
                'branch_office_id' => $model->branch_office_id,
                'emission_point_id' => $model->id,
                'document_code' => '01',
                'document_label' => 'Factura',
                'next_sequential' => 1,
            ]);

            return $this->mapper->toDomain($model->fresh()->toArray());
        });
    }

    public function update(EmissionPoint $emissionPoint): EmissionPoint
    {
        return DB::connection('tenant')->transaction(function () use ($emissionPoint): EmissionPoint {
            $model = EmissionPointModel::query()->lockForUpdate()->findOrFail($emissionPoint->id);
            $model->update($this->mapper->toPersistence($emissionPoint));

            // Keep the counter scoped to the same branch if the point is moved.
            EmissionPointSequenceModel::where('emission_point_id', $model->id)
                ->update(['branch_office_id' => $model->branch_office_id]);

            return $this->mapper->toDomain($model->fresh()->toArray());
        });
    }
}
