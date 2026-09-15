<?php

namespace App\Context\V3\Modules\Platform\Application\UseCases;

use App\Context\V3\Modules\Authentication\Domain\Exceptions\AuthenticationException;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;
use App\Context\V3\Modules\Platform\Domain\Exceptions\PlatformAdministrationException;
use App\Context\V3\Modules\Platform\Domain\Repositories\PlatformAdministrationRepositoryInterface;

final readonly class PlatformAdministrationUseCase
{
    public function __construct(private PlatformAdministrationRepositoryInterface $repository) {}

    /** @return array<string, mixed> */
    public function context(AuthenticationSession $session, string $context): array
    {
        $this->assertCapability($session, $this->contextCapability($context));

        $iam = $this->repository->iamSettings($session->tenantId);

        return [
            'context' => $context,
            'enterprise_uuid' => $session->tenantId,
            'menus' => $this->buildMenuTree($iam['menus']),
            'permissions' => $session->capabilities === [] ? ['*'] : $session->capabilities,
        ];
    }

    /** @return array<string, mixed> */
    public function overview(AuthenticationSession $session): array
    {
        $this->platformAdmin($session);
        $tenants = $this->repository->tenants();
        $active = count(array_filter($tenants, static fn (array $tenant): bool => $tenant['provisioning_status'] === 'ready'));

        return [
            'metrics' => ['active_tenants' => $active, 'renewals_due' => 0, 'collected_month' => '0.00', 'support_open' => 0],
            'tenant_statuses' => ['active' => $active, 'grace' => 0, 'suspended' => 0],
            'tenants' => $tenants,
            'contracts' => [],
            'support_tickets' => [],
            'users' => $this->repository->users(),
            'activity' => [],
            'invoices' => $this->repository->platformInvoices(),
        ];
    }

    /** @return list<array<string, mixed>> */
    public function tenants(AuthenticationSession $session): array
    {
        $this->platformAdmin($session);

        return $this->repository->tenants();
    }

    /** @return list<array<string, mixed>> */
    public function platformInvoices(AuthenticationSession $session): array
    {
        $this->platformAdmin($session);

        return $this->repository->platformInvoices();
    }

    /** @return array<string, mixed> */
    public function tenant(AuthenticationSession $session, int $id): array
    {
        $this->platformAdmin($session);

        return $this->repository->tenant($id) ?? throw new PlatformAdministrationException('No se encontró la empresa solicitada.', 'not_found', 404);
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    public function createTenant(AuthenticationSession $session, array $data): array
    {
        $this->platformAdmin($session);

        return $this->repository->createTenant($data, $session);
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    public function updateTenant(AuthenticationSession $session, int $id, array $data): array
    {
        $this->platformAdmin($session);

        return $this->repository->updateTenant($id, $data) ?? throw new PlatformAdministrationException('No se encontró la empresa solicitada.', 'not_found', 404);
    }

    /** @return list<array<string, mixed>> */
    public function users(AuthenticationSession $session): array
    {
        $this->platformAdmin($session);

        return $this->repository->users();
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    public function createUser(AuthenticationSession $session, array $data): array
    {
        $this->platformAdmin($session);

        return $this->repository->createUser($data);
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    public function updateUser(AuthenticationSession $session, int $id, array $data): array
    {
        $this->platformAdmin($session);

        return $this->repository->updateUser($id, $data) ?? throw new PlatformAdministrationException('No se encontró el usuario solicitado.', 'not_found', 404);
    }

    /** @return array{deleted: bool} */
    public function deleteUser(AuthenticationSession $session, int $id): array
    {
        $this->platformAdmin($session);

        return ['deleted' => $this->repository->deactivateUser($id)];
    }

    /** @return list<array<string, mixed>> */
    public function tenantUsers(AuthenticationSession $session, int $id): array
    {
        $this->platformAdmin($session);

        return $this->repository->tenantUsers($id);
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    public function assignUser(AuthenticationSession $session, int $id, array $data): array
    {
        $this->platformAdmin($session);

        return $this->repository->assignUser($id, $data) ?? throw new PlatformAdministrationException('No se encontró el usuario o la empresa.', 'not_found', 404);
    }

    /** @param array<string, mixed> $data @return array{updated: bool} */
    public function updateAssignment(AuthenticationSession $session, int $tenantId, int $userId, array $data): array
    {
        $this->platformAdmin($session);

        return ['updated' => $this->repository->updateAssignment($tenantId, $userId, $data)];
    }

    /** @return array{deleted: bool} */
    public function removeAssignment(AuthenticationSession $session, int $tenantId, int $userId): array
    {
        $this->platformAdmin($session);

        return ['deleted' => $this->repository->removeAssignment($tenantId, $userId)];
    }

    /** @return array<string, mixed> */
    public function taxSettings(AuthenticationSession $session, int $tenantId): array
    {
        $this->assertEnterpriseAccess($session, $tenantId);
        $this->assertCapability($session, 'settings.read');

        return $this->repository->taxSettings($tenantId);
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    public function saveTaxSettings(AuthenticationSession $session, int $tenantId, array $data): array
    {
        $this->assertEnterpriseAccess($session, $tenantId);
        $this->assertCapability($session, 'settings.update');

        return $this->repository->saveTaxSettings($tenantId, $data);
    }

    /** @return list<array<string, mixed>> */
    public function roles(AuthenticationSession $session): array
    {
        $this->assertCapability($session, 'roles.read');

        return $this->repository->iamSettings($session->tenantId)['roles'];
    }

    /** @return array<string, mixed> */
    public function role(AuthenticationSession $session, int $id): array
    {
        foreach ($this->roles($session) as $role) {
            if ((int) ($role['id'] ?? 0) === $id) {
                return $role;
            }
        } throw new PlatformAdministrationException('No se encontró el rol solicitado.', 'not_found', 404);
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    public function saveRole(AuthenticationSession $session, array $data, ?int $id = null): array
    {
        $this->assertCapability($session, 'roles.update');
        $settings = $this->repository->iamSettings($session->tenantId);
        $roles = $settings['roles'];
        $id ??= $this->nextId($roles);
        $record = [...$data, 'id' => $id, 'is_active' => (bool) ($data['is_active'] ?? true)];
        $roles = $this->replace($roles, $record);
        $settings['roles'] = $roles;
        $this->repository->saveIamSettings($session->tenantId, $settings);

        return $record;
    }

    /** @return array{deleted: bool} */
    public function deleteRole(AuthenticationSession $session, int $id): array
    {
        $this->assertCapability($session, 'roles.update');
        $settings = $this->repository->iamSettings($session->tenantId);
        $before = count($settings['roles']);
        $settings['roles'] = array_values(array_filter($settings['roles'], static fn (array $role): bool => (int) ($role['id'] ?? 0) !== $id));
        $this->repository->saveIamSettings($session->tenantId, $settings);

        return ['deleted' => count($settings['roles']) !== $before];
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    public function saveRoleMenus(AuthenticationSession $session, int $id, array $data): array
    {
        $role = $this->role($session, $id);

        return $this->saveRole($session, [...$role, ...$data, 'menus' => array_values((array) ($data['menus'] ?? []))], $id);
    }

    /** @return list<array<string, mixed>> */
    public function menus(AuthenticationSession $session): array
    {
        $this->assertCapability($session, 'menus.read');

        return $this->repository->iamSettings($session->tenantId)['menus'];
    }

    /** @return list<array<string, mixed>> */
    public function permissions(AuthenticationSession $session): array
    {
        $this->assertCapability($session, 'permissions.read');

        return $this->repository->iamSettings($session->tenantId)['permissions'];
    }

    public function menuTree(AuthenticationSession $session): array
    {
        return $this->buildMenuTree($this->menus($session));
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    public function saveMenu(AuthenticationSession $session, array $data, ?int $id = null): array
    {
        $this->assertCapability($session, 'menus.update');
        $settings = $this->repository->iamSettings($session->tenantId);
        $menus = $settings['menus'];
        $id ??= $this->nextId($menus);
        $record = [...$data, 'id' => $id, 'is_active' => (bool) ($data['is_active'] ?? true)];
        $settings['menus'] = $this->replace($menus, $record);
        $this->repository->saveIamSettings($session->tenantId, $settings);

        return $record;
    }

    /** @return array{deleted: bool} */
    public function deleteMenu(AuthenticationSession $session, int $id): array
    {
        $this->assertCapability($session, 'menus.update');
        $settings = $this->repository->iamSettings($session->tenantId);
        $before = count($settings['menus']);
        $settings['menus'] = array_values(array_filter($settings['menus'], static fn (array $menu): bool => (int) ($menu['id'] ?? 0) !== $id));
        $this->repository->saveIamSettings($session->tenantId, $settings);

        return ['deleted' => count($settings['menus']) !== $before];
    }

    /** @return list<array<string, mixed>> */
    public function economicActivities(AuthenticationSession $session, ?string $search): array
    {
        return $this->repository->economicActivities($search);
    }

    /** @return list<array<string, mixed>> */
    public function countries(AuthenticationSession $session): array
    {
        return [['id' => 1, 'name' => 'Ecuador', 'code' => 'EC']];
    }

    /** @return list<array<string, mixed>> */
    public function provinces(AuthenticationSession $session, ?string $countryCode): array
    {
        return $countryCode !== null && $countryCode !== 'EC' ? [] : [['id' => 1, 'name' => 'Azuay', 'country_code' => 'EC'], ['id' => 2, 'name' => 'Guayas', 'country_code' => 'EC'], ['id' => 3, 'name' => 'Pichincha', 'country_code' => 'EC'], ['id' => 4, 'name' => 'Manabí', 'country_code' => 'EC']];
    }

    /** @return list<array<string, mixed>> */
    public function cities(AuthenticationSession $session, ?string $countryCode, ?int $provinceId): array
    {
        $cities = [['id' => 1, 'name' => 'Cuenca', 'province_id' => 1, 'country_code' => 'EC'], ['id' => 2, 'name' => 'Guayaquil', 'province_id' => 2, 'country_code' => 'EC'], ['id' => 3, 'name' => 'Quito', 'province_id' => 3, 'country_code' => 'EC'], ['id' => 4, 'name' => 'Manta', 'province_id' => 4, 'country_code' => 'EC']];

        return array_values(array_filter($cities, static fn (array $city): bool => ($countryCode === null || $countryCode === $city['country_code']) && ($provinceId === null || $provinceId === $city['province_id'])));
    }

    /** @return list<array<string, mixed>> */
    public function sriEnvironments(AuthenticationSession $session): array
    {
        return [['id' => 1, 'name' => 'Laboratorio local', 'code' => 'lab'], ['id' => 2, 'name' => 'Staging CELCER', 'code' => 'staging'], ['id' => 3, 'name' => 'Producción SRI', 'code' => 'production']];
    }

    /** @return array<string, mixed> */
    public function certification(AuthenticationSession $session, int $tenantId): array
    {
        $this->assertEnterpriseAccess($session, $tenantId);

        return ['ready' => false, 'configured_provider' => 'mock', 'effective_provider' => 'mock', 'configuration_consistent' => true, 'checks' => [['code' => 'v3_master', 'ready' => false, 'message' => 'La certificación SRI requiere el flujo fiscal configurado.']], 'cases' => [], 'certification_status' => 'pending', 'certification_expires_at' => null];
    }

    public function assertEnterpriseAccess(AuthenticationSession $session, int $tenantLegacyId): void
    {
        $tenant = $this->repository->tenant($tenantLegacyId)
            ?? throw new PlatformAdministrationException('No se encontró la empresa solicitada.', 'not_found', 404);

        if ((string) ($tenant['uuid'] ?? '') !== $session->tenantId) {
            throw new AuthenticationException('La empresa seleccionada no está disponible para esta sesión.', 'forbidden', 403);
        }
    }

    private function assertCapability(AuthenticationSession $session, string $capability): void
    {
        if (in_array('*', $session->capabilities, true) || in_array($capability, $session->capabilities, true)) {
            return;
        }

        throw new AuthenticationException('No tienes permiso para realizar esta operación.', 'forbidden', 403);
    }

    private function contextCapability(string $context): string
    {
        return match ($context) {
            'customers' => 'customers.read',
            'products' => 'products.read',
            'branches' => 'branches.read',
            'settings' => 'settings.read',
            'security' => 'users.read',
            'invoice-list' => 'invoices.read',
            default => 'invoices.create',
        };
    }

    private function platformAdmin(AuthenticationSession $session): void
    {
        if (! $session->platformAdmin) {
            throw new AuthenticationException('No tienes permiso para realizar esta operación.', 'forbidden', 403);
        }
    }

    /** @param list<array<string, mixed>> $items @return list<array<string, mixed>> */
    private function replace(array $items, array $record): array
    {
        $found = false;
        foreach ($items as $index => $item) {
            if ((int) ($item['id'] ?? 0) === (int) $record['id']) {
                $items[$index] = $record;
                $found = true;
            }
        } if (! $found) {
            $items[] = $record;
        }

        return array_values($items);
    }

    /** @param list<array<string, mixed>> $items */
    private function nextId(array $items): int
    {
        return $items === [] ? 1 : max(array_map(static fn (array $item): int => (int) ($item['id'] ?? 0), $items)) + 1;
    }

    /** @param list<array<string, mixed>> $menus @return list<array<string, mixed>> */
    private function buildMenuTree(array $menus): array
    {
        $build = function (?int $parentId) use (&$build, $menus): array {
            return array_values(array_map(function (array $menu) use (&$build): array {
                $menu['children'] = $build(isset($menu['id']) ? (int) $menu['id'] : null);

                return $menu;
            }, array_filter($menus, static fn (array $menu): bool => ($menu['parent_id'] ?? null) === $parentId)));
        };

        return $build(null);
    }
}
