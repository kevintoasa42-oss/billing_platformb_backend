<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Application\UseCases;

use App\Context\V3\Modules\Fiscal\Invoice\Application\DTOs\InvoiceCreateDTO;
use App\Context\V3\Modules\Fiscal\Invoice\Application\DTOs\InvoiceQueryDTO;
use App\Context\V3\Modules\Fiscal\Invoice\Application\DTOs\InvoiceVoidDTO;
use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\Invoice;
use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\InvoiceReadiness;
use App\Context\V3\Modules\Fiscal\Invoice\Domain\Repository\InvoiceRepositoryInterface;
use Illuminate\Support\Str;

final class InvoiceUseCase
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $repository,
    ) {}

    /**
     * @return array{items: array<int, Invoice>, meta: array<string, mixed>}
     */
    public function list(InvoiceQueryDTO $query): array
    {
        return $this->repository->all($query->filters);
    }

    public function show(int $legacyId): ?Invoice
    {
        return $this->repository->findByLegacyId($legacyId);
    }

    public function create(InvoiceCreateDTO $dto, string $actorId): Invoice
    {
        $idempotencyKey = $dto->idempotencyKey ?? Str::uuid()->toString();

        return $this->repository->create([
            'emission_point_id' => $dto->emissionPointId,
            'establishment_code' => $dto->establishmentCode,
            'emission_point_code' => $dto->emissionPointCode,
            'recipient_snapshot' => $dto->recipient,
            'issuer_snapshot' => $dto->issuer,
            'lines' => $dto->lines,
            'payments' => $dto->payments,
            'subtotal' => $dto->subtotal,
            'tax' => $dto->tax,
            'total' => $dto->total,
            'discount' => $dto->discount ?? 0,
            'due_at' => $dto->dueAt,
        ], $idempotencyKey, $actorId);
    }

    public function authorize(int $legacyId): ?Invoice
    {
        return $this->repository->authorize($legacyId);
    }

    public function void(InvoiceVoidDTO $dto): ?Invoice
    {
        return $this->repository->void($dto->legacyId, $dto->reasonCode, $dto->reasonNote);
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return $this->repository->summary([]);
    }

    public function export(): string
    {
        return $this->repository->export([]);
    }

    public function readiness(int $branchId, int $issuancePointId): InvoiceReadiness
    {
        return $this->repository->readiness($branchId, $issuancePointId);
    }

    public function artifact(int $legacyId, string $kind): ?string
    {
        return $this->repository->artifact($legacyId, $kind);
    }

    /** @return array<string, mixed> */
    public function editorContext(string $tenantId): array
    {
        return $this->repository->editorContext($tenantId);
    }
}
