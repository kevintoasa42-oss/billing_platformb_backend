<?php

namespace App\Context\V1\SriVoucherTypes\Application\Adapters;

use App\Context\V1\SriVoucherTypes\Application\DTOs\SriVoucherTypeDTO;
use App\Context\V1\SriVoucherTypes\Application\UseCases\SriVoucherTypeCrudService;

final readonly class SriVoucherTypeCatalogService implements SriVoucherTypeCatalogInterface
{
    public function __construct(private SriVoucherTypeCrudService $service) {}

    public function currentByCode(string $code): ?SriVoucherTypeDTO
    {
        return $this->service->getCurrentByCode($code);
    }
}
