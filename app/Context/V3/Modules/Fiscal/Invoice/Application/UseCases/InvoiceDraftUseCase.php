<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Application\UseCases;

use App\Context\V3\Modules\Fiscal\Invoice\Application\DTOs\InvoiceDraftSaveDTO;
use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\InvoiceDraft;
use App\Context\V3\Modules\Fiscal\Invoice\Domain\Repository\InvoiceDraftRepositoryInterface;

final class InvoiceDraftUseCase
{
    public function __construct(
        private readonly InvoiceDraftRepositoryInterface $repository,
    ) {}

    /**
     * @return array<int, InvoiceDraft>
     */
    public function list(string $userId): array
    {
        return $this->repository->all($userId);
    }

    public function current(string $userId): ?InvoiceDraft
    {
        return $this->repository->current($userId);
    }

    public function save(string $userId, InvoiceDraftSaveDTO $dto): InvoiceDraft
    {
        return $this->repository->save($userId, $dto->payload, $dto->revision);
    }

    public function delete(string $userId, ?string $publicId, ?int $revision): void
    {
        $this->repository->delete($userId, $publicId, $revision);
    }
}
