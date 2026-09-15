<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services;

use DOMDocument;
use DOMXPath;

/**
 * Performs deterministic structural checks before relying on the SRI XSD.
 */
final class SriXmlValidator
{
    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public static function validate(string $xml, bool $requireSignature = false): array
    {
        $errors = [];
        $dom = new DOMDocument;
        $previousUseInternalErrors = libxml_use_internal_errors(true);

        try {
            $loaded = $dom->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS);
            if (! $loaded || ! $dom->documentElement) {
                $errors[] = 'El XML no es bien formado.';

                return ['valid' => false, 'errors' => $errors];
            }

            if ($dom->documentElement->localName !== 'factura') {
                $errors[] = 'El documento XML debe tener una raiz factura.';
            }

            $xpath = new DOMXPath($dom);
            $accessKey = trim((string) $xpath->evaluate(
                'string(/*[local-name()="factura"]/*[local-name()="infoTributaria"]/*[local-name()="claveAcceso"])'
            ));

            if (! preg_match('/^\d{49}$/', $accessKey)) {
                $errors[] = 'La clave de acceso debe contener 49 digitos.';
            }

            $details = $xpath->query('/*[local-name()="factura"]/*[local-name()="detalles"]/*[local-name()="detalle"]');
            foreach ($details ?: [] as $detail) {
                $principalCode = trim((string) $xpath->evaluate(
                    'string(./*[local-name()="codigoPrincipal"][1])',
                    $detail,
                ));
                if ($principalCode === '') {
                    $errors[] = 'Cada detalle debe incluir un codigo principal SRI.';
                } elseif (mb_strlen($principalCode) > 25) {
                    $errors[] = 'El codigo principal SRI no puede superar 25 caracteres.';
                }

                $auxiliaryCode = trim((string) $xpath->evaluate(
                    'string(./*[local-name()="codigoAuxiliar"][1])',
                    $detail,
                ));
                if (mb_strlen($auxiliaryCode) > 25) {
                    $errors[] = 'El codigo auxiliar SRI no puede superar 25 caracteres.';
                }
            }

            if ($requireSignature && $xpath->query(
                '//*[local-name()="Signature" and namespace-uri()="http://www.w3.org/2000/09/xmldsig#"]'
            )->length === 0) {
                $errors[] = 'El XML firmado no contiene una firma XMLDSig.';
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousUseInternalErrors);
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}
