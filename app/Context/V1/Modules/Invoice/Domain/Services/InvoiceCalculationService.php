<?php

namespace App\Context\V1\Modules\Invoice\Domain\Services;

use App\Context\V1\Modules\Invoice\Domain\Mappers\InvoiceTaxAggregatorMapper;
use App\Context\V1\Modules\Invoice\Domain\Models\InvoiceHeader;
use App\Context\V1\Modules\Invoice\Domain\Repositories\SignatureConfigRepositoryInterface;
use App\Context\V1\Modules\Invoice\Domain\Repositories\SriCatalogRepositoryInterface;
use App\Context\V1\Shared\Domain\Services\AccessKeyGenerator;

class InvoiceCalculationService
{
    public function __construct(
        private SriCatalogRepositoryInterface $sriCatalogRepository,
        private SignatureConfigRepositoryInterface $signatureConfigRepository,
        private AccessKeyGenerator $accessKeyGenerator,
    ) {}

    /**
     * Calculate all header totals, item tax_base, aggregate header taxes,
     * fill SRI catalog data, resolve environment/emission_type from signature,
     * and generate access key.
     *
     * @throws \InvalidArgumentException If no active signature is found or payments do not match the total.
     */
    public function calculateAndEnrich(InvoiceHeader $invoice, int $enterpriseId): InvoiceHeader
    {
        // Resolve environment and emission_type from signature (backend responsibility)
        $signatureConfig = $this->signatureConfigRepository->getActiveConfig(
            $invoice->carrier_id,
            $enterpriseId,
        );

        if (!$signatureConfig) {
            throw new \InvalidArgumentException(
                'No active signature found for this ' . ($invoice->carrier_id ? 'carrier' : 'enterprise') . '.'
            );
        }

        // Map signature values to SRI codes
        $invoice->environment = $signatureConfig->environment === 'produccion' ? '2' : '1';
        $invoice->emission_type = $signatureConfig->emission_type ? '1' : '2';

        // Generate access key (backend responsibility, never from frontend)
        $accessKey = $this->accessKeyGenerator->generate(
            issueDate: $invoice->issue_date,
            documentCode: $invoice->document_code ?? '01',
            ruc: $invoice->ruc,
            environment: $invoice->environment,
            establishment: $invoice->establishment,
            emissionPoint: $invoice->emission_point,
            sequential: $invoice->sequential,
        );
        $invoice->access_key = $accessKey->value;

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
                    $tax->percentage_code = $catalog['percentage_code'];
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

        // Build aggregated header taxes via mapper
        $invoice->taxes = InvoiceTaxAggregatorMapper::fromAggregated($aggregatedTaxes);

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
}
