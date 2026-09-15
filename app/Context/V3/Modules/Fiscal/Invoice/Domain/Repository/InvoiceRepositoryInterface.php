<?php

namespace App\Context\V3\Modules\Fiscal\Invoice\Domain\Repository;

use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\Invoice;
use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\InvoiceReadiness;

interface InvoiceRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{items: array<int, Invoice>, meta: array<string, mixed>}
     */
    public function all(array $filters = []): array;

    public function findByLegacyId(int $legacyId): ?Invoice;

    /**
     * @param  array<string, mixed>  $input
     */
    public function create(array $input, string $idempotencyKey, string $actorId): Invoice;

    public function authorize(int $legacyId): ?Invoice;

    public function void(int $legacyId, string $reasonCode, string $reasonNote): ?Invoice;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function summary(array $filters = []): array;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters = []): string;

    public function readiness(int $branchId, int $issuancePointId): InvoiceReadiness;

    public function artifact(int $legacyId, string $kind): ?string;

    /** @return array<string, mixed> */
    public function editorContext(string $tenantId): array;
}
