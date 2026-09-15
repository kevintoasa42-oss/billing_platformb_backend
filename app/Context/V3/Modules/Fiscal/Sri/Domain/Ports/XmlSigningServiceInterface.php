<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Domain\Ports;

use App\Context\V3\Modules\Fiscal\Sri\Domain\Models\ElectronicSignature;

/**
 * Contract for signing XML documents with XAdES-EPES.
 * Implemented by the infrastructure signing service.
 */
interface XmlSigningServiceInterface
{
    /**
     * Sign an XML document and return the signed XML.
     *
     * @param  string  $xml  The unsigned XML document.
     * @param  ElectronicSignature  $credential  The signing credential.
     * @return string The signed XML document.
     */
    public function sign(string $xml, ElectronicSignature $credential): string;
}
