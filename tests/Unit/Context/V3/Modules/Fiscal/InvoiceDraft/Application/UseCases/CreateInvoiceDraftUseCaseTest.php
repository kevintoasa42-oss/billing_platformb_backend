<?php

declare(strict_types=1);

namespace Tests\Unit\Context\V3\Modules\Fiscal\InvoiceDraft\Application\UseCases;

use App\Context\V3\Modules\Fiscal\InvoiceDraft\Application\DTOs\InvoiceDraftCreateDTO;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Application\UseCases\CreateInvoiceDraftUseCase;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Domain\Models\InvoiceDraft;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Domain\Models\InvoiceDraftSummary;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Domain\Repository\InvoiceDraftRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CreateInvoiceDraftUseCaseTest extends TestCase
{
    public function test_it_saves_a_draft_for_the_authenticated_user(): void
    {
        $payload = [
            'client' => ['name' => 'Cliente de prueba'],
            'items' => [['quantity' => 2, 'unitPrice' => 10, 'taxRate' => 15]],
            'discount' => 2,
        ];

        $draft = (new CreateInvoiceDraftUseCase($this->repository()))->create(
            '00000000-0000-4000-8000-000000000601',
            new InvoiceDraftCreateDTO($payload, null),
        );

        self::assertSame('00000000-0000-4000-8000-000000000602', $draft->publicId);
        self::assertSame(1, $draft->revision);
        self::assertSame('Cliente de prueba', $draft->summary['client_name']);
        self::assertSame(1, $draft->summary['product_count']);
        self::assertSame('20.70', $draft->summary['total']);
    }

    public function test_it_forwards_the_expected_revision_to_the_repository(): void
    {
        $repository = new class implements InvoiceDraftRepositoryInterface
        {
            public ?int $revision = null;

            public function save(string $userId, array $payload, ?int $expectedRevision): InvoiceDraft
            {
                $this->revision = $expectedRevision;

                return new InvoiceDraft(
                    '00000000-0000-4000-8000-000000000602',
                    2,
                    $payload,
                    InvoiceDraftSummary::fromPayload($payload),
                    '2026-09-15T00:00:00+00:00',
                    '2026-10-15T00:00:00+00:00',
                );
            }
        };

        (new CreateInvoiceDraftUseCase($repository))->create(
            '00000000-0000-4000-8000-000000000601',
            new InvoiceDraftCreateDTO(['items' => []], 1),
        );

        self::assertSame(1, $repository->revision);
    }

    private function repository(): InvoiceDraftRepositoryInterface
    {
        return new class implements InvoiceDraftRepositoryInterface
        {
            public function save(string $userId, array $payload, ?int $expectedRevision): InvoiceDraft
            {
                return new InvoiceDraft(
                    '00000000-0000-4000-8000-000000000602',
                    1,
                    $payload,
                    InvoiceDraftSummary::fromPayload($payload),
                    '2026-09-15T00:00:00+00:00',
                    '2026-10-15T00:00:00+00:00',
                );
            }
        };
    }
}
