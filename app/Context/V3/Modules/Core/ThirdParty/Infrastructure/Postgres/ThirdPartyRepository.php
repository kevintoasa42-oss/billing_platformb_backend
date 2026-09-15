<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdParty;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyQueryRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\ValueObjects\CanonicalIdentification;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyModel;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Mappers\ThirdPartyMapper;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ThirdPartyRepository implements ThirdPartyRepositoryInterface, ThirdPartyQueryRepositoryInterface
{
    public function __construct(
        private readonly ThirdPartyMapper $mapper,
    ) {}

    /** @return array<int, ThirdParty> */
    public function all(): array
    {
        $records = ThirdPartyModel::query()->with(['roles', 'activities'])->orderBy('name')->get();

        return $this->mapper->toDomainList($records);
    }

    public function find(string $id): ?ThirdParty
    {
        $record = ThirdPartyModel::query()->with(['roles', 'activities'])->find($id);

        return $record !== null ? $this->mapper->toDomain($record) : null;
    }

    public function identificationExists(string $identification, string $identificationType): bool
    {
        $canonical = CanonicalIdentification::normalize($identification);
        if ($canonical === '') {
            return false;
        }

        $query = ThirdPartyModel::query()
            ->whereRaw("upper(regexp_replace(trim(identification), '\\s+', '', 'g')) = ?", [$canonical])
            ->where('identification_type', CanonicalIdentification::normalizeType($identificationType, $canonical));

        return $query->exists();
    }

    public function findByIdentification(string $identification, ?string $identificationType = null): ?ThirdParty
    {
        $canonical = CanonicalIdentification::normalize($identification);
        if ($canonical === '') {
            return null;
        }

        $query = ThirdPartyModel::query()->with(['roles', 'activities'])
            ->whereRaw("upper(regexp_replace(trim(identification), '\\s+', '', 'g')) = ?", [$canonical]);
        if ($identificationType !== null && trim($identificationType) !== '') {
            $query->where('identification_type', CanonicalIdentification::normalizeType($identificationType, $canonical));
        }

        $record = $query->first();

        return $record === null ? null : $this->mapper->toDomain($record);
    }

    public function create(ThirdParty $thirdParty): ThirdParty
    {
        try {
            return DB::connection('master_v3')->transaction(function () use ($thirdParty): ThirdParty {
                $record = ThirdPartyModel::query()->create(
                    [...$this->mapper->toDatabaseArray($thirdParty), 'tenant_id' => $thirdParty->tenantId, 'id' => $thirdParty->id ?? Str::uuid()->toString()],
                );

                if ($thirdParty->roles !== null) {
                    $record->roles()->createMany($this->mapRoles((string) $record->tenant_id, $thirdParty->roles));
                }

                if ($thirdParty->activities !== null) {
                    $record->activities()->createMany($this->mapActivities($thirdParty->activities));
                }

                return $this->mapper->toDomain($record->load(['roles', 'activities']));
            });
        } catch (QueryException $exception) {
            $this->throwIfIdentificationUniquenessViolation($exception);
            throw $exception;
        }
    }

    public function update(string $id, ThirdParty $thirdParty): ?ThirdParty
    {
        $record = ThirdPartyModel::query()->find($id);

        if ($record === null) {
            return null;
        }

        try {
            return DB::connection('master_v3')->transaction(function () use ($record, $thirdParty): ThirdParty {
                $record->update($this->mapper->toDatabaseArray($thirdParty));

                if ($thirdParty->roles !== null) {
                    $record->roles()->delete();
                    $record->roles()->createMany($this->mapRoles((string) $record->tenant_id, $thirdParty->roles));
                }

                if ($thirdParty->activities !== null) {
                    $record->activities()->delete();
                    $record->activities()->createMany($this->mapActivities($thirdParty->activities));
                }

                return $this->mapper->toDomain($record->load(['roles', 'activities']));
            });
        } catch (QueryException $exception) {
            $this->throwIfIdentificationUniquenessViolation($exception);
            throw $exception;
        }
    }

    public function findByRole(string $role): array
    {
        $records = ThirdPartyModel::query()->with(['roles', 'activities'])
            ->whereHas('roles', fn ($q) => $q->where('role', $role))
            ->orderBy('name')
            ->get();

        return $this->mapper->toDomainList($records);
    }

    public function findCarriers(): array
    {
        return $this->findByRole('carrier');
    }

    private function throwIfIdentificationUniquenessViolation(QueryException $exception): void
    {
        $message = strtolower($exception->getMessage());
        $isUniqueViolation = $exception->getCode() === '23505'
            || str_contains($message, 'duplicate key value violates unique constraint');
        if ($isUniqueViolation && str_contains($message, 'third_part')) {
            throw new ConflictHttpException('Ya existe un cliente con ese número de identificación.', $exception);
        }
    }

    /**
     * @param  array<int, string>  $roles
     * @return array<int, array<string, mixed>>
     */
    private function mapRoles(string $tenantId, array $roles): array
    {
        return array_map(fn (string $role) => [
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'role' => $role,
        ], $roles);
    }

    /**
     * @param  array<int, array<string, mixed>>  $activities
     * @return array<int, array<string, mixed>>
     */
    private function mapActivities(array $activities): array
    {
        return array_map(fn (array $a) => [
            'id' => Str::uuid()->toString(),
            'establishment_code' => $a['establishment_code'] ?? null,
            'activity_id' => $a['activity_id'] ?? null,
            'validity' => $a['validity'] ?? null,
        ], $activities);
    }
}
