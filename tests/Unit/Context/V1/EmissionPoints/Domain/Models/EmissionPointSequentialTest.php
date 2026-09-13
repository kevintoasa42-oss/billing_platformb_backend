<?php

namespace Tests\Unit\Context\V1\EmissionPoints\Domain\Models;

use App\Context\V1\EmissionPoints\Application\UseCases\GetNextSequentialUseCase;
use App\Context\V1\EmissionPoints\Application\UseCases\TakeNextSequentialUseCase;
use App\Context\V1\EmissionPoints\Domain\Models\EmissionPointSequential;
use App\Context\V1\EmissionPoints\Domain\Ports\NextSequentialGeneratorInterface;
use App\Context\V1\SriVoucherTypes\Application\Adapters\SriVoucherTypeCatalogInterface;
use App\Context\V1\SriVoucherTypes\Application\DTOs\SriVoucherTypeDTO;
use PHPUnit\Framework\TestCase;

final class EmissionPointSequentialTest extends TestCase
{
    public function test_it_formats_the_sri_sequential_from_the_branch_and_emission_point_codes(): void
    {
        $sequential = new EmissionPointSequential('001', '004', 6);

        self::assertSame('001-004-000000006', $sequential->formatted());
    }

    public function test_the_use_case_exposes_the_formatted_sequential_in_its_response_dto(): void
    {
        $generator = new class implements NextSequentialGeneratorInterface
        {
            public int $previewCalls = 0;

            public int $takeCalls = 0;

            public ?int $carrierId = null;

            public ?string $documentCode = null;

            public ?string $documentLabel = null;

            public function nextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null, ?int $carrierId = null, string $documentCode = '01', string $documentLabel = 'Factura'): EmissionPointSequential
            {
                $this->previewCalls++;
                $this->carrierId = $carrierId;
                $this->documentCode = $documentCode;
                $this->documentLabel = $documentLabel;

                return new EmissionPointSequential('001', '004', 6);
            }

            public function takeNextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null, ?int $carrierId = null, string $documentCode = '01', string $documentLabel = 'Factura'): EmissionPointSequential
            {
                $this->takeCalls++;

                return new EmissionPointSequential('001', '004', 6);
            }
        };

        $result = (new GetNextSequentialUseCase($generator, $this->voucherTypes()))->execute(1, 1, '004', 9, '04')->toArray();

        self::assertSame(6, $result['sequential']);
        self::assertSame('001-004-000000006', $result['formatted_sequential']);
        self::assertSame(9, $result['carrier_id']);
        self::assertSame('04', $result['document_code']);
        self::assertSame('Nota de Crédito', $result['document_label']);
        self::assertSame(1, $generator->previewCalls);
        self::assertSame(0, $generator->takeCalls);
        self::assertSame(9, $generator->carrierId);
        self::assertSame('04', $generator->documentCode);
        self::assertSame('Nota de Crédito', $generator->documentLabel);
    }

    public function test_the_take_use_case_uses_the_internal_counter_advancement_operation(): void
    {
        $generator = new class implements NextSequentialGeneratorInterface
        {
            public function nextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null, ?int $carrierId = null, string $documentCode = '01', string $documentLabel = 'Factura'): EmissionPointSequential
            {
                throw new \LogicException('The preview operation must not be called when taking a sequential.');
            }

            public function takeNextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null, ?int $carrierId = null, string $documentCode = '01', string $documentLabel = 'Factura'): EmissionPointSequential
            {
                return new EmissionPointSequential('001', '004', 6);
            }
        };

        $result = (new TakeNextSequentialUseCase($generator, $this->voucherTypes()))->execute(1, 1, '004', 9, '04')->toArray();

        self::assertSame(6, $result['sequential']);
        self::assertSame('001-004-000000006', $result['formatted_sequential']);
        self::assertSame(9, $result['carrier_id']);
        self::assertSame('04', $result['document_code']);
        self::assertSame('Nota de Crédito', $result['document_label']);
    }

    private function voucherTypes(): SriVoucherTypeCatalogInterface
    {
        return new class implements SriVoucherTypeCatalogInterface
        {
            public function currentByCode(string $code): ?SriVoucherTypeDTO
            {
                return match ($code) {
                    '01' => SriVoucherTypeDTO::fromArray(['code' => '01', 'document' => 'Factura']),
                    '04' => SriVoucherTypeDTO::fromArray(['code' => '04', 'document' => 'Nota de Crédito']),
                    default => null,
                };
            }
        };
    }
}
