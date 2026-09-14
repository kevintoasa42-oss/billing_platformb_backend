<?php

namespace App\Context\V1\Modules\Clients\Enterprise\Domain\Repositories;

use App\Context\V1\Modules\Clients\Enterprise\Domain\Models\Enterprise;

interface EnterpriseRepositoryInterface
{
    /**
     * Crea una enterprise y devuelve el modelo de dominio con el id asignado.
     *
     * @param  Enterprise  $enterprise
     * @return Enterprise
     */
    public function crear(Enterprise $enterprise): Enterprise;

    /**
     * Lista todas las enterprises.
     *
     * @return Enterprise[]
     */
    public function listar(): array;

    /**
     * Busca una enterprise por su id.
     *
     * @param  int  $id
     * @return Enterprise|null
     */
    public function buscarPorId(int $id): ?Enterprise;

    /**
     * Busca una enterprise por su RUC.
     *
     * @param  string  $ruc
     * @return Enterprise|null
     */
    public function buscarPorRuc(string $ruc): ?Enterprise;

    /**
     * Lista las enterprises asociadas a un user.
     *
     * @param  int  $userId
     * @return Enterprise[]
     */
    public function listarPorUser(int $userId): array;
}
