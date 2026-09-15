<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services;

use DOMDocument;
use RuntimeException;

/**
 * Validates an invoice XML against the official SRI XSD schema.
 */
final class SriInvoiceXsdValidator
{
    public function validate(string $xml): void
    {
        $schemaPath = (string) config('services.sri.invoice_xsd');
        $expectedHash = strtolower((string) config('services.sri.invoice_xsd_sha256'));
        if ($schemaPath === '' || ! is_file($schemaPath) || ! preg_match('/^[a-f0-9]{64}$/', $expectedHash)) {
            throw new RuntimeException('El XSD oficial de factura no esta instalado y verificado.', 503);
        }
        if (! hash_equals($expectedHash, hash_file('sha256', $schemaPath))) {
            throw new RuntimeException('El XSD instalado no coincide con el hash aprobado.', 503);
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            if (! $document->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS) || ! $document->schemaValidate($schemaPath)) {
                $messages = array_map(
                    fn (\LibXMLError $error): string => trim("Linea {$error->line}: {$error->message}"),
                    libxml_get_errors(),
                );
                throw new RuntimeException('XML invalido segun XSD: '.implode(' | ', array_slice($messages, 0, 5)), 422);
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
