<?php

namespace App\Context\V1\Modules\Carrier\Application\UseCases;

use App\Context\V1\Modules\Carrier\Domain\Repositories\CarrierRepositoryInterface;

class ListCarriersUseCase
{
    public function __construct(
        private CarrierRepositoryInterface $repository,
    ) {}

    /**
     * Paginated list of carriers.
     *
     * @param  int  $page
     * @param  int  $perPage
     * @param  string|null  $search
     * @return array
     */
    public function execute(int $page = 1, int $perPage = 15, ?string $search = null): array
    {
        return $this->repository->listPaginated($page, $perPage, $search);
    }
}
