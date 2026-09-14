<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\UseCases;

use App\Context\V3\Modules\Core\Establishment\Application\DTOs\BranchCreateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\DTOs\BranchUpdateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\DTOs\EstablishmentCreateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\DTOs\EstablishmentUpdateDTO;
use App\Context\V3\Modules\Core\Establishment\Domain\Models\Establishment;
use App\Context\V3\Modules\Core\Establishment\Domain\Repository\EmissionPointRepositoryInterface;
use App\Context\V3\Modules\Core\Establishment\Domain\Repository\EstablishmentRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EstablishmentUseCase
{
    public function __construct(
        private readonly EstablishmentRepositoryInterface $repository,
        private readonly EmissionPointRepositoryInterface $pointRepository,
    ) {}

    public function all(): array
    {
        $establishments = $this->repository->all();

        return array_map(fn (Establishment $e) => $e->toLegacyArray(), $establishments);
    }

    public function find(string $id): ?array
    {
        $establishment = $this->repository->find($id);

        return $establishment?->toLegacyArray();
    }

    public function create(EstablishmentCreateDTO $dto): array
    {
        $establishment = Establishment::fromArray($dto->toArray());

        $created = $this->repository->create($establishment);

        return $created->toLegacyArray();
    }

    public function update(string $id, EstablishmentUpdateDTO $dto): ?array
    {
        $existing = $this->repository->find($id);

        if ($existing === null) {
            return null;
        }

        $merged = array_merge($existing->toLegacyArray(), $dto->toArray());

        $establishment = Establishment::fromArray($merged);

        $updated = $this->repository->update($id, $establishment);

        return $updated?->toLegacyArray();
    }

    /**
     * Branch surface — list branches with nested issuance points.
     */
    public function allBranches(): array
    {
        $branches = $this->repository->allBranches();

        return array_map(fn (Establishment $e) => $e->toArray(), $branches);
    }

    public function createBranch(BranchCreateDTO $dto): ?array
    {
        return DB::connection('master_v3')->transaction(function () use ($dto): ?array {
            $sriCode = $dto->sriCode();

            if (DB::connection('master_v3')->table('core.establishments')->where('sri_code', $sriCode)->exists()) {
                throw new \DomainException('El código de establecimiento ya está registrado.');
            }

            $tenantId = request()->attributes->get('v3.tenant_id')
                ?? DB::connection('master_v3')->selectOne("SELECT current_setting('app.tenant_id', true) AS tenant_id")?->tenant_id;

            $company = DB::connection('master_v3')
                ->table('core.companies')
                ->first();

            if ($company === null) {
                return null;
            }

            $id = Str::uuid()->toString();
            DB::connection('master_v3')->table('core.establishments')->insert([
                'tenant_id' => $tenantId,
                'id' => $id,
                'company_id' => $company->id,
                'sri_code' => $sriCode,
                'branch_code' => $dto->branchCode ?? $sriCode,
                'name' => trim($dto->name ?? 'Sucursal'),
                'address' => $dto->address,
                'phone' => $dto->phone,
                'email' => $dto->email,
                'city_id' => $dto->cityId,
                'is_active' => true,
            ]);

            if ($dto->issuancePoint !== null && $dto->issuancePoint !== []) {
                $pointCode = $this->code((string) ($dto->issuancePoint['issuance_point_number'] ?? '001'));
                DB::connection('master_v3')->table('core.emission_points')->insert([
                    'tenant_id' => $tenantId,
                    'id' => Str::uuid()->toString(),
                    'establishment_id' => $id,
                    'sri_code' => $pointCode,
                    'name' => $dto->issuancePoint['name'] ?? null,
                    'is_active' => (bool) ($dto->issuancePoint['is_active'] ?? true),
                    'is_default' => (bool) ($dto->issuancePoint['is_default'] ?? false),
                    'has_tax_validity' => (bool) ($dto->issuancePoint['has_tax_validity'] ?? true),
                ]);
            }

            return $this->repository->findByLegacyId(
                (int) DB::connection('master_v3')->table('core.establishments')->where('id', $id)->value('legacy_id')
            )?->toArray();
        });
    }

    public function updateBranch(int $legacyId, BranchUpdateDTO $dto): ?array
    {
        return DB::connection('master_v3')->transaction(function () use ($legacyId, $dto): ?array {
            $branch = DB::connection('master_v3')
                ->table('core.establishments')
                ->where('legacy_id', $legacyId)
                ->first();

            if ($branch === null) {
                return null;
            }

            $values = array_filter([
                'name' => $dto->name,
                'branch_code' => $dto->branchCode,
                'address' => $dto->address,
                'phone' => $dto->phone,
                'email' => $dto->email,
                'city_id' => $dto->cityId,
                'is_active' => $dto->isActive,
            ], fn ($value): bool => $value !== null);

            if ($values !== []) {
                DB::connection('master_v3')->table('core.establishments')->where('id', $branch->id)->update($values);
            }

            if ($dto->issuancePoint !== null) {
                $point = DB::connection('master_v3')
                    ->table('core.emission_points')
                    ->where('establishment_id', $branch->id)
                    ->orderBy('legacy_id')
                    ->first();

                if ($point !== null) {
                    DB::connection('master_v3')->table('core.emission_points')->where('id', $point->id)->update($dto->issuancePoint);
                }
            }

            return $this->repository->findByLegacyId($legacyId)?->toArray();
        });
    }

    public function deleteBranch(int $legacyId): bool
    {
        return $this->repository->deleteByLegacyId($legacyId);
    }

    private function code(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?: '001';

        return str_pad(substr($digits, -3), 3, '0', STR_PAD_LEFT);
    }
}
