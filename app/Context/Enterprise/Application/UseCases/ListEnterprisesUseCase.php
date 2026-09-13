<?php

namespace App\Context\Enterprise\Application\UseCases;

use App\Context\Enterprise\Application\DTOs\EnterpriseDTO;
use App\Context\Enterprise\Domain\Repositories\EnterpriseRepositoryInterface;

class ListEnterprisesUseCase
{
    public function __construct(
        private EnterpriseRepositoryInterface $enterpriseRepository,
    ) {}

    /**
     * Lista todas las enterprises.
     *
     * @return EnterpriseDTO[]
     */
    public function ejecutar(): array
    {
        $enterprises = $this->enterpriseRepository->listar();

        return array_map(
            fn ($e) => EnterpriseDTO::fromArray([
                'id' => $e->id,
                'nombre' => $e->nombre,
                'ruc' => $e->ruc,
                'tradename' => $e->tradename,
                'matrixname' => $e->matrixname,
                'telefono' => $e->telefono,
                'correo_corporativo' => $e->correo_corporativo,
            ]),
            $enterprises
        );
    }
}
