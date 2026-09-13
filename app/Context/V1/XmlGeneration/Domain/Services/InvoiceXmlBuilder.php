<?php

namespace App\Context\V1\XmlGeneration\Domain\Services;

use App\Context\V1\Invoice\Domain\Models\InvoiceHeader;
use DOMDocument;

/**
 * Builds the SRI factura XML (version 1.1.0) from an InvoiceHeader domain model.
 */
class InvoiceXmlBuilder
{
    public function __construct(
        private string $providerRuc,
    ) {}

    /**
     * Build the XML string for the given invoice.
     */
    public function build(InvoiceHeader $invoice): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $factura = $dom->createElement('factura');
        $factura->setAttribute('id', 'comprobante');
        $factura->setAttribute('version', '1.1.0');
        $dom->appendChild($factura);

        // === infoTributaria ===
        $infoTributaria = $dom->createElement('infoTributaria');
        $factura->appendChild($infoTributaria);

        $this->appendText($dom, $infoTributaria, 'ambiente', $invoice->environment);
        $this->appendText($dom, $infoTributaria, 'tipoEmision', $invoice->emission_type);
        $this->appendText($dom, $infoTributaria, 'razonSocial', $invoice->legal_name);
        if ($invoice->tradename) {
            $this->appendText($dom, $infoTributaria, 'nombreComercial', $invoice->tradename);
        }
        $this->appendText($dom, $infoTributaria, 'ruc', $invoice->ruc);
        $this->appendText($dom, $infoTributaria, 'claveAcceso', $invoice->access_key);
        $this->appendText($dom, $infoTributaria, 'codDoc', $invoice->document_code);
        $this->appendText($dom, $infoTributaria, 'estab', $invoice->establishment);
        $this->appendText($dom, $infoTributaria, 'ptoEmi', $invoice->emission_point);
        $this->appendText($dom, $infoTributaria, 'secuencial', $invoice->sequential);
        $this->appendText($dom, $infoTributaria, 'dirMatriz', $invoice->matrix_address);

        // === infoFactura ===
        $infoFactura = $dom->createElement('infoFactura');
        $factura->appendChild($infoFactura);

        $this->appendText($dom, $infoFactura, 'fechaEmision', $this->formatDate($invoice->issue_date));
        $this->appendText($dom, $infoFactura, 'dirEstablecimiento', $invoice->establishment_address);
        $this->appendText($dom, $infoFactura, 'obligadoContabilidad', $invoice->accounting_required);
        $this->appendText($dom, $infoFactura, 'tipoIdentificacionComprador', $invoice->buyer_identification_type);
        $this->appendText($dom, $infoFactura, 'razonSocialComprador', $invoice->buyer_name);
        $this->appendText($dom, $infoFactura, 'identificacionComprador', $invoice->buyer_identification);
        $this->appendText($dom, $infoFactura, 'direccionComprador', $invoice->buyer_address);
        $this->appendText($dom, $infoFactura, 'totalSinImpuestos', $this->formatNumber($invoice->tax_base));
        $this->appendText($dom, $infoFactura, 'totalDescuento', $this->formatNumber($invoice->discount));

        // totalConImpuestos
        $totalConImpuestos = $dom->createElement('totalConImpuestos');
        $infoFactura->appendChild($totalConImpuestos);

        foreach ($invoice->taxes as $tax) {
            $totalImpuesto = $dom->createElement('totalImpuesto');
            $totalConImpuestos->appendChild($totalImpuesto);

            $this->appendText($dom, $totalImpuesto, 'codigo', $tax->code);
            $this->appendText($dom, $totalImpuesto, 'codigoPorcentaje', $tax->percentage_code);
            $this->appendText($dom, $totalImpuesto, 'baseImponible', $this->formatNumber($tax->tax_base));
            $this->appendText($dom, $totalImpuesto, 'valor', $this->formatNumber($tax->tax));
        }

        $this->appendText($dom, $infoFactura, 'propina', $this->formatNumber($invoice->tip));
        $this->appendText($dom, $infoFactura, 'importeTotal', $this->formatNumber($invoice->total));
        $this->appendText($dom, $infoFactura, 'moneda', $invoice->currency);

        if ($invoice->plate) {
            $this->appendText($dom, $infoFactura, 'placa', $invoice->plate);
        }

        // pagos
        if (!empty($invoice->payments)) {
            $pagos = $dom->createElement('pagos');
            $infoFactura->appendChild($pagos);

            foreach ($invoice->payments as $payment) {
                $pago = $dom->createElement('pago');
                $pagos->appendChild($pago);

                $this->appendText($dom, $pago, 'formaPago', $payment->payment_code);
                $this->appendText($dom, $pago, 'total', $this->formatNumber($payment->total));
                $this->appendText($dom, $pago, 'plazo', (string) $payment->term);
            }
        }

        // === detalles ===
        $detalles = $dom->createElement('detalles');
        $factura->appendChild($detalles);

        foreach ($invoice->items as $item) {
            $detalle = $dom->createElement('detalle');
            $detalles->appendChild($detalle);

            $this->appendText($dom, $detalle, 'codigoPrincipal', $item->main_code);
            if ($item->auxiliary_code) {
                $this->appendText($dom, $detalle, 'codigoAuxiliar', $item->auxiliary_code);
            }
            $this->appendText($dom, $detalle, 'descripcion', $item->description);
            $this->appendText($dom, $detalle, 'cantidad', $this->formatNumber($item->quantity, 5));
            $this->appendText($dom, $detalle, 'precioUnitario', $this->formatNumber($item->unit_price, 5));
            $this->appendText($dom, $detalle, 'descuento', $this->formatNumber($item->discount));
            $this->appendText($dom, $detalle, 'precioTotalSinImpuesto', $this->formatNumber($item->tax_base));

            // impuestos del detalle
            $impuestos = $dom->createElement('impuestos');
            $detalle->appendChild($impuestos);

            foreach ($item->taxes as $tax) {
                $impuesto = $dom->createElement('impuesto');
                $impuestos->appendChild($impuesto);

                $this->appendText($dom, $impuesto, 'codigo', $tax->code);
                $this->appendText($dom, $impuesto, 'codigoPorcentaje', $tax->percentage_code);
                $this->appendText($dom, $impuesto, 'tarifa', $this->formatNumber($tax->rate));
                $this->appendText($dom, $impuesto, 'baseImponible', $this->formatNumber($tax->tax_base));
                $this->appendText($dom, $impuesto, 'valor', $this->formatNumber($tax->tax));
            }
        }

        // === infoAdicional ===
        $infoAdicional = $dom->createElement('infoAdicional');
        $factura->appendChild($infoAdicional);

        // RUC Proveedor (always included, from config)
        $this->appendCampoAdicional($dom, $infoAdicional, 'RUC Proveedor', $this->providerRuc);

        // Additional info from invoice
        foreach ($invoice->additional_info as $info) {
            $this->appendCampoAdicional($dom, $infoAdicional, $info->name, $info->value);
        }

        return $dom->saveXML();
    }

    private function appendText(DOMDocument $dom, \DOMElement $parent, string $name, ?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $parent->appendChild($dom->createElement($name, htmlspecialchars($value, ENT_XML1)));
    }

    private function appendCampoAdicional(DOMDocument $dom, \DOMElement $parent, string $name, string $value): void
    {
        $campo = $dom->createElement('campoAdicional', htmlspecialchars($value, ENT_XML1));
        $campo->setAttribute('nombre', $name);
        $parent->appendChild($campo);
    }

    private function formatDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }
        $dt = \DateTime::createFromFormat('Y-m-d', $date);
        return $dt ? $dt->format('d/m/Y') : $date;
    }

    private function formatNumber(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, '.', '');
    }
}
