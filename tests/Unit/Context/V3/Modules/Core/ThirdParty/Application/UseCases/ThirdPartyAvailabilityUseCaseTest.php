<?php

declare(strict_types=1);

namespace Tests\Unit\Context\V3\Modules\Core\ThirdParty\Application\UseCases;

use App\Context\V3\Modules\Core\ThirdParty\Application\UseCases\ThirdPartyAvailabilityUseCase;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyIdentity;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyAvailabilityRepositoryInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ThirdPartyAvailabilityUseCaseTest extends TestCase
{
    public function test_it_reports_a_canonical_identification_as_unavailable_when_it_exists(): void
    {
        $result = (new ThirdPartyAvailabilityUseCase($this->repository()))->execute(' 179 0000000001 ', 'ruc');

        self::assertFalse($result['available']);
        self::assertTrue($result['exists']);
        self::assertSame('1790000000001', $result['identification']);
        self::assertSame('04', $result['identification_type']);
        self::assertSame('00000000-0000-4000-8000-000000000101', $result['third_party_id']);
        self::assertSame('Transportes V3', $result['name']);
        self::assertSame(['customer', 'carrier'], $result['roles']);
    }

    public function test_it_allows_the_same_third_party_when_it_is_excluded(): void
    {
        $result = (new ThirdPartyAvailabilityUseCase($this->repository()))->execute(
            '1790000000001',
            '04',
            '00000000-0000-4000-8000-000000000101',
        );

        self::assertTrue($result['available']);
        self::assertFalse($result['exists']);
        self::assertNull($result['third_party_id']);
        self::assertNull($result['name']);
        self::assertSame([], $result['roles']);
    }

    public function test_it_returns_a_non_available_result_for_an_empty_identification(): void
    {
        $result = (new ThirdPartyAvailabilityUseCase($this->repository()))->execute('', null);

        self::assertSame([
            'available' => false,
            'exists' => false,
            'identification' => '',
            'identification_type' => null,
        ], $result);
    }

    public function test_it_rejects_an_unknown_identification_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ThirdPartyAvailabilityUseCase($this->repository()))->execute('1790000000001', 'invalid');
    }

    private function repository(): ThirdPartyAvailabilityRepositoryInterface
    {
        return new class implements ThirdPartyAvailabilityRepositoryInterface
        {
            public function findByIdentification(string $identification, string $identificationType): ?ThirdPartyIdentity
            {
                if ($identification !== '1790000000001' || $identificationType !== '04') {
                    return null;
                }

                return new ThirdPartyIdentity(
                    '00000000-0000-4000-8000-000000000101',
                    'Transportes V3',
                    ['customer', 'carrier'],
                );
            }
        };
    }
}
