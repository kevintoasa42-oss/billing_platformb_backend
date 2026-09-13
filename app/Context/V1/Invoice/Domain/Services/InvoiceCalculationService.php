<?php

namespace App\Context\V1\Invoice\Domain\Services;

use App\Context\V1\Invoice\Domain\Models\InvoiceHeader;
use App\Context\V1\Invoice\Domain\Models\InvoiceTax;
use App\Context\V1\Invoice\Domain\Repositories\SriCatalogRepositoryInterface;

class InvoiceCalculationService
{
    public function __construct(
        private SriCatalogRepositoryInterface $sriCatalogRepository,
    ) {}

    /**
     * Calculate all header totals, item tax_base, aggregate header taxes,
     * and fill SRI catalog data (code, percentage_code, rate, payment_code).
     *
     * @throws \InvalidArgumentException If payments do not match the total.
     */
    public function calculateAndEnrich(InvoiceHeader $invoice): InvoiceHeader
    {
        $ivaPercentages = $this->sriCatalogRepository->getIvaPercentages();
        $paymentMethods = $this->sriCatalogRepository->getPaymentMethods();

        $subtotal = 0;
        $totalDiscount = 0;
        $totalTax = 0;
        $aggregatedTaxes = []; // keyed by sri_iva_percentage_id

        foreach ($invoice->items as $item) {
            // Calculate item tax_base = (quantity * unit_price) - discount
            $item->tax_base = round(
                ($item->quantity * $item->unit_price) - ($item->discount ?? 0),
                2
            );

            $subtotal += round($item->quantity * $item->unit_price, 2);
            $totalDiscount += round($item->discount ?? 0, 2);

            // Enrich item taxes with SRI catalog data
            foreach ($item->taxes as $tax) {
                $catalog = $ivaPercentages[$tax->sri_iva_percentage_id] ?? null;
                if ($catalog) {
                    $tax->code = '2'; // IVA
                    $tax->percentage_code = $this->resolvePercentageCode($catalog['code']);
                    $tax->rate = $catalog['percentage'] !== null ? (float) $catalog['percentage'] : 0;
                }

                $totalTax += round($tax->tax, 2);

                // Aggregate for header taxes
                $key = $tax->sri_iva_percentage_id;
                if (!isset($aggregatedTaxes[$key])) {
                    $aggregatedTaxes[$key] = [
                        'sri_iva_percentage_id' => $key,
                        'code' => $tax->code,
                        'percentage_code' => $tax->percentage_code,
                        'rate' => $tax->rate,
                        'tax_base' => 0,
                        'tax' => 0,
                    ];
                }
                $aggregatedTaxes[$key]['tax_base'] += round($tax->tax_base, 2);
                $aggregatedTaxes[$key]['tax'] += round($tax->tax, 2);
            }
        }

        // Calculate header totals
        $invoice->subtotal = round($subtotal, 2);
        $invoice->discount = round($totalDiscount, 2);
        $invoice->tax_base = round($subtotal - $totalDiscount, 2);
        $invoice->tax = round($totalTax, 2);
        $invoice->total = round(
            $invoice->tax_base + $invoice->tax + ($invoice->tip ?? 0),
            2
        );

        // Build aggregated header taxes
        $invoice->taxes = array_map(
            fn ($t) => new InvoiceTax(
                sri_iva_percentage_id: $t['sri_iva_percentage_id'],
                code: $t['code'],
                percentage_code: $t['percentage_code'],
                rate: $t['rate'],
                tax_base: round($t['tax_base'], 2),
                tax: round($t['tax'], 2),
            ),
            array_values($aggregatedTaxes)
        );

        // Enrich payments with payment_code from catalog
        foreach ($invoice->payments as $payment) {
            $method = $paymentMethods[$payment->sri_payment_method_id] ?? null;
            if ($method) {
                $payment->payment_code = $method['code'];
            }
        }

        // Validate: sum of payments must match total
        $paymentsTotal = round(
            array_sum(array_map(fn ($p) => $p->total, $invoice->payments)),
            2
        );

        if (!empty($invoice->payments) && abs($paymentsTotal - $invoice->total) > 0.01) {
            throw new \InvalidArgumentException(
                "The sum of payments ({$paymentsTotal}) does not match the invoice total ({$invoice->total})."
            );
        }

        return $invoice;
    }

    /**
     * Resolve the SRI percentage code from the catalog code.
     * Maps: IVA_15 -> 4, IVA_8 -> 3, IVA_5 -> 2, IVA_0 -> 0, EXENTO -> 7, NO_OBJETO -> 6
     */
    private function resolvePercentageCode(string $catalogCode): string
    {
        return match ($catalogCode) {
            'IVA_15' => '4',
            'IVA_8' => '3',
            'IVA_5' => '2',
            'IVA_0' => '0',
            'EXENTO' => '7',
            'NO_OBJETO' => '6',
            default => '0',
        };
    }
}
