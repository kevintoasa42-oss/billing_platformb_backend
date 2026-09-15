<?php

namespace Tests\Unit\Context\V3\Authentication\Application;

use App\Context\V3\Modules\Authentication\Application\DTOs\CompleteSessionDTO;
use App\Context\V3\Modules\Authentication\Application\DTOs\CredentialsDTO;
use App\Context\V3\Modules\Authentication\Application\UseCases\CompleteAuthenticationSessionUseCase;
use App\Context\V3\Modules\Authentication\Application\UseCases\CreateLoginChallengeUseCase;
use App\Context\V3\Modules\Authentication\Application\UseCases\RefreshAuthenticationSessionUseCase;
use App\Context\V3\Modules\Authentication\Domain\Exceptions\AuthenticationException;
use App\Context\V3\Modules\Authentication\Domain\Models\AccessibleEnterprise;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticatedUser;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;
use App\Context\V3\Modules\Authentication\Domain\Repositories\AuthenticationRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class AuthenticationUseCasesTest extends TestCase
{
    public function test_it_creates_a_short_lived_challenge_without_exposing_the_password(): void
    {
        $challenge = (new CreateLoginChallengeUseCase($this->repository()))->execute(
            CredentialsDTO::fromArray(['email' => 'ADMIN@BILLING.COM', 'password' => 'Admin123!']),
        );

        self::assertSame('admin@billing.com', $challenge->user->email);
        self::assertSame([
            '00000000-0000-4000-8000-000000000001',
            '00000000-0000-4000-8000-000000000002',
        ], $challenge->cookiePayload()['enterprise_ids']);
        self::assertArrayNotHasKey('password', $challenge->cookiePayload());
        self::assertGreaterThan(time(), $challenge->expiresAt);
    }

    public function test_it_issues_a_scoped_session_only_for_an_enterprise_in_the_challenge(): void
    {
        $repository = $this->repository();
        $session = (new CompleteAuthenticationSessionUseCase($repository))->execute(
            new CompleteSessionDTO(
                '00000000-0000-4000-8000-000000000010',
                ['00000000-0000-4000-8000-000000000001', '00000000-0000-4000-8000-000000000002'],
                time() + 60,
                '00000000-0000-4000-8000-000000000002',
            ),
        );

        self::assertNotEmpty($session->token);
        self::assertSame('00000000-0000-4000-8000-000000000002', $session->enterprise->id);
        self::assertSame('admin@billing.com', $session->user->email);
        self::assertCount(2, $session->enterprises);
        self::assertSame(1, $session->toArray()['enterprises'][0]['id']);
        self::assertSame('00000000-0000-4000-8000-000000000001', $session->toArray()['enterprises'][0]['uuid']);
        self::assertSame([1, 2], $session->toArray()['user']['platform_admin_enterprise_ids']);
    }

    public function test_it_accepts_an_enterprise_legacy_id_from_the_login_challenge(): void
    {
        $session = (new CompleteAuthenticationSessionUseCase($this->repository()))->execute(
            new CompleteSessionDTO(
                '00000000-0000-4000-8000-000000000010',
                ['00000000-0000-4000-8000-000000000001', '00000000-0000-4000-8000-000000000002'],
                time() + 60,
                '2',
            ),
        );

        self::assertSame('00000000-0000-4000-8000-000000000002', $session->enterprise->id);
    }

    public function test_it_refreshes_a_valid_session(): void
    {
        $session = (new RefreshAuthenticationSessionUseCase($this->repository()))->execute(
            new AuthenticationSession(
                tokenHash: 'current-token-hash',
                tenantId: '00000000-0000-4000-8000-000000000001',
                userId: '00000000-0000-4000-8000-000000000010',
                membershipId: '10000000-0000-4000-8000-000000000001',
                authorizationVersion: 1,
                expiresAt: now()->addMinute()->toDateTimeString(),
                capabilities: ['*'],
                platformAdmin: true,
            ),
        );

        self::assertNotEmpty($session->token);
        self::assertCount(2, $session->enterprises);
    }

    public function test_it_rejects_an_enterprise_that_was_not_in_the_login_challenge(): void
    {
        $this->expectException(AuthenticationException::class);

        (new CompleteAuthenticationSessionUseCase($this->repository()))->execute(
            new CompleteSessionDTO(
                '00000000-0000-4000-8000-000000000010',
                ['00000000-0000-4000-8000-000000000001'],
                time() + 60,
                '00000000-0000-4000-8000-000000000002',
            ),
        );
    }

    private function repository(): AuthenticationRepositoryInterface
    {
        return new class implements AuthenticationRepositoryInterface
        {
            private AuthenticatedUser $user;

            /** @var AccessibleEnterprise[] */
            private array $enterprises;

            public function __construct()
            {
                $this->user = new AuthenticatedUser(
                    id: '00000000-0000-4000-8000-000000000010',
                    legacyId: 10,
                    name: 'Admin',
                    email: 'admin@billing.com',
                    passwordHash: password_hash('Admin123!', PASSWORD_BCRYPT),
                    firstName: 'Admin',
                    lastName: 'Billing',
                    platformAdmin: true,
                    active: true,
                );
                $this->enterprises = [
                    new AccessibleEnterprise(
                        '00000000-0000-4000-8000-000000000001',
                        1,
                        'Empresa Uno',
                        '1790000000001',
                        '10000000-0000-4000-8000-000000000001',
                        1,
                        ['*'],
                    ),
                    new AccessibleEnterprise(
                        '00000000-0000-4000-8000-000000000002',
                        2,
                        'Empresa Dos',
                        '1790000000002',
                        '10000000-0000-4000-8000-000000000002',
                        1,
                        ['*'],
                    ),
                ];
            }

            public function findUserByEmail(string $email): ?AuthenticatedUser
            {
                return $email === $this->user->email ? $this->user : null;
            }

            public function findUserById(string $id): ?AuthenticatedUser
            {
                return $id === $this->user->id ? $this->user : null;
            }

            public function enterprisesForUser(string $userId): array
            {
                return $userId === $this->user->id ? $this->enterprises : [];
            }

            public function enterpriseForUser(string $userId, string $enterpriseId): ?AccessibleEnterprise
            {
                foreach ($this->enterprisesForUser($userId) as $enterprise) {
                    if ($enterprise->id === $enterpriseId || (string) $enterprise->legacyId === $enterpriseId) {
                        return $enterprise;
                    }
                }

                return null;
            }

            public function createSession(
                AccessibleEnterprise $enterprise,
                string $userId,
                string $tokenHash,
                int $ttlMinutes,
            ): AuthenticationSession {
                return new AuthenticationSession(
                    tokenHash: $tokenHash,
                    tenantId: $enterprise->id,
                    userId: $userId,
                    membershipId: $enterprise->membershipId,
                    authorizationVersion: $enterprise->authorizationVersion,
                    expiresAt: now()->addMinutes($ttlMinutes)->toDateTimeString(),
                    capabilities: $enterprise->capabilities,
                    platformAdmin: true,
                );
            }

            public function resolveSession(string $tokenHash): ?AuthenticationSession
            {
                return null;
            }

            public function revokeSession(string $tokenHash): void {}

            public function menuTreeForTenant(string $tenantId): array
            {
                return [];
            }
        };
    }
}
