<?php

namespace App\Context\V1\Invoice\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'carrier_id' => 'nullable|integer|exists:tenant.carriers,id',
            'environment' => 'nullable|string|in:1,2',
            'emission_type' => 'nullable|string|in:1,2',
            'ruc' => 'required|string|max:13',
            'legal_name' => 'required|string|max:255',
            'tradename' => 'nullable|string|max:255',
            'access_key' => 'required|string|size:49',
            'document_code' => 'nullable|string|max:2',
            'establishment' => 'required|string|max:3',
            'emission_point' => 'required|string|max:3',
            'sequential' => 'required|string|max:9',
            'matrix_address' => 'nullable|string|max:500',
            'issue_date' => 'required|date',
            'establishment_address' => 'nullable|string|max:500',
            'accounting_required' => 'nullable|string|in:SI,NO',
            'buyer_identification_type' => 'required|string|max:2',
            'buyer_name' => 'required|string|max:255',
            'buyer_identification' => 'required|string|max:20',
            'buyer_address' => 'nullable|string|max:500',
            'buyer_phone' => 'nullable|string|max:20',
            'buyer_email' => 'nullable|email|max:255',
            'subtotal' => 'required|numeric|min:0',
            'total_without_taxes' => 'required|numeric|min:0',
            'total_discount' => 'nullable|numeric|min:0',
            'total_tax' => 'nullable|numeric|min:0',
            'tip' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'plate' => 'nullable|string|max:20',
            'status' => 'nullable|string|in:PENDIENTE,RECHAZADO,AUTORIZADO',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer|exists:tenant.products,id',
            'items.*.main_code' => 'required|string|max:100',
            'items.*.auxiliary_code' => 'nullable|string|max:100',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.total_without_tax' => 'required|numeric|min:0',
            'items.*.taxes' => 'nullable|array',
            'items.*.taxes.*.sri_iva_percentage_id' => 'nullable|integer|exists:pgsql.sri_iva_percentages,id',
            'items.*.taxes.*.code' => 'required|string|max:2',
            'items.*.taxes.*.percentage_code' => 'required|string|max:2',
            'items.*.taxes.*.rate' => 'required|numeric|min:0',
            'items.*.taxes.*.taxable_base' => 'required|numeric|min:0',
            'items.*.taxes.*.value' => 'required|numeric|min:0',
            'taxes' => 'nullable|array',
            'taxes.*.sri_iva_percentage_id' => 'nullable|integer|exists:pgsql.sri_iva_percentages,id',
            'taxes.*.code' => 'required|string|max:2',
            'taxes.*.percentage_code' => 'required|string|max:2',
            'taxes.*.taxable_base' => 'required|numeric|min:0',
            'taxes.*.value' => 'required|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.payment_method' => 'required|string|max:2',
            'payments.*.total' => 'required|numeric|min:0',
            'payments.*.term' => 'nullable|integer|min:0',
            'additional_info' => 'nullable|array',
            'additional_info.*.name' => 'required|string|max:100',
            'additional_info.*.value' => 'nullable|string',
        ];
    }

    public static function toDTO(array $data): \App\Context\V1\Invoice\Application\DTOs\InvoiceDTO
    {
        return \App\Context\V1\Invoice\Application\DTOs\InvoiceDTO::fromArray($data);
    }
}
