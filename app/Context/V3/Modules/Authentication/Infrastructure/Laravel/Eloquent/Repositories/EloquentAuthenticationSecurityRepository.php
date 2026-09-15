<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Eloquent\Repositories;

use App\Context\V3\Modules\Authentication\Domain\Exceptions\AuthenticationException;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;
use App\Context\V3\Modules\Authentication\Domain\Repositories\AuthenticationSecurityRepositoryInterface;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Eloquent\Models\AuthenticationUserModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class EloquentAuthenticationSecurityRepository implements AuthenticationSecurityRepositoryInterface
{
    public function activeSessions(AuthenticationSession $current): array
    {
        return $this->withinTenant($current->tenantId, function () use ($current): array {
            return DB::connection('master_v3')->table('auth.sessions')
                ->where('user_id', $current->userId)
                ->where('expires_at', '>', now())
                ->orderByDesc('created_at')
                ->orderByDesc('legacy_id')
                ->get(['legacy_id', 'device_label', 'created_at', 'last_used_at', 'expires_at', 'token_hash'])
                ->map(static fn (object $session): array => [
                    'id' => (int) $session->legacy_id,
                    'device' => (string) ($session->device_label ?: 'Dispositivo desconocido'),
                    'created_at' => $session->created_at,
                    'last_used_at' => $session->last_used_at,
                    'expires_at' => $session->expires_at,
                    'current' => hash_equals($current->tokenHash, (string) $session->token_hash),
                ])
                ->all();
        });
    }

    public function revokeSession(AuthenticationSession $current, int $sessionLegacyId): bool
    {
        return $this->withinTenant($current->tenantId, fn (): bool => DB::connection('master_v3')->table('auth.sessions')
            ->where('user_id', $current->userId)
            ->where('legacy_id', $sessionLegacyId)
            ->delete() > 0);
    }

    public function revokeOtherSessions(AuthenticationSession $current): int
    {
        return $this->withinTenant($current->tenantId, fn (): int => DB::connection('master_v3')->table('auth.sessions')
            ->where('user_id', $current->userId)
            ->where('token_hash', '!=', $current->tokenHash)
            ->delete());
    }

    public function verifyPassword(string $userId, string $password): bool
    {
        $user = AuthenticationUserModel::query()->find($userId);

        return $user !== null && Hash::check($password, (string) $user->getAttribute('password_hash'));
    }

    public function changePassword(string $userId, string $newPassword): void
    {
        AuthenticationUserModel::query()->whereKey($userId)->update(['password_hash' => Hash::make($newPassword)]);
    }

    public function mfaStatus(string $userId): array
    {
        $user = AuthenticationUserModel::query()->find($userId, ['mfa_enabled', 'mfa_confirmed_at']);
        $confirmedAt = $user?->getAttribute('mfa_confirmed_at');

        return [
            'enabled' => (bool) ($user?->getAttribute('mfa_enabled') ?? false),
            'confirmed_at' => $confirmedAt instanceof \DateTimeInterface
                ? $confirmedAt->format(DATE_ATOM)
                : ($confirmedAt === null ? null : (string) $confirmedAt),
        ];
    }

    public function beginMfa(string $userId): array
    {
        $secret = strtoupper(substr(str_replace(['=', '+', '/'], '', base64_encode(random_bytes(20))), 0, 32));
        AuthenticationUserModel::query()->whereKey($userId)->update([
            'mfa_secret' => $secret,
            'mfa_enabled' => false,
            'mfa_confirmed_at' => null,
        ]);

        return [
            'url' => 'otpauth://totp/Billing%20V3:'.$userId.'?secret='.$secret.'&issuer=Billing%20V3',
            'barcode' => $secret,
        ];
    }

    public function confirmMfa(string $userId, string $code): array
    {
        $user = AuthenticationUserModel::query()->find($userId, ['mfa_secret']);
        if ($user === null || trim((string) $user->getAttribute('mfa_secret')) === '') {
            throw new AuthenticationException('Primero solicita una clave de configuración MFA.', 'mfa_setup_required', 422);
        }
        if (! preg_match('/^\d{6}$/', $code)) {
            throw new AuthenticationException('El código MFA debe tener seis dígitos.', 'validation_failed', 422);
        }

        AuthenticationUserModel::query()->whereKey($userId)->update(['mfa_enabled' => true, 'mfa_confirmed_at' => now()]);

        return $this->mfaStatus($userId);
    }

    public function preferences(string $userId): array
    {
        $preferences = DB::connection('master_v3')->table('auth.user_preferences')->where('user_id', $userId)->value('preferences');

        return is_array($preferences) ? $preferences : (json_decode((string) $preferences, true) ?: []);
    }

    public function savePreferences(string $userId, array $preferences): array
    {
        if (strlen((string) json_encode($preferences)) > 16384) {
            throw new AuthenticationException('Las preferencias exceden el tamaño permitido.', 'validation_failed', 422);
        }

        DB::connection('master_v3')->table('auth.user_preferences')->updateOrInsert(
            ['user_id' => $userId],
            ['preferences' => json_encode($preferences, JSON_THROW_ON_ERROR), 'updated_at' => now()],
        );

        return $preferences;
    }

    public function hasActiveUserWithLegacyId(int $legacyId): bool
    {
        return AuthenticationUserModel::query()->where('legacy_id', $legacyId)->where('active', true)->exists();
    }

    private function withinTenant(string $tenantId, callable $callback): mixed
    {
        return DB::connection('master_v3')->transaction(function () use ($tenantId, $callback): mixed {
            DB::connection('master_v3')->select("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            return $callback();
        });
    }
}
