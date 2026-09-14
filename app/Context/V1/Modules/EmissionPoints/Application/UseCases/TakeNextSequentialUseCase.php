<?php

namespace App\Context\V1\Modules\EmissionPoints\Application\UseCases;

use App\Context\V1\Modules\EmissionPoints\Application\DTOs\NextSequentialDTO;
use App\Context\V1\Modules\EmissionPoints\Domain\Ports\NextSequentialGeneratorInterface;
use App\Context\V1\Modules\SriVoucherTypes\Application\Adapters\SriVoucherTypeCatalogInterface;
use App\Context\V1\Modules\SriVoucherTypes\Domain\Exceptions\SriVoucherTypeNotFoundException;

/**
 * Takes a sequential for a document and advances the counter atomically.
 */
final class TakeNextSequentialUseCase
{
    public function __construct(
        private NextSequentialGeneratorInterface $generator,
        private SriVoucherTypeCatalogInterface $voucherTypes,
    ) {}

    public function execute(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null, ?int $carrierId = null, string $documentCode = '01'): NextSequentialDTO
    {
        $voucherType = $this->voucherTypes->currentByCode($documentCode);
        if (! $voucherType) {
            throw new SriVoucherTypeNotFoundException($documentCode);
        }
        $takenSequential = $this->generator->takeNextSequential(
            $branchOfficeId,
            $emissionPointId,
            $emissionPoint,
            $carrierId,
            $voucherType->code,
            $voucherType->document,
        );

        return new NextSequentialDTO(
            branch_office_id: $branchOfficeId,
            emission_point_id: $emissionPointId,
            emission_point: $emissionPoint,
            carrier_id: $carrierId,
            document_code: $voucherType->code,
            document_label: $voucherType->document,
            sequential: $takenSequential->sequential,
            formatted_sequential: $takenSequential->formatted(),
        );
    }
}
