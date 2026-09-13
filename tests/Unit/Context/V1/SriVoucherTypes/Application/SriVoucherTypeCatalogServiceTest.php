<?php

namespace Tests\Unit\Context\V1\SriVoucherTypes\Application;

use App\Context\V1\SriVoucherTypes\Application\Adapters\SriVoucherTypeCatalogService;
use App\Context\V1\SriVoucherTypes\Application\UseCases\SriVoucherTypeCrudService;
use App\Context\V1\SriVoucherTypes\Domain\Mappers\SriVoucherTypeMapperInterface;
use App\Context\V1\SriVoucherTypes\Domain\Models\SriVoucherType;
use App\Context\V1\SriVoucherTypes\Domain\Repositories\SriVoucherTypeRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class SriVoucherTypeCatalogServiceTest extends TestCase
{
    public function test_it_resolves_the_current_sri_voucher_type_by_code(): void
    {
        $repository = new class implements SriVoucherTypeRepositoryInterface
        {
            public function listPaginated(int $page = 1, int $perPage = 15, array $filters = []): array
            {
                return ['data' => [], 'total' => 0, 'page' => $page, 'perPage' => $perPage, 'lastPage' => 1];
            }

            public function findById(int $id): ?SriVoucherType
            {
                return null;
            }

            public function findCurrentByCode(string $code): ?SriVoucherType
            {
                return $code === '07'
                    ? new SriVoucherType(document: 'Comprobante de Retención', code: '07', retention: true)
                    : null;
            }

            public function create(SriVoucherType $voucherType): SriVoucherType
            {
                return $voucherType;
            }

            public function update(SriVoucherType $voucherType): SriVoucherType
            {
                return $voucherType;
            }

            public function delete(int $id): bool
            {
                return false;
            }
        };
        $mapper = new class implements SriVoucherTypeMapperInterface
        {
            public function toDomain(array $data): SriVoucherType
            {
                return new SriVoucherType;
            }

            public function toPersistence(SriVoucherType $voucherType): array
            {
                return [];
            }

            public function toArray(SriVoucherType $voucherType): array
            {
                return [];
            }
        };

        $catalogue = new SriVoucherTypeCatalogService(new SriVoucherTypeCrudService($repository, $mapper));

        self::assertSame('Comprobante de Retención', $catalogue->currentByCode('07')?->document);
        self::assertSame('07', $catalogue->currentByCode('07')?->code);
        self::assertNull($catalogue->currentByCode('99'));
    }
}
