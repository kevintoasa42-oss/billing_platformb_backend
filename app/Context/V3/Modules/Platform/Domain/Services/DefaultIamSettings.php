<?php

namespace App\Context\V3\Modules\Platform\Domain\Services;

final readonly class DefaultIamSettings
{
    /** @return array{roles: array, menus: list<array<string, mixed>>, permissions: array} */
    public function value(): array
    {
        $menu = static fn (int $id, ?int $parentId, string $name, string $code, ?string $routePath, int $order): array => [
            'id' => $id,
            'parent_id' => $parentId,
            'name' => $name,
            'code' => $code,
            'icon' => $code,
            'route_path' => $routePath,
            'order' => $order,
            'is_active' => true,
            'permissions' => ['*'],
        ];

        return [
            'roles' => [],
            'permissions' => [],
            'menus' => [
                $menu(1, null, 'Nuevo', 'new', null, 1),
                $menu(2, 1, 'Nueva factura', 'invoices_create', '/facturas/nueva', 1),
                $menu(3, 1, 'Socio transportista', 'transporters_create', '/socios-transportistas/nuevo', 2),
                $menu(4, null, 'Documentos electrónicos', 'electronic_documents', null, 2),
                $menu(5, 4, 'Facturas', 'invoices_list', '/facturas', 1),
                $menu(6, 4, 'Notas de crédito', 'credit_notes', '/notas-de-credito', 2),
                $menu(7, 6, 'Nueva nota de crédito', 'credit_notes_create', '/notas-de-credito/nueva', 1),
                $menu(8, 6, 'Listado de notas de crédito', 'credit_notes_list', '/notas-de-credito', 2),
                $menu(9, 4, 'Notas de débito', 'debit_notes', '/notas-de-debito', 3),
                $menu(10, 4, 'Retenciones', 'withholdings', '/retenciones', 4),
                $menu(11, 4, 'Guías de remisión', 'delivery_guides', '/guias-remision', 5),
                $menu(12, 4, 'Liquidaciones de compra', 'purchase_settlements', '/compras', 6),
                $menu(13, null, 'Socios transportistas', 'transporters', '/socios-transportistas', 3),
                $menu(14, null, 'Mi empresa', 'business', '/empresa', 4),
                $menu(15, 14, 'Sucursales', 'branches', '/empresa/sucursales', 1),
                $menu(16, 14, 'Clientes', 'customers', '/empresa/clientes', 2),
                $menu(17, 14, 'Productos', 'products', '/empresa/productos', 3),
                $menu(18, 14, 'Control de acceso', 'security', '/seguridad', 4),
                $menu(19, 18, 'Usuarios', 'users', '/seguridad/usuarios', 1),
                $menu(20, 18, 'Perfiles de acceso', 'roles', '/seguridad/roles', 2),
                $menu(21, 18, 'Navegación', 'menus', '/seguridad/navegacion', 3),
                $menu(22, 18, 'Permisos técnicos', 'permissions', '/seguridad/permisos', 4),
                $menu(23, null, 'Reportes', 'reports', '/reportes', 5),
                $menu(24, null, 'Configuración', 'settings', '/configuracion', 6),
            ],
        ];
    }
}
