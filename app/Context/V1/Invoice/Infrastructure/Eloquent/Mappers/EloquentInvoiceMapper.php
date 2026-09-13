<?php

namespace App\Context\V1\Invoice\Infrastructure\Eloquent\Mappers;

use App\Context\V1\Invoice\Domain\Models\InvoiceAdditionalInfo;
use App\Context\V1\Invoice\Domain\Models\InvoiceHeader;
use App\Context\V1\Invoice\Domain\Models\InvoiceItem;
use App\Context\V1\Invoice\Domain\Models\InvoiceItemTax;
use App\Context\V1\Invoice\Domain\Models\InvoicePayment;
use App\Context\V1\Invoice\Domain\Models\InvoiceTax;
use App\Models\InvoiceHeaderModel;

class EloquentInvoiceMapper
{
    public static function toDomain(object $model): InvoiceHeader
    {
        /** @var InvoiceHeaderModel $model */
        $model->load(['items.taxes', 'taxes', 'payments', 'additionalInfo']);

        $items = $model->items->map(fn ($i) => new InvoiceItem(
            id: $i->id,
            invoice_header_id: $i->invoice_header_id,
            product_id: $i->product_id,
            main_code: $i->main_code,
            auxiliary_code: $i->auxiliary_code,
            description: $i->description,
            quantity: (float) $i->quantity,
            unit_price: (float) $i->unit_price,
            discount: (float) $i->discount,
            tax_base: (float) $i->tax_base,
            taxes: $i->taxes->map(fn ($t) => new InvoiceItemTax(
                id: $t->id,
                invoice_item_id: $t->invoice_item_id,
                sri_iva_percentage_id: $t->sri_iva_percentage_id,
                code: $t->code,
                percentage_code: $t->percentage_code,
                rate: (float) $t->rate,
                tax_base: (float) $t->tax_base,
                tax: (float) $t->tax,
            ))->toArray(),
        ))->toArray();

        $taxes = $model->taxes->map(fn ($t) => new InvoiceTax(
            id: $t->id,
            invoice_header_id: $t->invoice_header_id,
            sri_iva_percentage_id: $t->sri_iva_percentage_id,
            code: $t->code,
            percentage_code: $t->percentage_code,
            rate: (float) $t->rate,
            tax_base: (float) $t->tax_base,
            tax: (float) $t->tax,
        ))->toArray();

        $payments = $model->payments->map(fn ($p) => new InvoicePayment(
            id: $p->id,
            invoice_header_id: $p->invoice_header_id,
            sri_payment_method_id: $p->sri_payment_method_id,
            payment_code: $p->payment_code,
            total: (float) $p->total,
            term: $p->term,
        ))->toArray();

        $additionalInfo = $model->additionalInfo->map(fn ($a) => new InvoiceAdditionalInfo(
            id: $a->id,
            invoice_header_id: $a->invoice_header_id,
            name: $a->name,
            value: $a->value,
        ))->toArray();

        return new InvoiceHeader(
            id: $model->id,
            carrier_id: $model->carrier_id,
            environment: $model->environment,
            emission_type: $model->emission_type,
            ruc: $model->ruc,
            legal_name: $model->legal_name,
            tradename: $model->tradename,
            access_key: $model->access_key,
            document_code: $model->document_code,
            establishment: $model->establishment,
            emission_point: $model->emission_point,
            sequential: $model->sequential,
            matrix_address: $model->matrix_address,
            issue_date: $model->issue_date?->format('Y-m-d'),
            establishment_address: $model->establishment_address,
            accounting_required: $model->accounting_required,
            buyer_identification_type: $model->buyer_identification_type,
            buyer_name: $model->buyer_name,
            buyer_identification: $model->buyer_identification,
            buyer_address: $model->buyer_address,
            buyer_phone: $model->buyer_phone,
            buyer_email: $model->buyer_email,
            total_without_taxes: (float) $model->total_without_taxes,
            total_discount: (float) $model->total_discount,
            tip: (float) $model->tip,
            total_amount: (float) $model->total_amount,
            currency: $model->currency,
            plate: $model->plate,
            status: $model->status,
            items: $items,
            taxes: $taxes,
            payments: $payments,
            additional_info: $additionalInfo,
        );
    }

    public static function toModel(InvoiceHeader $invoice): array
    {
        return [
            'carrier_id' => $invoice->carrier_id,
            'environment' => $invoice->environment,
            'emission_type' => $invoice->emission_type,
            'ruc' => $invoice->ruc,
            'legal_name' => $invoice->legal_name,
            'tradename' => $invoice->tradename,
            'access_key' => $invoice->access_key,
            'document_code' => $invoice->document_code,
            'establishment' => $invoice->establishment,
            'emission_point' => $invoice->emission_point,
            'sequential' => $invoice->sequential,
            'matrix_address' => $invoice->matrix_address,
            'issue_date' => $invoice->issue_date,
            'establishment_address' => $invoice->establishment_address,
            'accounting_required' => $invoice->accounting_required,
            'buyer_identification_type' => $invoice->buyer_identification_type,
            'buyer_name' => $invoice->buyer_name,
            'buyer_identification' => $invoice->buyer_identification,
            'buyer_address' => $invoice->buyer_address,
            'buyer_phone' => $invoice->buyer_phone,
            'buyer_email' => $invoice->buyer_email,
            'total_without_taxes' => $invoice->total_without_taxes,
            'total_discount' => $invoice->total_discount,
            'tip' => $invoice->tip,
            'total_amount' => $invoice->total_amount,
            'currency' => $invoice->currency,
            'plate' => $invoice->plate,
            'status' => $invoice->status,
        ];
    }
}
