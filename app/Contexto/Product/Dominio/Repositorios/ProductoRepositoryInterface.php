<?php

namespace App\Contexto\Product\Dominio\Repositorios;

use App\Contexto\Product\Dominio\Modelos\Producto;

interface ProductoRepositoryInterface
{
    /**
     * Lista paginada de productos.
     *
     * @param  int  $page
     * @param  int  $perPage
     * @param  string|null  $search
     * @return array{data: Producto[], total: int, page: int, perPage: int, lastPage: int}
     */
    public function listarPaginado(int $page = 1, int $perPage = 15, ?string $search = null): array;

    public function obtenerPorId(int $id): ?Producto;

    public function crear(Producto $producto): Producto;

    public function actualizar(Producto $producto): Producto;

    public function cambiarEstado(int $id, bool $estado): bool;

    public function asignarImpuestos(int $productoId, array $impuestoIds): void;
}
