<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Domain\Ports;

/**
 * Contract for SRI SOAP operations (reception and authorization).
 */
interface SriSoapClientInterface
{
    /**
     * Submit a signed XML to the SRI reception web service (validarComprobante).
     *
     * @param  string  $signedXml  The signed XML document.
     * @param  string  $ambiente  SRI environment code: '1' = pruebas, '2' = produccion.
     * @return array{status: bool, estado?: string, messages?: array, response?: string}
     */
    public function receptionXml(string $signedXml, string $ambiente): array;

    /**
     * Query the SRI authorization web service (autorizacionComprobante).
     *
     * @param  string  $claveAcceso  49-digit access key.
     * @param  string  $ambiente  SRI environment code: '1' = pruebas, '2' = produccion.
     * @return array{status: bool, estado?: string, fechaAutorizacion?: ?string, numeroAutorizacion?: ?string, comprobante?: ?string, messages?: array, response?: string}
     */
    public function authorize(string $claveAcceso, string $ambiente): array;
}
