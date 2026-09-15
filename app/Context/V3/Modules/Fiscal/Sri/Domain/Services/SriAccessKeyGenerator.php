<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Domain\Services;

use RuntimeException;

/**
 * Generates the SRI access key (clave de acceso) for electronic documents.
 *
 * The access key is a 49-digit string composed of:
 *   fechaEmision(8) + tipoComprobante(2) + RUC(13) + ambiente(1) +
 *   establecimiento(3) + puntoEmision(3) + secuencial(9) +
 *   codigoNumerico(8) + tipoEmision(1) + digitoVerificador(1)
 *
 * The check digit is calculated using the module 11 algorithm.
 */
final class SriAccessKeyGenerator
{
    public const VOUCHER_TYPE_INVOICE = '01';
    public const VOUCHER_TYPE_CREDIT_NOTE = '04';
    public const VOUCHER_TYPE_PURCHASE_SETTLEMENT = '03';
    public const VOUCHER_TYPE_DEBIT_NOTE = '05';
    public const VOUCHER_TYPE_DELIVERY_NOTE = '06';
    public const VOUCHER_TYPE_RETENTION = '07';

    private const EMISSION_TYPE_NORMAL = '1';

    public static function generate(
        string $issueDate,
        string $ruc,
        string $environmentCode,
        string $establishment,
        string $emissionPoint,
        string $sequential,
        string $voucherType = self::VOUCHER_TYPE_INVOICE,
    ): string {
        if (preg_match('/^\d{13}$/D', $ruc) !== 1) {
            throw new RuntimeException('Registra el RUC de 13 digitos de la empresa antes de emitir comprobantes.', 422);
        }

        if (! in_array($voucherType, [
            self::VOUCHER_TYPE_INVOICE,
            self::VOUCHER_TYPE_PURCHASE_SETTLEMENT,
            self::VOUCHER_TYPE_CREDIT_NOTE,
            self::VOUCHER_TYPE_DEBIT_NOTE,
            self::VOUCHER_TYPE_DELIVERY_NOTE,
            self::VOUCHER_TYPE_RETENTION,
        ], true)) {
            throw new RuntimeException('Tipo de comprobante no soportado para la clave de acceso.', 422);
        }

        $fechaEmision = date('dmY', strtotime($issueDate));
        $codigoNumerico = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        $serie = $establishment.$emissionPoint;
        $cadena = $fechaEmision
            .$voucherType
            .$ruc
            .$environmentCode
            .$serie
            .$sequential
            .$codigoNumerico
            .self::EMISSION_TYPE_NORMAL;

        $verificador = self::moduleElevenVerifier($cadena);

        return $cadena.$verificador;
    }

    private static function moduleElevenVerifier(string $cadena): int
    {
        $baseMultiplicador = 7;
        $multiplicador = 2;
        $total = 0;

        for ($i = strlen($cadena) - 1; $i >= 0; $i--) {
            $total += (int) substr($cadena, $i, 1) * $multiplicador;
            $multiplicador++;
            if ($multiplicador > $baseMultiplicador) {
                $multiplicador = 2;
            }
        }

        $verificador = 11 - ($total % 11);

        if ($verificador == 11) {
            $verificador = 0;
        }

        if ($verificador == 10) {
            $verificador = 1;
        }

        return $verificador;
    }
}
