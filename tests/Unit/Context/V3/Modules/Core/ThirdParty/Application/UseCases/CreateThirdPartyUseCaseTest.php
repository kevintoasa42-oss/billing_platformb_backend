<?php

declare(strict_types=1);

namespace Tests\Unit\Context\V3\Modules\Core\ThirdParty\Application\UseCases;

use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyCreateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Application\UseCases\CreateThirdPartyUseCase;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Exceptions\ThirdPartyException;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdParty;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CreateThirdPartyUseCaseTest extends TestCase
{
    public function test_it_creates_a_canonical_third_party_with_the_requested_roles(): void
    {
        $result = (new CreateThirdPartyUseCase($this->repository()))->create(
            ThirdPartyCreateDTO::fromArray([
                'name' => 'Transportes Andinos',
                'identification' => ' 1790012345001 ',
                'identification_type' => 'RUC',
                'must_invoice' => false,
                'roles' => ['carrier', 'customer'],
                'email' => 'operaciones@example.test',
                'custom_fields' => ['fleet_code' => 'A-01'],
            ]),
        );

        self::assertSame('1790012345001', $result['identification']);
        self::assertSame('04', $result['identification_type']);
        self::assertSame(['carrier', 'customer'], $result['roles']);
        self::assertFalse($result['must_invoice']);
        self::assertSame(['fleet_code' => 'A-01'], $result['custom_fields']);
    }

    public function test_it_assigns_customer_as_the_default_role(): void
    {
        $result = (new CreateThirdPartyUseCase($this->repository()))->create(
            ThirdPartyCreateDTO::fromArray([
                'name' => 'Consumidor final',
                'identification' => '9999999999999',
                'identification_type' => '07',
            ]),
        );

        self::assertSame(['customer'], $result['roles']);
        self::assertTrue($result['must_invoice']);
        self::assertTrue($result['is_active']);
    }

    public function test_it_rejects_an_identification_already_registered_for_the_tenant(): void
    {
        $this->expectException(ThirdPartyException::class);
        $this->expectExceptionCode(409);

        (new CreateThirdPartyUseCase($this->repository(true)))->create(
            ThirdPartyCreateDTO::fromArray([
                'name' => 'Duplicado',
                'identification' => '0912345678',
                'identification_type' => '05',
            ]),
        );
    }

    private function repository(bool $exists = false): ThirdPartyRepositoryInterface
    {
        return new class($exists) implements ThirdPartyRepositoryInterface
        {
            public function __construct(private readonly bool $exists) {}

            public function identificationExists(string $identification, string $identificationType): bool
            {
                return $this->exists;
            }

            public function create(ThirdParty $thirdParty): ThirdParty
            {
                return ThirdParty::fromArray([
                    ...$thirdParty->toArray(),
                    'id' => '00000000-0000-4000-8000-000000000401',
                    'tenant_id' => '00000000-0000-4000-8000-000000000402',
                ]);
            }
        };
    }
}
