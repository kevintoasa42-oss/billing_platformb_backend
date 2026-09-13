<?php

namespace App\Context\V1\Invoice\Domain\Mappers;

use App\Context\V1\Invoice\Application\DTOs\InvoiceDTO;
use App\Context\V1\Invoice\Domain\Models\InvoiceAdditionalInfo;
use App\Context\V1\Invoice\Domain\Models\InvoiceHeader;
use App\Context\V1\Invoice\Domain\Models\InvoiceItem;
use App\Context\V1\Invoice\Domain\Models\InvoiceItemTax;
use App\Context\V1\Invoice\Domain\Models\InvoicePayment;
use App\Context\V1\Invoice\Domain\Models\InvoiceTax;

class InvoiceMapper
{
    public static function fromDto(InvoiceDTO $dto): InvoiceHeader
    {
        $items = array_map(fn ($i) => new InvoiceItem(
            id: $i['id'] ?? null,
            invoice_header_id: $i['invoice_header_id'] ?? null,
            product_id: $i['product_id'] ?? null,
            main_code: $i['main_code'] ?? null,
            auxiliary_code: $i['auxiliary_code'] ?? null,
            description: $i['description'] ?? null,
            quantity: $i['quantity'] ?? 0,
            unit_price: $i['unit_price'] ?? 0,
            discount: $i['discount'] ?? 0,
            total_without_tax: $i['total_without_tax'] ?? 0,
            taxes: array_map(fn ($t) => new InvoiceItemTax(
                id: $t['id'] ?? null,
                invoice_item_id: $t['invoice_item_id'] ?? null,
                sri_iva_percentage_id: $t['sri_iva_percentage_id'] ?? null,
                code: $t['code'] ?? null,
                percentage_code: $t['percentage_code'] ?? null,
                rate: $t['rate'] ?? 0,
                tax_base: $t['tax_base'] ?? 0,
                tax: $t['tax'] ?? 0,
            ), $i['taxes'] ?? []),
        ), $dto->items);

        $taxes = array_map(fn ($t) => new InvoiceTax(
            id: $t['id'] ?? null,
            invoice_header_id: $t['invoice_header_id'] ?? null,
            sri_iva_percentage_id: $t['sri_iva_percentage_id'] ?? null,
            code: $t['code'] ?? null,
            percentage_code: $t['percentage_code'] ?? null,
            rate: $t['rate'] ?? 0,
            tax_base: $t['tax_base'] ?? 0,
            tax: $t['tax'] ?? 0,
        ), $dto->taxes);

        $payments = array_map(fn ($p) => new InvoicePayment(
            id: $p['id'] ?? null,
            invoice_header_id: $p['invoice_header_id'] ?? null,
            sri_payment_method_id: $p['sri_payment_method_id'] ?? null,
            payment_code: $p['payment_code'] ?? null,
            total: $p['total'] ?? 0,
            term: $p['term'] ?? 0,
        ), $dto->payments);

        $additionalInfo = array_map(fn ($a) => new InvoiceAdditionalInfo(
            id: $a['id'] ?? null,
            invoice_header_id: $a['invoice_header_id'] ?? null,
            name: $a['name'] ?? null,
            value: $a['value'] ?? null,
        ), $dto->additional_info);

        return new InvoiceHeader(
            id: $dto->id,
            carrier_id: $dto->carrier_id,
            environment: $dto->environment,
            emission_type: $dto->emission_type,
            ruc: $dto->ruc,
            legal_name: $dto->legal_name,
            tradename: $dto->tradename,
            access_key: $dto->access_key,
            document_code: $dto->document_code,
            establishment: $dto->establishment,
            emission_point: $dto->emission_point,
            sequential: $dto->sequential,
            matrix_address: $dto->matrix_address,
            issue_date: $dto->issue_date,
            establishment_address: $dto->establishment_address,
            accounting_required: $dto->accounting_required,
            buyer_identification_type: $dto->buyer_identification_type,
            buyer_name: $dto->buyer_name,
            buyer_identification: $dto->buyer_identification,
            buyer_address: $dto->buyer_address,
            buyer_phone: $dto->buyer_phone,
            buyer_email: $dto->buyer_email,
            total_without_taxes: $dto->total_without_taxes,
            total_discount: $dto->total_discount,
            tip: $dto->tip,
            total_amount: $dto->total_amount,
            currency: $dto->currency,
            plate: $dto->plate,
            status: $dto->status,
            items: $items,
            taxes: $taxes,
            payments: $payments,
            additional_info: $additionalInfo,
        );
    }

    public static function toDtoArray(InvoiceHeader $invoice): array
    {
        return [
            'id' => $invoice->id,
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
            'items' => array_map(fn ($i) => [
                'id' => $i->id,
                'invoice_header_id' => $i->invoice_header_id,
                'product_id' => $i->product_id,
                'main_code' => $i->main_code,
                'auxiliary_code' => $i->auxiliary_code,
                'description' => $i->description,
                'quantity' => $i->quantity,
                'unit_price' => $i->unit_price,
                'discount' => $i->discount,
                'total_without_tax' => $i->total_without_tax,
                'taxes' => array_map(fn ($t) => [
                    'id' => $t->id,
                    'invoice_item_id' => $t->invoice_item_id,
                    'sri_iva_percentage_id' => $t->sri_iva_percentage_id,
                    'code' => $t->code,
                    'percentage_code' => $t->percentage_code,
                    'rate' => $t->rate,
                    'tax_base' => $t->tax_base,
                    'tax' => $t->tax,
                ], $i->taxes),
            ], $invoice->items),
            'taxes' => array_map(fn ($t) => [
                'id' => $t->id,
                'invoice_header_id' => $t->invoice_header_id,
                'sri_iva_percentage_id' => $t->sri_iva_percentage_id,
                'code' => $t->code,
                'percentage_code' => $t->percentage_code,
                'rate' => $t->rate,
                'tax_base' => $t->tax_base,
                'tax' => $t->tax,
            ], $invoice->taxes),
            'payments' => array_map(fn ($p) => [
                'id' => $p->id,
                'invoice_header_id' => $p->invoice_header_id,
                'sri_payment_method_id' => $p->sri_payment_method_id,
                'payment_code' => $p->payment_code,
                'total' => $p->total,
                'term' => $p->term,
            ], $invoice->payments),
            'additional_info' => array_map(fn ($a) => [
                'id' => $a->id,
                'invoice_header_id' => $a->invoice_header_id,
                'name' => $a->name,
                'value' => $a->value,
            ], $invoice->additional_info),
        ];
    }
}
