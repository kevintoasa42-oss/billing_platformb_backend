<?php

namespace App\Context\V3\Modules\Fiscal\Invoice\Domain\Repository;

use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\InvoiceDraft;

interface InvoiceDraftRepositoryInterface
{
    /**
     * @return array<int, InvoiceDraft>
     */
    public function all(string $userId): array;

    public function current(string $userId): ?InvoiceDraft;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function save(string $userId, array $payload, ?int $revision): InvoiceDraft;

    public function delete(string $userId, ?string $publicId, ?int $revision): void;
}
