<?php

declare(strict_types=1);

namespace Tests\Unit\Context\V3\Modules\Core\Product\Application\UseCases;

use App\Context\V3\Modules\Core\Product\Application\UseCases\ProductUseCase;
use App\Context\V3\Modules\Core\Product\Domain\Models\Product;
use App\Context\V3\Modules\Core\Product\Domain\Repository\ProductRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class ProductUseCaseTest extends TestCase
{
    public function test_it_passes_search_pagination_and_active_filter_to_the_repository(): void
    {
        $repository = new class implements ProductRepositoryInterface
        {
            /**
             * @var array{search: ?string, limit: int, is_active: ?bool}
             */
            public array $filters;

            public function all(?string $search = null, int $limit = 500, ?bool $isActive = null): array
            {
                $this->filters = [
                    'search' => $search,
                    'limit' => $limit,
                    'is_active' => $isActive,
                ];

                return [];
            }

            public function findByLegacyId(int $legacyId): ?Product
            {
                return null;
            }

            public function create(array $data): Product
            {
                return new Product;
            }

            public function update(int $legacyId, array $data): ?Product
            {
                return null;
            }

            public function setActive(int $legacyId, bool $isActive): ?Product
            {
                return null;
            }

            public function delete(int $legacyId): ?Product
            {
                return null;
            }

            public function duplicates(array $filters): array
            {
                return [];
            }
        };

        (new ProductUseCase($repository))->all('ca', 20, true);

        self::assertSame([
            'search' => 'ca',
            'limit' => 20,
            'is_active' => true,
        ], $repository->filters);
    }
}
