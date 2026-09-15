<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services;

use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\Invoice;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\View;

/**
 * Generates the RIDE (Representacion Impresa de Documento Electronico) PDF
 * using Dompdf and a Blade view. Uses fiscal snapshots for historical accuracy.
 */
final class InvoiceRideRenderer
{
    /**
     * @param  array<string, mixed>  $enterprise  Enterprise data for the header.
     * @return string The PDF binary content.
     */
    public function render(Invoice $invoice, array $enterprise): string
    {
        $html = View::make('sri.invoice-ride', [
            'invoice' => $invoice,
            'enterprise' => $enterprise,
        ])->render();

        $dompdf = new Dompdf([
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return (string) $dompdf->output();
    }

    /**
     * Generate a simple lab PDF for mock mode.
     */
    public function renderLab(string $documentNumber, string $total): string
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
            .'body{font-family:DejaVu Sans,Arial,sans-serif;padding:40px;color:#333;}'
            .'h1{color:#c62828;border-bottom:3px solid #c62828;padding-bottom:10px;}'
            .'table{width:100%;border-collapse:collapse;margin-top:20px;}'
            .'td,th{padding:8px;border:1px solid #ddd;text-align:left;}'
            .'th{background:#f5f5f5;}'
            .'</style></head><body>'
            .'<h1>Documento de Laboratorio</h1>'
            .'<p>Este documento fue generado en modo simulacion (lab/mock).</p>'
            .'<p>No tiene validez fiscal.</p>'
            .'<table><tr><th>Numero</th><td>'.$documentNumber.'</td></tr>'
            .'<tr><th>Total</th><td>$ '.$total.'</td></tr>'
            .'<tr><th>Estado</th><td>SIMULADO</td></tr>'
            .'<tr><th>Fecha</th><td>'.date('Y-m-d H:i:s').'</td></tr>'
            .'</table></body></html>';

        $dompdf = new Dompdf([
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return (string) $dompdf->output();
    }
}
