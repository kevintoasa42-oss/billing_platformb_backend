<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Eloquent\Repositories;

use App\Context\V3\Modules\Authentication\Domain\Mappers\AuthenticationMapperInterface;
use App\Context\V3\Modules\Authentication\Domain\Models\AccessibleEnterprise;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticatedUser;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;
use App\Context\V3\Modules\Authentication\Domain\Repositories\AuthenticationRepositoryInterface;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Eloquent\Models\AuthenticationEnterpriseModel;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Eloquent\Models\AuthenticationMembershipModel;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Eloquent\Models\AuthenticationSessionModel;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Eloquent\Models\AuthenticationUserModel;
use Illuminate\Support\Facades\DB;

final class EloquentAuthenticationRepository implements AuthenticationRepositoryInterface
{
    public function __construct(private readonly AuthenticationMapperInterface $mapper) {}

    public function findUserByEmail(string $email): ?AuthenticatedUser
    {
        $model = AuthenticationUserModel::query()
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->first();

        return $model ? $this->mapper->user($model->getAttributes()) : null;
    }

    public function findUserById(string $id): ?AuthenticatedUser
    {
        $model = AuthenticationUserModel::find($id);

        return $model ? $this->mapper->user($model->getAttributes()) : null;
    }

    public function enterprisesForUser(string $userId): array
    {
        return AuthenticationEnterpriseModel::query()
            ->orderBy('name')
            ->get()
            ->map(function (AuthenticationEnterpriseModel $tenant) use ($userId): ?AccessibleEnterprise {
                return $this->withinTenantContext((string) $tenant->getKey(), function () use ($tenant, $userId): ?AccessibleEnterprise {
                    $membership = AuthenticationMembershipModel::query()
                        ->where('user_id', $userId)
                        ->where('active', true)
                        ->first();

                    return $membership
                        ? $this->mapper->enterprise([
                            ...$tenant->getAttributes(),
                            ...$membership->getAttributes(),
                            'id' => $tenant->getKey(),
                            'membership_id' => $membership->getAttribute('id'),
                        ])
                        : null;
                });
            })
            ->filter()
            ->values()
            ->all();
    }

    public function enterpriseForUser(string $userId, string $enterpriseId): ?AccessibleEnterprise
    {
        $tenant = ctype_digit($enterpriseId)
            ? AuthenticationEnterpriseModel::query()->where('legacy_id', (int) $enterpriseId)->first()
            : AuthenticationEnterpriseModel::query()->find($enterpriseId);

        if (! $tenant) {
            return null;
        }

        return $this->withinTenantContext((string) $tenant->getKey(), function () use ($tenant, $userId): ?AccessibleEnterprise {
            $membership = AuthenticationMembershipModel::query()
                ->where('user_id', $userId)
                ->where('active', true)
                ->first();

            return $membership
                ? $this->mapper->enterprise([
                    ...$tenant->getAttributes(),
                    ...$membership->getAttributes(),
                    'id' => $tenant->getKey(),
                    'membership_id' => $membership->getAttribute('id'),
                ])
                : null;
        });
    }

    public function createSession(
        AccessibleEnterprise $enterprise,
        string $userId,
        string $tokenHash,
        int $ttlMinutes,
    ): AuthenticationSession {
        $ttlMinutes = max(5, min(24 * 60, $ttlMinutes));

        return $this->withinTenantContext($enterprise->id, function () use ($enterprise, $userId, $tokenHash, $ttlMinutes): AuthenticationSession {
            $model = new AuthenticationSessionModel;
            $model->setAttribute('token_hash', $tokenHash);
            $model->setAttribute('tenant_id', $enterprise->id);
            $model->setAttribute('user_id', $userId);
            $model->setAttribute('membership_id', $enterprise->membershipId);
            $model->setAttribute('authorization_version', $enterprise->authorizationVersion);
            $model->setAttribute('expires_at', now()->addMinutes($ttlMinutes));
            $model->save();

            return $this->mapper->session($model->getAttributes());
        });
    }

    public function resolveSession(string $tokenHash): ?AuthenticationSession
    {
        $row = DB::connection('master_v3')->selectOne(
            'SELECT tenant_id, user_id, capabilities, platform_admin, expires_at FROM auth.resolve_session(?) LIMIT 1',
            [$tokenHash],
        );

        return $row
            ? $this->mapper->session([...((array) $row), 'token_hash' => $tokenHash])
            : null;
    }

    /** @return list<array<string, mixed>> */
    public function menuTreeForTenant(string $tenantId): array
    {
        return $this->withinTenantContext($tenantId, function () use ($tenantId): array {
            $rawSettings = DB::connection('master_v3')->table('core.tenant_settings')->where('tenant_id', $tenantId)->value('iam_settings');
            $settings = is_array($rawSettings) ? $rawSettings : json_decode((string) $rawSettings, true);
            $menus = is_array($settings) ? array_values((array) ($settings['menus'] ?? [])) : [];

            return $this->buildMenuTree($menus);
        });
    }

    public function revokeSession(string $tokenHash): void
    {
        $session = $this->resolveSession($tokenHash);
        if (! $session) {
            return;
        }

        $this->withinTenantContext($session->tenantId, function () use ($tokenHash): void {
            AuthenticationSessionModel::query()->whereKey($tokenHash)->delete();
        });
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

    /** @template TResult */
    private function withinTenantContext(string $tenantId, callable $callback): mixed
    {
        return DB::connection('master_v3')->transaction(function () use ($tenantId, $callback): mixed {
            DB::connection('master_v3')->select("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            return $callback();
        });
    }
}
