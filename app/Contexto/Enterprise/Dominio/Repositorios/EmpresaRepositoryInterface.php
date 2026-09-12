<?php

namespace App\Contexto\Enterprise\Dominio\Repositorios;

use App\Contexto\Enterprise\Dominio\Modelos\Empresa;

interface EmpresaRepositoryInterface
{
    /**
     * Crea una empresa y devuelve el modelo de dominio con el id asignado.
     *
     * @param  Empresa  $empresa
     * @return Empresa
     */
    public function crear(Empresa $empresa): Empresa;

    /**
     * Lista todas las empresas.
     *
     * @return Empresa[]
     */
    public function listar(): array;

    /**
     * Busca una empresa por su id.
     *
     * @param  int  $id
     * @return Empresa|null
     */
    public function buscarPorId(int $id): ?Empresa;

    /**
     * Busca una empresa por su RUC.
     *
     * @param  string  $ruc
     * @return Empresa|null
     */
    public function buscarPorRuc(string $ruc): ?Empresa;

    /**
     * Lista las empresas asociadas a un usuario.
     *
     * @param  int  $usuarioId
     * @return Empresa[]
     */
    public function listarPorUsuario(int $usuarioId): array;
}
