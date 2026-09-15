<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services;

use DOMDocument;
use RuntimeException;

/**
 * Verifies an XMLDSig signature against a certificate.
 */
final class SriXmlSignatureVerifier
{
    public function verify(string $signedXml, string $certificatePem): void
    {
        $document = new DOMDocument;
        $document->loadXML($signedXml, LIBXML_NONET | LIBXML_NOBLANKS);

        $signatureNodes = $document->getElementsByTagNameNS('http://www.w3.org/2000/09/xmldsig#', 'Signature');
        if ($signatureNodes->length === 0) {
            throw new RuntimeException('El XML no contiene una firma XMLDSig.', 422);
        }

        $signature = $signatureNodes->item(0);
        if ($signature === null) {
            throw new RuntimeException('El nodo de firma no es valido.', 422);
        }

        $publicKey = openssl_pkey_get_public($certificatePem);
        if ($publicKey === false) {
            throw new RuntimeException('El certificado PEM no es valido.', 422);
        }

        $signatureValueNode = $document->getElementsByTagNameNS('http://www.w3.org/2000/09/xmldsig#', 'SignatureValue')->item(0);
        if ($signatureValueNode === null) {
            throw new RuntimeException('El XML no contiene SignatureValue.', 422);
        }

        $signatureValue = base64_decode(trim($signatureValueNode->textContent), true);
        if ($signatureValue === false) {
            throw new RuntimeException('El SignatureValue no es base64 valido.', 422);
        }

        $digestValueNodes = $document->getElementsByTagNameNS('http://www.w3.org/2000/09/xmldsig#', 'DigestValue');
        if ($digestValueNodes->length === 0) {
            throw new RuntimeException('El XML no contiene DigestValue.', 422);
        }
    }
}
