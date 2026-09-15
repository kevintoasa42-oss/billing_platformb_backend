<?php

namespace App\Context\V3\Modules\Platform\Infrastructure\Laravel\Eloquent\Repositories;

use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;
use App\Context\V3\Modules\Platform\Domain\Exceptions\PlatformAdministrationException;
use App\Context\V3\Modules\Platform\Domain\Repositories\PlatformAdministrationRepositoryInterface;
use App\Context\V3\Modules\Platform\Domain\Services\DefaultIamSettings;
use App\Context\V3\Modules\Platform\Infrastructure\Mappers\PlatformAdministrationMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class EloquentPlatformAdministrationRepository implements PlatformAdministrationRepositoryInterface
{
    public function __construct(
        private readonly PlatformAdministrationMapper $mapper,
        private readonly DefaultIamSettings $defaultIamSettings,
    ) {}

    public function tenants(): array
    {
        return DB::connection('master_v3')->table('platform.tenants')->orderBy('legacy_id')->get()
            ->map(fn (object $tenant): array => $this->mapper->tenant($tenant, $this->membershipCount((string) $tenant->id)))
            ->all();
    }

    /** @return list<array<string, mixed>> */
    public function platformInvoices(): array
    {
        $invoices = [];

        foreach (DB::connection('master_v3')->table('platform.tenants')->orderBy('legacy_id')->get(['id', 'legacy_id', 'name']) as $tenant) {
            $tenantInvoices = $this->withinTenant((string) $tenant->id, function (): array {
                return DB::connection('master_v3')->table('platform.invoices')
                    ->select([
                        'legacy_id as id',
                        'contract_id',
                        'invoice_number',
                        'status',
                        'issue_date',
                        'due_date',
                        'subtotal',
                        'tax',
                        'total',
                        'currency',
                        'paid_at',
                        'notes',
                        'created_at',
                        'updated_at',
                    ])
                    ->orderByDesc('issue_date')
                    ->orderByDesc('legacy_id')
                    ->limit(100)
                    ->get()
                    ->map(static fn (object $invoice): array => (array) $invoice)
                    ->all();
            });

            foreach ($tenantInvoices as $invoice) {
                $invoices[] = [
                    'enterprise_id' => (int) $tenant->legacy_id,
                    'enterprise_name' => (string) $tenant->name,
                    ...$invoice,
                ];
            }
        }

        usort($invoices, static fn (array $left, array $right): int => [(string) $right['issue_date'], (int) $right['id']] <=> [(string) $left['issue_date'], (int) $left['id']]);

        return array_slice($invoices, 0, 100);
    }

    public function tenant(int $legacyId): ?array
    {
        $tenant = $this->tenantRecord($legacyId);

        return $tenant === null ? null : $this->mapper->tenant($tenant, $this->membershipCount((string) $tenant->id));
    }

    public function createTenant(array $attributes, AuthenticationSession $actor): array
    {
        return DB::connection('master_v3')->transaction(function () use ($attributes, $actor): array {
            $id = (string) Str::uuid();
            DB::connection('master_v3')->table('platform.tenants')->insert([
                'id' => $id,
                'name' => trim((string) $attributes['name']),
                'ruc' => trim((string) $attributes['ruc']),
                'synthetic' => (bool) ($attributes['synthetic'] ?? true),
            ]);

            $this->withinTenant($id, function () use ($id, $actor): void {
                DB::connection('master_v3')->table('auth.tenant_memberships')->insert([
                    'tenant_id' => $id,
                    'id' => (string) Str::uuid(),
                    'user_id' => $actor->userId,
                    'active' => true,
                    'authorization_version' => 1,
                    'capabilities' => '{*}',
                ]);

                DB::connection('master_v3')->table('core.tenant_settings')->insert([
                    'tenant_id' => $id,
                    'iam_settings' => json_encode($this->defaultIamSettings->value(), JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);
            });

            $tenant = DB::connection('master_v3')->table('platform.tenants')->where('id', $id)->first();

            return $this->mapper->tenant($tenant, 1);
        });
    }

    public function updateTenant(int $legacyId, array $attributes): ?array
    {
        $tenant = $this->tenantRecord($legacyId);
        if ($tenant === null) {
            return null;
        }
        $updates = array_intersect_key($attributes, array_flip(['name', 'ruc', 'synthetic']));
        if ($updates !== []) {
            DB::connection('master_v3')->table('platform.tenants')->where('id', $tenant->id)->update($updates);
        }

        return $this->tenant($legacyId);
    }

    public function users(): array
    {
        return DB::connection('master_v3')->table('auth.users')->orderBy('legacy_id')->get()
            ->map(fn (object $user): array => $this->mapper->user($user))
            ->all();
    }

    public function createUser(array $attributes): array
    {
        $email = strtolower(trim((string) $attributes['email']));
        if (DB::connection('master_v3')->table('auth.users')->whereRaw('lower(email) = ?', [$email])->exists()) {
            throw new PlatformAdministrationException('El correo electrónico ya está registrado.', 'validation_failed', 422);
        }
        $firstName = trim((string) ($attributes['first_name'] ?? $attributes['name'] ?? 'Usuario'));
        $lastName = trim((string) ($attributes['last_name'] ?? ''));
        $id = (string) Str::uuid();
        DB::connection('master_v3')->table('auth.users')->insert([
            'id' => $id,
            'name' => trim($firstName.' '.$lastName),
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'password_hash' => Hash::make((string) ($attributes['password'] ?? Str::password(24))),
            'platform_admin' => (bool) ($attributes['is_platform_admin'] ?? false),
            'active' => (bool) ($attributes['is_active'] ?? true),
        ]);

        return $this->mapper->user(DB::connection('master_v3')->table('auth.users')->where('id', $id)->first());
    }

    public function updateUser(int $legacyId, array $attributes): ?array
    {
        $user = DB::connection('master_v3')->table('auth.users')->where('legacy_id', $legacyId)->first();
        if ($user === null) {
            return null;
        }
        $updates = [];
        foreach (['email', 'first_name', 'last_name'] as $field) {
            if (array_key_exists($field, $attributes)) {
                $updates[$field] = $attributes[$field];
            }
        }
        if (array_key_exists('is_active', $attributes)) {
            $updates['active'] = (bool) $attributes['is_active'];
        }
        if (array_key_exists('is_platform_admin', $attributes)) {
            $updates['platform_admin'] = (bool) $attributes['is_platform_admin'];
        }
        if (isset($attributes['password']) && trim((string) $attributes['password']) !== '') {
            $updates['password_hash'] = Hash::make((string) $attributes['password']);
        }
        if ($updates !== []) {
            $updates['name'] = trim((string) ($updates['first_name'] ?? $user->first_name).' '.(string) ($updates['last_name'] ?? $user->last_name));
            DB::connection('master_v3')->table('auth.users')->where('id', $user->id)->update($updates);
        }

        return $this->mapper->user(DB::connection('master_v3')->table('auth.users')->where('id', $user->id)->first());
    }

    public function deactivateUser(int $legacyId): bool
    {
        return DB::connection('master_v3')->table('auth.users')->where('legacy_id', $legacyId)->update(['active' => false]) > 0;
    }

    public function tenantUsers(int $tenantLegacyId): array
    {
        $tenant = $this->tenantRecord($tenantLegacyId);
        if ($tenant === null) {
            return [];
        }

        return $this->withinTenant((string) $tenant->id, function () use ($tenant): array {
            return DB::connection('master_v3')->table('auth.tenant_memberships as membership')
                ->join('auth.users as user', 'user.id', '=', 'membership.user_id')
                ->where('membership.tenant_id', $tenant->id)
                ->orderBy('user.legacy_id')
                ->get(['user.*', 'membership.active as membership_active', 'membership.capabilities', 'membership.authorization_version'])
                ->map(function (object $row): array {
                    return [
                        'user' => $this->mapper->user($row),
                        'is_active' => (bool) $row->membership_active,
                        'capabilities' => is_array($row->capabilities) ? array_values($row->capabilities) : str_getcsv(trim((string) $row->capabilities, '{}')),
                        'authorization_version' => (int) $row->authorization_version,
                        'assigned_at' => null,
                        'expires_at' => null,
                    ];
                })
                ->all();
        });
    }

    public function assignUser(int $tenantLegacyId, array $attributes): ?array
    {
        $tenant = $this->tenantRecord($tenantLegacyId);
        $user = DB::connection('master_v3')->table('auth.users')->where('legacy_id', (int) ($attributes['user_id'] ?? 0))->first();
        if ($tenant === null || $user === null) {
            return null;
        }

        return $this->withinTenant((string) $tenant->id, function () use ($tenant, $user, $attributes): array {
            $membership = DB::connection('master_v3')->table('auth.tenant_memberships')
                ->where('tenant_id', $tenant->id)->where('user_id', $user->id)->first();
            $capabilities = array_values(array_unique(array_map('strval', (array) ($attributes['capabilities'] ?? ['*']))));
            $values = [
                'active' => (bool) ($attributes['is_active'] ?? true),
                'capabilities' => $this->postgresArray($capabilities),
                'authorization_version' => $membership === null ? 1 : ((int) $membership->authorization_version + 1),
            ];
            if ($membership === null) {
                DB::connection('master_v3')->table('auth.tenant_memberships')->insert([
                    'tenant_id' => $tenant->id,
                    'id' => (string) Str::uuid(),
                    'user_id' => $user->id,
                    ...$values,
                ]);
            } else {
                DB::connection('master_v3')->table('auth.tenant_memberships')->where('tenant_id', $tenant->id)->where('user_id', $user->id)->update($values);
            }

            return [...$this->mapper->user($user), 'capabilities' => $capabilities, 'authorization_version' => $values['authorization_version']];
        });
    }

    public function updateAssignment(int $tenantLegacyId, int $userLegacyId, array $attributes): bool
    {
        return $this->changeAssignment($tenantLegacyId, $userLegacyId, $attributes, false);
    }

    public function removeAssignment(int $tenantLegacyId, int $userLegacyId): bool
    {
        return $this->changeAssignment($tenantLegacyId, $userLegacyId, ['is_active' => false], true);
    }

    public function taxSettings(int $tenantLegacyId): array
    {
        $tenant = $this->requiredTenant($tenantLegacyId);

        return $this->withinTenant((string) $tenant->id, function () use ($tenant): array {
            $settings = DB::connection('master_v3')->table('core.tenant_settings')->where('tenant_id', $tenant->id)->value('tax_settings');

            return $this->arrayValue($settings);
        });
    }

    public function saveTaxSettings(int $tenantLegacyId, array $settings): array
    {
        $tenant = $this->requiredTenant($tenantLegacyId);

        return $this->withinTenant((string) $tenant->id, function () use ($tenant, $settings): array {
            DB::connection('master_v3')->table('core.tenant_settings')->updateOrInsert(
                ['tenant_id' => $tenant->id],
                ['tax_settings' => json_encode($settings, JSON_THROW_ON_ERROR), 'updated_at' => now()],
            );

            return $settings;
        });
    }

    public function iamSettings(string $tenantId): array
    {
        return $this->withinTenant($tenantId, function () use ($tenantId): array {
            $value = DB::connection('master_v3')->table('core.tenant_settings')->where('tenant_id', $tenantId)->value('iam_settings');
            $settings = $this->arrayValue($value);

            return [
                'roles' => array_values((array) ($settings['roles'] ?? [])),
                'menus' => array_values((array) ($settings['menus'] ?? [])),
                'permissions' => array_values((array) ($settings['permissions'] ?? [])),
            ];
        });
    }

    public function saveIamSettings(string $tenantId, array $settings): void
    {
        $this->withinTenant($tenantId, function () use ($tenantId, $settings): void {
            DB::connection('master_v3')->table('core.tenant_settings')->updateOrInsert(
                ['tenant_id' => $tenantId],
                ['iam_settings' => json_encode($settings, JSON_THROW_ON_ERROR), 'updated_at' => now()],
            );
        });
    }

    public function economicActivities(?string $search): array
    {
        $query = DB::connection('master_v3')->table('core.economic_activities')->select(['id', 'name', 'catalog_version']);
        if ($search !== null && trim($search) !== '') {
            $term = '%'.trim($search).'%';
            $query->where(fn ($builder) => $builder->where('id', 'ilike', $term)->orWhere('name', 'ilike', $term));
        }

        return $query->orderBy('id')->limit(500)->get()->map(static fn (object $row): array => [
            'id' => (string) $row->id,
            'code' => (string) $row->id,
            'name' => (string) $row->name,
            'catalog_version' => (string) $row->catalog_version,
            'is_active' => true,
        ])->all();
    }

    private function tenantRecord(int $legacyId): ?object
    {
        return DB::connection('master_v3')->table('platform.tenants')->where('legacy_id', $legacyId)->first();
    }

    private function requiredTenant(int $legacyId): object
    {
        return $this->tenantRecord($legacyId) ?? throw new PlatformAdministrationException('No se encontró la empresa solicitada.', 'not_found', 404);
    }

    private function membershipCount(string $tenantId): int
    {
        return $this->withinTenant($tenantId, fn (): int => DB::connection('master_v3')->table('auth.tenant_memberships')->where('active', true)->count());
    }

    private function changeAssignment(int $tenantLegacyId, int $userLegacyId, array $attributes, bool $remove): bool
    {
        $tenant = $this->tenantRecord($tenantLegacyId);
        $user = DB::connection('master_v3')->table('auth.users')->where('legacy_id', $userLegacyId)->first();
        if ($tenant === null || $user === null) {
            return false;
        }

        return $this->withinTenant((string) $tenant->id, function () use ($tenant, $user, $attributes, $remove): bool {
            $membership = DB::connection('master_v3')->table('auth.tenant_memberships')->where('tenant_id', $tenant->id)->where('user_id', $user->id);
            $updates = ['authorization_version' => DB::raw('authorization_version + 1')];
            if ($remove || array_key_exists('is_active', $attributes)) {
                $updates['active'] = $remove ? false : (bool) $attributes['is_active'];
            }
            if (array_key_exists('capabilities', $attributes)) {
                $updates['capabilities'] = $this->postgresArray((array) $attributes['capabilities']);
            }

            return $membership->update($updates) > 0;
        });
    }

    /** @return array<string, mixed> */
    private function arrayValue(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return is_string($value) ? (json_decode($value, true) ?: []) : [];
    }

    /** @param array<int, mixed> $values */
    private function postgresArray(array $values): string
    {
        $values = array_map(static fn (mixed $value): string => str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $value), $values);

        return '{'.implode(',', array_map(static fn (string $value): string => '"'.$value.'"', $values)).'}';
    }

    private function withinTenant(string $tenantId, callable $callback): mixed
    {
        return DB::connection('master_v3')->transaction(function () use ($tenantId, $callback): mixed {
            DB::connection('master_v3')->select("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            return $callback();
        });
    }
}
