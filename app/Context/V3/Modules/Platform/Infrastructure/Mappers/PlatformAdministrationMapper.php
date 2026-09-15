<?php

namespace App\Context\V3\Modules\Platform\Infrastructure\Mappers;

final class PlatformAdministrationMapper
{
    /** @param object|array<string, mixed> $source @return array<string, mixed> */
    public function user(object|array $source): array
    {
        $row = (array) $source;
        $firstName = trim((string) ($row['first_name'] ?? ''));
        $lastName = trim((string) ($row['last_name'] ?? ''));

        return [
            'id' => (int) ($row['legacy_id'] ?? 0),
            'uuid' => (string) ($row['id'] ?? $row['user_uuid'] ?? ''),
            'legacy_id' => isset($row['legacy_id']) ? (int) $row['legacy_id'] : null,
            'name' => (string) ($row['name'] ?? trim($firstName.' '.$lastName)),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => trim($firstName.' '.$lastName) ?: (string) ($row['name'] ?? 'Usuario'),
            'email' => (string) ($row['email'] ?? ''),
            'phone' => null,
            'is_active' => (bool) ($row['active'] ?? true),
            'is_platform_admin' => (bool) ($row['platform_admin'] ?? false),
            'is_super_admin' => (bool) ($row['platform_admin'] ?? false),
        ];
    }

    /** @param object|array<string, mixed> $source @return array<string, mixed> */
    public function tenant(object|array $source, int $usersCount = 0): array
    {
        $row = (array) $source;

        return [
            'id' => (int) ($row['legacy_id'] ?? 0),
            'uuid' => (string) ($row['id'] ?? ''),
            'legacy_id' => isset($row['legacy_id']) ? (int) $row['legacy_id'] : null,
            'name' => (string) ($row['name'] ?? ''),
            'legal_name' => (string) ($row['name'] ?? ''),
            'trade_name' => (string) ($row['name'] ?? ''),
            'short_name' => (string) ($row['name'] ?? ''),
            'ruc' => (string) ($row['ruc'] ?? ''),
            'synthetic' => (bool) ($row['synthetic'] ?? false),
            'tenant_database' => 'master_v3',
            'provisioning_status' => 'ready',
            'users_count' => $usersCount,
        ];
    }
}
