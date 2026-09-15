<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Application\UseCases;

use App\Context\V3\Modules\Fiscal\Sri\Domain\Models\ElectronicSignature;
use App\Context\V3\Modules\Fiscal\Sri\Domain\Ports\XmlSigningServiceInterface;

/**
 * Orchestrates XML signing by delegating to the signing service port.
 */
final class SignXmlUseCase
{
    public function __construct(
        private readonly XmlSigningServiceInterface $signingService,
    ) {}

    public function execute(string $xml, ElectronicSignature $credential): string
    {
        return $this->signingService->sign($xml, $credential);
    }
}
