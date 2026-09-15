<?php

namespace App\Context\V3\Modules\Platform\Domain\Repositories;

use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;

interface PlatformAdministrationRepositoryInterface
{
    /** @return list<array<string, mixed>> */
    public function tenants(): array;

    /** @return list<array<string, mixed>> */
    public function platformInvoices(): array;

    /** @return array<string, mixed>|null */
    public function tenant(int $legacyId): ?array;

    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    public function createTenant(array $attributes, AuthenticationSession $actor): array;

    /** @param array<string, mixed> $attributes @return array<string, mixed>|null */
    public function updateTenant(int $legacyId, array $attributes): ?array;

    /** @return list<array<string, mixed>> */
    public function users(): array;

    /** @param array<string, mixed> $attributes @return array<string, mixed> */
    public function createUser(array $attributes): array;

    /** @param array<string, mixed> $attributes @return array<string, mixed>|null */
    public function updateUser(int $legacyId, array $attributes): ?array;

    public function deactivateUser(int $legacyId): bool;

    /** @return list<array<string, mixed>> */
    public function tenantUsers(int $tenantLegacyId): array;

    /** @param array<string, mixed> $attributes @return array<string, mixed>|null */
    public function assignUser(int $tenantLegacyId, array $attributes): ?array;

    /** @param array<string, mixed> $attributes */
    public function updateAssignment(int $tenantLegacyId, int $userLegacyId, array $attributes): bool;

    public function removeAssignment(int $tenantLegacyId, int $userLegacyId): bool;

    /** @return array<string, mixed> */
    public function taxSettings(int $tenantLegacyId): array;

    /** @param array<string, mixed> $settings @return array<string, mixed> */
    public function saveTaxSettings(int $tenantLegacyId, array $settings): array;

    /** @return array<string, mixed> */
    public function iamSettings(string $tenantId): array;

    /** @param array<string, mixed> $settings */
    public function saveIamSettings(string $tenantId, array $settings): void;

    /** @return list<array<string, mixed>> */
    public function economicActivities(?string $search): array;
}
