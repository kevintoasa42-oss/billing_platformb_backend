<?php

namespace App\Context\V1\Modules\Invoice\Application\UseCases;

use App\Context\V1\Modules\Invoice\Application\DTOs\InvoiceDTO;
use App\Context\V1\Modules\Invoice\Domain\Repositories\InvoiceRepositoryInterface;

class GetInvoiceByIdUseCase
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
    ) {}

    public function execute(int $id): ?InvoiceDTO
    {
        $data = $this->repository->getById($id);

        if (!$data) {
            return null;
        }

        return InvoiceDTO::fromArray($data);
    }
}
