<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\InvoiceDraft\Application\UseCases;

use App\Context\V3\Modules\Fiscal\InvoiceDraft\Application\DTOs\InvoiceDraftCreateDTO;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Domain\Models\InvoiceDraft;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Domain\Repository\InvoiceDraftRepositoryInterface;

final readonly class CreateInvoiceDraftUseCase
{
    public function __construct(
        private InvoiceDraftRepositoryInterface $repository,
    ) {}

    public function create(string $userId, InvoiceDraftCreateDTO $dto): InvoiceDraft
    {
        return $this->repository->save($userId, $dto->payload, $dto->revision);
    }
}
