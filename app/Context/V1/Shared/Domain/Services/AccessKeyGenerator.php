<?php

namespace App\Context\V1\Shared\Domain\Services;

use App\Context\V1\Shared\Domain\ValueObjects\AccessKey;

/**
 * Generates SRI access keys for electronic documents.
 *
 * Structure (49 digits):
 *   1-8    fechaEmision (ddmmyyyy)
 *   9-10   codDoc (01=factura, 03=liquidacion, 04=nota credito, etc.)
 *   11-23  RUC (13 digits)
 *   24-26  ambiente (1=pruebas, 2=produccion) padded to 3
 *   27-34  estab(3) + ptoEmi(3) + "00"
 *   35-43  secuencial (9 digits, zero-padded)
 *   44-48  random numeric code (5 digits)
 *   49     check digit (module 10)
 */
class AccessKeyGenerator
{
    private const DOCUMENT_CODES = [
        '01' => '01', // factura
        '03' => '03', // liquidacion de compra
        '04' => '04', // nota de credito
        '05' => '05', // nota de debito
        '06' => '06', // guia de remision
        '07' => '07', // comprobante de retencion
    ];

    /**
     * Generate a 49-digit access key.
     *
     * @param  string  $issueDate  Y-m-d (e.g. "2026-08-28")
     * @param  string  $documentCode  SRI document code (01, 03, 04, 05, 06, 07)
     * @param  string  $ruc  13-digit RUC
     * @param  string  $environment  "1"=pruebas, "2"=produccion
     * @param  string  $establishment  3-digit establishment code
     * @param  string  $emissionPoint  3-digit emission point code
     * @param  string  $sequential  sequential number (will be padded to 9)
     * @return AccessKey
     */
    public function generate(
        string $issueDate,
        string $documentCode,
        string $ruc,
        string $environment,
        string $establishment,
        string $emissionPoint,
        string $sequential,
    ): AccessKey {
        $this->validate($issueDate, $documentCode, $ruc, $environment, $establishment, $emissionPoint, $sequential);

        // 1. Fecha emision: ddmmyyyy
        $date = \DateTime::createFromFormat('Y-m-d', $issueDate);
        $datePart = $date->format('dmY');

        // 2. Tipo de comprobante
        $docPart = self::DOCUMENT_CODES[$documentCode] ?? $documentCode;

        // 3. RUC (13 digits)
        $rucPart = str_pad($ruc, 13, '0', STR_PAD_LEFT);

        // 4. Ambiente (padded to 3)
        $envPart = str_pad($environment, 3, '0', STR_PAD_LEFT);

        // 5. Establecimiento + Punto emision + "00"
        $estabPart = str_pad($establishment, 3, '0', STR_PAD_LEFT)
                   . str_pad($emissionPoint, 3, '0', STR_PAD_LEFT)
                   . '00';

        // 6. Secuencial (padded to 9)
        $seqPart = str_pad($sequential, 9, '0', STR_PAD_LEFT);

        // 7. Random numeric code (5 digits)
        $randomPart = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);

        // Build the 48-digit base
        $base = $datePart . $docPart . $rucPart . $envPart . $estabPart . $seqPart . $randomPart;

        // 8. Check digit (module 10)
        $checkDigit = $this->calculateCheckDigit($base);

        return new AccessKey($base . $checkDigit);
    }

    /**
     * Calculate the check digit using module 10 algorithm.
     *
     * 1. Multiply each digit alternately by 2 and 1, from right to left
     * 2. If the product is >= 10, subtract 9
     * 3. Sum all results
     * 4. Check digit = (10 - (sum % 10)) % 10
     */
    private function calculateCheckDigit(string $base): int
    {
        $sum = 0;
        $weight = 2;
        $length = strlen($base);

        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int) $base[$i];
            $product = $digit * $weight;

            if ($product >= 10) {
                $product -= 9;
            }

            $sum += $product;
            $weight = ($weight === 2) ? 1 : 2;
        }

        return (10 - ($sum % 10)) % 10;
    }

    private function validate(
        string $issueDate,
        string $documentCode,
        string $ruc,
        string $environment,
        string $establishment,
        string $emissionPoint,
        string $sequential,
    ): void {
        if (\DateTime::createFromFormat('Y-m-d', $issueDate) === false) {
            throw new \InvalidArgumentException('issue_date must be a valid Y-m-d date.');
        }

        if (!array_key_exists($documentCode, self::DOCUMENT_CODES)) {
            throw new \InvalidArgumentException(
                "Invalid document code: {$documentCode}. Valid: " . implode(', ', array_keys(self::DOCUMENT_CODES))
            );
        }

        if (!ctype_digit($ruc) || strlen($ruc) > 13) {
            throw new \InvalidArgumentException('ruc must be numeric and max 13 digits.');
        }

        if (!in_array($environment, ['1', '2'], true)) {
            throw new \InvalidArgumentException('environment must be "1" (pruebas) or "2" (produccion).');
        }

        if (!ctype_digit($establishment) || strlen($establishment) > 3) {
            throw new \InvalidArgumentException('establishment must be numeric and max 3 digits.');
        }

        if (!ctype_digit($emissionPoint) || strlen($emissionPoint) > 3) {
            throw new \InvalidArgumentException('emission_point must be numeric and max 3 digits.');
        }

        if (!ctype_digit($sequential) || strlen($sequential) > 9) {
            throw new \InvalidArgumentException('sequential must be numeric and max 9 digits.');
        }
    }
}
