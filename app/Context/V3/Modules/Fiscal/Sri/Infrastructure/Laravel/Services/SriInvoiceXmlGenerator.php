<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services;

use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\Invoice;

/**
 * Generates the SRI invoice XML v2.1.0 using DOMDocument.
 * Adapted from the reference project to use domain models instead of Eloquent.
 */
final class SriInvoiceXmlGenerator
{
    private const VOUCHER_TYPE_INVOICE = '01';

    private const BUYER_TYPE_MAP = [
        'RUC' => '04',
        'CED' => '05',
        'PAS' => '06',
        'CF' => '07',
    ];

    /**
     * @param  array<string, mixed>  $enterpriseSnapshot
     * @param  array<string, mixed>  $documentSnapshot
     * @param  array<string, mixed>  $totalsSnapshot
     */
    public function generate(
        Invoice $invoice,
        array $enterpriseSnapshot,
        array $documentSnapshot,
        array $totalsSnapshot,
        string $ambiente,
    ): string {
        $issuer = $invoice->issuer ?? [];
        $recipient = $invoice->recipient ?? [];

        $documentNumber = $invoice->documentNumber;
        $parts = explode('-', $documentNumber);
        $estab = $parts[0] ?? '';
        $ptoEmi = $parts[1] ?? '';
        $secuencial = $parts[2] ?? '';

        $enterpriseLegalName = (string) ($enterpriseSnapshot['legal_name'] ?? $issuer['legal_name'] ?? '');
        $enterpriseRuc = (string) ($enterpriseSnapshot['ruc'] ?? $issuer['ruc'] ?? '');
        $enterpriseMatrixAddress = (string) ($enterpriseSnapshot['matrix_address'] ?? $issuer['matrix_address'] ?? '');
        $nombreComercial = (string) ($enterpriseSnapshot['trade_name'] ?? $issuer['trade_name'] ?? $enterpriseSnapshot['name'] ?? '');

        $fechaEmision = isset($documentSnapshot['issue_date'])
            ? date('d/m/Y', strtotime((string) $documentSnapshot['issue_date']))
            : ($invoice->issuedAt ? date('d/m/Y', strtotime($invoice->issuedAt)) : date('d/m/Y'));

        $accessKey = (string) ($documentSnapshot['access_key'] ?? '');

        $obligadoContabilidad = (bool) ($enterpriseSnapshot['has_accounting'] ?? false) ? 'SI' : 'NO';
        $customerIdentificationType = (string) ($recipient['identification_type'] ?? 'CF');
        $customerIdentification = (string) ($recipient['identification_number'] ?? '9999999999999');
        $tipoIdComprador = self::mapBuyerType($customerIdentificationType, $customerIdentification);

        $customerName = (string) ($recipient['business_name'] ?? $recipient['name'] ?? 'CONSUMIDOR FINAL');
        $customerAddress = (string) ($recipient['address'] ?? '');

        $builder = new SriXmlDocumentBuilder('factura', [
            'id' => 'comprobante',
            'version' => (string) config('services.sri.invoice_xsd_version', '2.1.0'),
        ]);
        $root = $builder->root();

        // infoTributaria
        $infoTributaria = $builder->append($root, 'infoTributaria');
        $builder->appendText($infoTributaria, 'ambiente', $ambiente);
        $builder->appendText($infoTributaria, 'tipoEmision', '1');
        $builder->appendText($infoTributaria, 'razonSocial', $enterpriseLegalName);
        $builder->appendText($infoTributaria, 'nombreComercial', $nombreComercial);
        $builder->appendText($infoTributaria, 'ruc', $enterpriseRuc);
        $builder->appendText($infoTributaria, 'claveAcceso', $accessKey);
        $builder->appendText($infoTributaria, 'codDoc', self::VOUCHER_TYPE_INVOICE);
        $builder->appendText($infoTributaria, 'estab', $estab);
        $builder->appendText($infoTributaria, 'ptoEmi', $ptoEmi);
        $builder->appendText($infoTributaria, 'secuencial', $secuencial);
        $builder->appendText($infoTributaria, 'dirMatriz', $enterpriseMatrixAddress);

        // infoFactura
        $infoFactura = $builder->append($root, 'infoFactura');
        $builder->appendText($infoFactura, 'fechaEmision', $fechaEmision);
        $builder->appendText($infoFactura, 'dirEstablecimiento', $enterpriseMatrixAddress);
        $builder->appendText($infoFactura, 'obligadoContabilidad', $obligadoContabilidad);
        $builder->appendText($infoFactura, 'tipoIdentificacionComprador', $tipoIdComprador);
        $builder->appendText($infoFactura, 'razonSocialComprador', $customerName);
        $builder->appendText($infoFactura, 'identificacionComprador', $customerIdentification);
        $builder->appendText($infoFactura, 'totalSinImpuestos', $this->formatDecimal($invoice->subtotal));
        $builder->appendText($infoFactura, 'totalDescuento', $this->formatDecimal($invoice->discount));

        // totalConImpuestos
        $totalConImpuestos = $builder->append($infoFactura, 'totalConImpuestos');
        $totalImpuesto = $builder->append($totalConImpuestos, 'totalImpuesto');
        $builder->appendText($totalImpuesto, 'codigo', '2');
        $builder->appendText($totalImpuesto, 'codigoPorcentaje', '2');
        $builder->appendText($totalImpuesto, 'baseImponible', $this->formatDecimal($invoice->subtotal));
        $builder->appendText($totalImpuesto, 'valor', $this->formatDecimal($invoice->tax));

        $builder->appendText($infoFactura, 'propina', '0.00');
        $builder->appendText($infoFactura, 'importeTotal', $this->formatDecimal($invoice->total));
        $builder->appendText($infoFactura, 'moneda', 'DOLAR');

        // pagos
        $pagos = $builder->append($infoFactura, 'pagos');
        foreach ($invoice->payments as $payment) {
            $pago = $builder->append($pagos, 'pago');
            $builder->appendText($pago, 'formaPago', $payment->methodCode ?? '01');
            $builder->appendText($pago, 'total', $this->formatDecimal($payment->amount ?? $invoice->total));
            $builder->appendText($pago, 'plazo', '0');
            $builder->appendText($pago, 'unidadTiempo', 'dias');
        }

        // detalles
        $detalles = $builder->append($root, 'detalles');
        foreach ($invoice->details as $line) {
            $detalle = $builder->append($detalles, 'detalle');
            $builder->appendText($detalle, 'codigoPrincipal', (string) ($line->sriPrincipalCode ?? $line->productId ?? '0'));
            $builder->appendText($detalle, 'descripcion', (string) ($line->productName ?? $line->description ?? 'Producto'));
            $builder->appendText($detalle, 'cantidad', $this->formatDecimal($line->quantity ?? '1'));
            $builder->appendText($detalle, 'precioUnitario', $this->formatDecimal($line->unitPrice ?? '0'));
            $builder->appendText($detalle, 'descuento', $this->formatDecimal($line->discount ?? '0'));
            $builder->appendText($detalle, 'precioTotalSinImpuesto', $this->formatDecimal($line->subtotal ?? '0'));

            $impuestos = $builder->append($detalle, 'impuestos');
            $impuesto = $builder->append($impuestos, 'impuesto');
            $builder->appendText($impuesto, 'codigo', '2');
            $builder->appendText($impuesto, 'codigoPorcentaje', '2');
            $builder->appendText($impuesto, 'baseImponible', $this->formatDecimal($line->subtotal ?? '0'));
            $builder->appendText($impuesto, 'valor', $this->formatDecimal($line->tax ?? '0'));
        }

        return $builder->toXml();
    }

    private static function mapBuyerType(string $type, string $identification): string
    {
        $normalized = strtoupper(trim($type));

        if (isset(self::BUYER_TYPE_MAP[$normalized])) {
            return self::BUYER_TYPE_MAP[$normalized];
        }

        if (strlen($identification) === 13) {
            return '04';
        }

        if (strlen($identification) === 10) {
            return '05';
        }

        return '07';
    }

    private function formatDecimal(?string $value): string
    {
        if ($value === null || $value === '') {
            return '0.00';
        }

        return number_format((float) $value, 2, '.', '');
    }
}
